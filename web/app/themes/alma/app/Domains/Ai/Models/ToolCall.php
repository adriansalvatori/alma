<?php

namespace App\Domains\Ai\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $ability
 * @property string $outcome
 * @property string|null $input_summary
 * @property string|null $error
 * @property int|null $duration_ms
 * @property Carbon|null $created_at
 */
class ToolCall extends Model
{
    public const SUCCEEDED = 'succeeded';

    public const FAILED = 'failed';

    public const DENIED = 'denied';

    public const INVALID = 'invalid';

    public const AWAITING_CONFIRMATION = 'awaiting_confirmation';

    public const UPDATED_AT = null;

    protected $table = 'alma_ai_tool_calls';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'duration_ms' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
