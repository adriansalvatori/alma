<?php

use App\Domains\Payment\Enums\PaymentStatus;

it('only allows forward transitions', function (PaymentStatus $from, PaymentStatus $to, bool $allowed) {
    expect($from->canTransitionTo($to))->toBe($allowed);
})->with([
    [PaymentStatus::Pending, PaymentStatus::Succeeded, true],
    [PaymentStatus::Pending, PaymentStatus::Failed, true],
    [PaymentStatus::Failed, PaymentStatus::Succeeded, true],
    [PaymentStatus::Succeeded, PaymentStatus::Refunded, true],
    // A late "failed" must never undo a payment that succeeded.
    [PaymentStatus::Succeeded, PaymentStatus::Failed, false],
    [PaymentStatus::Succeeded, PaymentStatus::Pending, false],
    [PaymentStatus::Pending, PaymentStatus::Refunded, false],
    [PaymentStatus::Refunded, PaymentStatus::Succeeded, false],
]);
