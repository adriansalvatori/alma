<?php

namespace App\Domains\Ai\Abilities\Redirects;

use App\Domains\Seo\Redirects\Redirect;
use App\Domains\Seo\Redirects\RedirectStore;

class ListRedirects extends RedirectAbility
{
    public function __construct(private readonly RedirectStore $store) {}

    public function name(): string
    {
        return 'alma/list-redirects';
    }

    public function label(): string
    {
        return __('List redirects', 'alma');
    }

    public function description(): string
    {
        return __('List the site\'s redirects: editor-managed ones (with id and hit count; these can be deleted) and code-reviewed ones from config/redirects.php (read-only, changed in git).', 'alma');
    }

    public function readOnly(): bool
    {
        return true;
    }

    public function idempotent(): bool
    {
        return true;
    }

    protected function inputProperties(): array
    {
        return [
            'search' => ['type' => 'string', 'description' => 'Match against the source path or target.'],
        ];
    }

    public function execute(array $input): array
    {
        $search = strtolower(trim((string) ($input['search'] ?? '')));
        $matches = fn (string $from, string $to) => $search === '' || str_contains(strtolower($from), $search) || str_contains(strtolower($to), $search);

        $managed = Redirect::query()->orderBy('from_path')->get()
            ->filter(fn (Redirect $redirect) => $matches($redirect->from_path, $redirect->to_url))
            ->map(fn (Redirect $redirect) => [
                'id' => $redirect->id,
                'from' => $redirect->from_path,
                'to' => $redirect->to_url,
                'status' => $redirect->status_code,
                'hits' => $redirect->hits,
                'note' => $redirect->note,
                'source' => 'admin',
            ])
            ->values()
            ->all();

        $code = [];

        foreach ($this->store->codeMap() as $from => $redirect) {
            if ($matches($from, $redirect['to'])) {
                $code[] = ['id' => null, 'from' => $from, 'to' => $redirect['to'], 'status' => $redirect['status'], 'source' => 'code'];
            }
        }

        return ['managed' => $managed, 'code' => $code];
    }
}
