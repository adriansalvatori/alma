<?php

namespace App\Infrastructure\Observability\IntegrationLog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One recorded outbound HTTP call. Stored already redacted.
 *
 * @property int $id
 * @property string $source
 * @property string $method
 * @property string $host
 * @property string $url
 * @property int|null $status
 * @property int|null $duration_ms
 * @property array<string, mixed>|null $request_headers
 * @property string|null $request_body
 * @property string|null $response_body
 * @property string|null $error
 * @property Carbon $created_at
 */
class IntegrationCall extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'alma_integration_calls';

    protected $guarded = ['id'];

    protected $casts = [
        'status' => 'integer',
        'duration_ms' => 'integer',
        'request_headers' => 'array',
    ];

    public function succeeded(): bool
    {
        return $this->error === null && $this->status !== null && $this->status < 400;
    }
}
