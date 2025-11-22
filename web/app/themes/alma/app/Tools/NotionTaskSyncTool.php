<?php

namespace App\Tools;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Prism\Prism\Facades\Tool;

class NotionTaskSyncTool
{
    public function definition()
    {
        return Tool::as('notiontasks')
            ->for('Sync and create tasks in a Notion board from a list of tasks')
            ->withStringParameter('action', 'Action to perform (fields, create, refresh)', true)
            ->withStringParameter('tasks', 'Comma-separated list of tasks or JSON array of objects', false)
            ->using(function (string $action, ?string $database_id = null, ?string $tasks = null) {

                Log::debug("NotionTaskSyncTool: action={$action}, database_id={$database_id}, tasks={$tasks}");

                // Defaults from .env
                $database_id = env('NOTION_DEFAULT_DATABASE_ID');
                $token = env('NOTION_TOKEN');
                $notionVersion = env('NOTION_VERSION', '2022-06-28');
                $baseUrl = env('NOTION_API_BASE', 'https://api.notion.com/v1');

                if (empty($database_id) || empty($token)) {
                    return json_encode([
                        'type' => 'error',
                        'data' => ['message' => 'Missing Notion credentials or database ID. Check your .env.'],
                        'view' => 'livewire.tools.error',
                    ]);
                }

                $headers = [
                    'Authorization' => "Bearer {$token}",
                    'Notion-Version' => $notionVersion,
                    'Content-Type' => 'application/json',
                ];

                $cachePath = "notion/{$database_id}.json";

                // ACTION: refresh (force re-fetch schema)
                if ($action === 'refresh') {
                    Log::debug("Deleting cache: {$cachePath}");
                    Storage::delete($cachePath);
                    return $this->fetchAndCacheDatabase($baseUrl, $headers, $database_id, $cachePath);
                }

                // ACTION: fields
                if ($action === 'fields') {
                    // Use cache if available
                    if (Storage::exists($cachePath)) {
                        Log::debug("Using cached database: {$cachePath}");
                        $cached = json_decode(Storage::get($cachePath), true);
                        return json_encode([
                            'type' => 'notiontasks',
                            'data' => [
                                'action' => 'fields',
                                'fields' => $cached['fields'] ?? [],
                                'title' => $cached['title'] ?? 'Untitled Database',
                                'cached' => true,
                            ],
                            'view' => 'livewire.tools.notiontasks',
                        ]);
                    }

                    Log::debug("Fetching database schema from Notion...");
                    return $this->fetchAndCacheDatabase($baseUrl, $headers, $database_id, $cachePath);
                }

                // ACTION: create
                if ($action === 'create') {
                    if (empty($tasks)) {
                        return json_encode([
                            'type' => 'error',
                            'data' => ['message' => 'No tasks provided.'],
                            'view' => 'livewire.tools.error',
                        ]);
                    }

                    Log::debug("Creating tasks in database: {$database_id}");

                    $parsedTasks = json_decode($tasks, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $parsedTasks = array_map('trim', explode(',', $tasks));
                    }

                    $created = [];
                    foreach ($parsedTasks as $task) {
                        $taskName = is_array($task) ? ($task['name'] ?? 'Unnamed Task') : $task;

                        $payload = [
                            'parent' => ['database_id' => $database_id],
                            'properties' => [
                                'Name' => [
                                    'title' => [
                                        ['text' => ['content' => $taskName]],
                                    ],
                                ],
                            ],
                        ];

                        if (is_array($task)) {
                            foreach ($task as $key => $value) {
                                if ($key === 'name') continue;
                                $payload['properties'][$key] = ['rich_text' => [['text' => ['content' => $value]]]];
                            }
                        }

                        $response = Http::withHeaders($headers)->post("{$baseUrl}/pages", $payload);

                        if ($response->successful()) {
                            $created[] = ['task' => $taskName, 'status' => 'created'];
                        } else {
                            Log::debug("Error creating task {$taskName}: " . $response->status());
                            $created[] = ['task' => $taskName, 'status' => 'error'];
                        }
                    }

                    return json_encode([
                        'type' => 'notiontasks',
                        'data' => [
                            'action' => 'create',
                            'results' => $created,
                        ],
                        'view' => 'livewire.tools.notiontasks',
                    ]);
                }

                return json_encode([
                    'type' => 'error',
                    'data' => ['message' => 'Invalid action. Use "fields", "create", or "refresh".'],
                    'view' => 'livewire.tools.error',
                ]);
            });
    }

    /**
     * Fetch database schema and cache it locally
     */
    private function fetchAndCacheDatabase(string $baseUrl, array $headers, string $database_id, string $cachePath)
    {
        Log::debug("Fetching database schema from Notion...");

        $response = Http::withHeaders($headers)->get("{$baseUrl}/databases/{$database_id}");

        if ($response->failed()) {
            Log::debug("Failed fetching Notion database: " . $response->status());
            return json_encode([
                'type' => 'error',
                'data' => ['message' => 'Failed to fetch Notion database structure.'],
                'view' => 'livewire.tools.error',
            ]);
        }

        $data = $response->json();
        $fields = array_keys($data['properties'] ?? []);

        Log::debug("Caching database schema to: {$cachePath}");

        Storage::put($cachePath, json_encode([
            'fields' => $fields,
            'title' => $data['title'][0]['plain_text'] ?? 'Untitled Database',
            'fetched_at' => now()->toDateTimeString(),
        ]));

        return json_encode([
            'type' => 'notiontasks',
            'data' => [
                'action' => 'fields',
                'fields' => $fields,
                'title' => $data['title'][0]['plain_text'] ?? 'Untitled Database',
                'cached' => false,
            ],
            'view' => 'livewire.tools.notiontasks',
        ]);
    }
}

