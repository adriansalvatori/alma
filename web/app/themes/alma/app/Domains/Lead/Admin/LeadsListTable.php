<?php

namespace App\Domains\Lead\Admin;

use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadStatus;
use App\Domains\Lead\Services\LeadQuery;

/**
 * The Leads list. Only loadable once WordPress' WP_List_Table is (LeadsAdmin requires it first).
 */
class LeadsListTable extends \WP_List_Table
{
    private const PER_PAGE = 20;

    /**
     * @param  array<string, string>  $filters
     */
    public function __construct(private LeadQuery $leads, private array $filters)
    {
        parent::__construct(['singular' => 'lead', 'plural' => 'leads', 'ajax' => false]);
    }

    public function get_columns(): array
    {
        return [
            'name' => __('Name', 'alma'),
            'email' => __('Email', 'alma'),
            'form' => __('Form', 'alma'),
            'status' => __('Status', 'alma'),
            'source' => __('Source', 'alma'),
            'created_at' => __('Received', 'alma'),
        ];
    }

    public function prepare_items(): void
    {
        $query = $this->leads->build($this->filters);
        $total = (clone $query)->count();

        $this->items = $query->forPage($this->get_pagenum(), self::PER_PAGE)->get()->all();
        $this->_column_headers = [$this->get_columns(), [], []];

        $this->set_pagination_args(['total_items' => $total, 'per_page' => self::PER_PAGE]);
    }

    /**
     * @param  Lead  $item
     */
    public function column_name($item): string
    {
        $url = LeadsAdmin::url(['lead' => $item->id]);
        $label = $item->name !== '' ? $item->name : __('(no name)', 'alma');

        return sprintf('<strong><a class="row-title" href="%s">%s</a></strong>', esc_url($url), esc_html($label))
            .$this->row_actions(['view' => sprintf('<a href="%s">%s</a>', esc_url($url), esc_html__('View', 'alma'))]);
    }

    /**
     * @param  Lead  $item
     * @param  string  $column_name
     */
    public function column_default($item, $column_name): string
    {
        return match ($column_name) {
            'email' => sprintf('<a href="mailto:%1$s">%1$s</a>', esc_attr($item->email)),
            'form' => esc_html($item->form_key),
            'status' => esc_html($item->status->label()),
            'source' => esc_html(self::source($item)),
            'created_at' => esc_html((string) $item->created_at?->format(get_option('date_format').' '.get_option('time_format'))),
            default => '',
        };
    }

    protected function get_views(): array
    {
        $counts = $this->leads->statusCounts();
        $current = $this->filters['status'] ?? '';
        $all = array_sum(array_diff_key($counts, [LeadStatus::Spam->value => 0]));

        $views = ['all' => $this->view('', __('All', 'alma'), $all, $current === '')];

        foreach (LeadStatus::cases() as $status) {
            if (($counts[$status->value] ?? 0) > 0) {
                $views[$status->value] = $this->view($status->value, $status->label(), $counts[$status->value], $current === $status->value);
            }
        }

        return $views;
    }

    protected function extra_tablenav($which): void
    {
        if ($which !== 'top') {
            return;
        }

        $forms = $this->leads->forms();

        if (count($forms) < 2) {
            return;
        }

        echo '<div class="alignleft actions"><select name="form"><option value="">'.esc_html__('All forms', 'alma').'</option>';

        foreach ($forms as $form) {
            printf('<option value="%1$s"%2$s>%1$s</option>', esc_attr($form), selected($this->filters['form'] ?? '', $form, false));
        }

        echo '</select>';
        submit_button(__('Filter', 'alma'), '', 'filter_action', false);
        echo '</div>';
    }

    public function no_items(): void
    {
        esc_html_e('No leads yet. They appear here as soon as someone submits a form.', 'alma');
    }

    public static function source(Lead $lead): string
    {
        $touch = $lead->attribution['first'] ?? null;

        if (! is_array($touch)) {
            return __('Direct', 'alma');
        }

        return implode(' / ', array_filter([$touch['source'] ?? null, $touch['medium'] ?? null])) ?: __('Direct', 'alma');
    }

    private function view(string $status, string $label, int $count, bool $current): string
    {
        return sprintf(
            '<a href="%s"%s>%s <span class="count">(%d)</span></a>',
            esc_url(LeadsAdmin::url(array_filter(['status' => $status]))),
            $current ? ' class="current" aria-current="page"' : '',
            esc_html($label),
            $count,
        );
    }
}
