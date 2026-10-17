<?php

namespace App\Domains\Payment\WooCommerce;

use App\Domains\Payment\Actions\StartCheckoutAction;
use App\Domains\Payment\Data\CheckoutRequest;
use App\Domains\Payment\Data\Money;
use App\Domains\Payment\GatewayManager;
use Throwable;
use WC_Payment_Gateway;

/**
 * Expose a Payment module gateway as a WooCommerce payment method.
 *
 * One gateway implementation then serves both a standalone checkout (a deposit button, a
 * booking fee) and the WooCommerce checkout. A concrete bridge only names the gateway:
 *
 *     class WompiWooCommerceGateway extends WooCommerceGatewayBridge
 *     {
 *         protected function gatewayKey(): string { return 'wompi'; }
 *     }
 *
 * and is listed in config('payments.woocommerce.gateways'). Payment goes through
 * StartCheckoutAction (hosted checkout redirect); the provider's webhook then completes the
 * order through SyncWooCommerceOrder. Only ever loaded when WooCommerce is active.
 */
abstract class WooCommerceGatewayBridge extends WC_Payment_Gateway
{
    /**
     * The key of the gateway in config/payments.php that this payment method delegates to.
     */
    abstract protected function gatewayKey(): string;

    public function __construct()
    {
        $gateway = app(GatewayManager::class)->gateway($this->gatewayKey());

        $this->id = 'alma_'.$this->gatewayKey();
        $this->method_title = $gateway->label();
        $this->method_description = __('Hosted checkout through the Alma Payment module.', 'alma');
        $this->has_fields = false;
        $this->supports = ['products'];

        $this->init_form_fields();
        $this->init_settings();

        $this->title = (string) $this->get_option('title', $gateway->label());
        $this->description = (string) $this->get_option('description', '');

        add_action('woocommerce_update_options_payment_gateways_'.$this->id, function (): void {
            $this->process_admin_options();
        });
    }

    public function init_form_fields(): void
    {
        $this->form_fields = [
            'enabled' => ['title' => __('Enable', 'alma'), 'type' => 'checkbox', 'default' => 'no'],
            'title' => ['title' => __('Title', 'alma'), 'type' => 'text', 'default' => $this->method_title],
            'description' => ['title' => __('Description', 'alma'), 'type' => 'textarea', 'default' => ''],
        ];
    }

    /**
     * Hidden at checkout while the underlying gateway lacks credentials.
     */
    public function is_available()
    {
        return parent::is_available() && app(GatewayManager::class)->gateway($this->gatewayKey())->isConfigured();
    }

    /**
     * @param  int  $order_id
     * @return array{result: string, redirect?: string}
     */
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        if (! $order) {
            return ['result' => 'failure'];
        }

        try {
            $started = app(StartCheckoutAction::class)->execute(new CheckoutRequest(
                // WooCommerce stores totals as decimal strings; round half up to the currency.
                amount: Money::fromDecimal((string) $order->get_total(), $order->get_currency(), round: true),
                description: sprintf(__('Order #%s', 'alma'), $order->get_order_number()),
                customerEmail: $order->get_billing_email(),
                customerName: trim($order->get_billing_first_name().' '.$order->get_billing_last_name()) ?: null,
                metadata: ['wc_order_id' => $order->get_id()],
                successUrl: $this->get_return_url($order),
                cancelUrl: $order->get_cancel_order_url_raw(),
            ), $this->gatewayKey());
        } catch (Throwable $e) {
            wc_add_notice(__('The payment could not be started. Please try again.', 'alma'), 'error');
            report($e);

            return ['result' => 'failure'];
        }

        $order->update_meta_data('_alma_payment_uuid', $started->payment->uuid);
        $order->save();

        return ['result' => 'success', 'redirect' => (string) $started->redirectUrl()];
    }
}
