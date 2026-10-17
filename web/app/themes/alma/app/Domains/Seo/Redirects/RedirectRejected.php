<?php

namespace App\Domains\Seo\Redirects;

use RuntimeException;

/**
 * A redirect that would break the site (a loop, a system path, an insecure target). The message
 * is written for the editor who tried to save it.
 */
class RedirectRejected extends RuntimeException {}
