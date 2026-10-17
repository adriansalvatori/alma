<?php

namespace App\Domains\Seo\Redirects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * An editor-managed redirect (Tools → Redirects). Code-reviewed redirects live in
 * config/redirects.php instead.
 *
 * @property int $id
 * @property string $from_path
 * @property string $from_key
 * @property string $to_url
 * @property int $status_code
 * @property string|null $note
 * @property int $hits
 * @property Carbon|null $last_hit_at
 * @property int|null $created_by
 */
class Redirect extends Model
{
    protected $table = 'alma_redirects';

    protected $guarded = ['id'];

    protected $casts = [
        'status_code' => 'integer',
        'hits' => 'integer',
        'created_by' => 'integer',
        'last_hit_at' => 'datetime',
    ];
}
