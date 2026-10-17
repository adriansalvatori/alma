<?php

namespace App\Domains\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One row of the lead audit log. See the alma_lead_activity migration for the dispatch/outcome
 * pairing.
 *
 * @property int $id
 * @property int $lead_id
 * @property string $event
 * @property string $actor
 * @property string $outcome
 * @property array<string, mixed>|null $detail
 * @property Carbon|null $created_at
 */
class LeadActivity extends Model
{
    public const DISPATCHED = 'dispatched';

    public const SUCCEEDED = 'succeeded';

    public const FAILED = 'failed';

    public const SKIPPED = 'skipped';

    public const UPDATED_AT = null;

    protected $table = 'alma_lead_activity';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'detail' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Lead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }
}
