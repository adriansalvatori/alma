<?php

namespace App\Domains\Payment\Enums;

enum WebhookOutcome: string
{
    /** The payment changed status and its event was dispatched. */
    case Processed = 'processed';

    /** This provider event was handled before; nothing was done twice. */
    case Duplicate = 'duplicate';

    /** Verified and recorded, but it changed nothing (unknown reference, invalid transition). */
    case Ignored = 'ignored';
}
