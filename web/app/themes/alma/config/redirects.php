<?php

/*
|--------------------------------------------------------------------------
| Redirects (code-reviewed)
|--------------------------------------------------------------------------
|
| Permanent redirects that belong to the codebase: a migrated site's old URLs,
| a renamed page. Editors add campaign or one-off redirects without a deploy
| in Tools → Redirects (stored in the alma_redirects table). This map is
| checked first and shown read-only in that screen.
|
| Keys are request paths, matched case-insensitively with or without the
| trailing slash. Values are a path on this site, an https URL on another
| site, or ['to' => ..., 'status' => 302]. The query string of the request is
| carried over unless the target has its own.
|
| Example:
|   '/old-pricing' => '/pricing/',
|   '/promo' => ['to' => '/offers/spring/', 'status' => 302],
|   '/docs' => 'https://docs.example.com/',
|
*/

return [

];
