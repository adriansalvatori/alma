<?php

namespace App\Models;

use Corcel\Model\Post;
use Corcel\Model\Meta\PostMeta;

class AiConversation extends Post
{
    protected $postType = 'ai_conversation';
    protected $connection = 'wordpress';

    public function scopeByThreadAndAgent($query, $threadId, $agentId)
    {
        return $query->whereHas('meta', function ($metaQuery) use ($threadId) {
            $metaQuery->where('meta_key', 'thread_id')->where('meta_value', $threadId);
        })->whereHas('meta', function ($metaQuery) use ($agentId) {
            $metaQuery->where('meta_key', 'agent_id')->where('meta_value', $agentId);
        });
    }

    public function getMessagesAttribute()
    {
        $value = $this->meta->where('meta_key', 'messages')->first()->meta_value ?? '[]';
        return json_decode($value, true) ?? [];
    }

    public function setMessagesAttribute($value)
    {
        $this->meta()->updateOrCreate(
            ['meta_key' => 'messages'],
            ['meta_value' => json_encode($value)]
        );
    }

    public function getThreadIdAttribute()
    {
        return $this->meta->where('meta_key', 'thread_id')->first()->meta_value ?? '';
    }

    public function setThreadIdAttribute($value)
    {
        $this->meta()->updateOrCreate(
            ['meta_key' => 'thread_id'],
            ['meta_value' => $value]
        );
    }

    public function getAgentIdAttribute()
    {
        return $this->meta->where('meta_key', 'agent_id')->first()->meta_value ?? '';
    }

    public function setAgentIdAttribute($value)
    {
        $this->meta()->updateOrCreate(
            ['meta_key' => 'agent_id'],
            ['meta_value' => $value]
        );
    }

    public function getUserIdAttribute()
    {
        return $this->meta->where('meta_key', 'user_id')->first()->meta_value ?? 0;
    }

    public function setUserIdAttribute($value)
    {
        $this->meta()->updateOrCreate(
            ['meta_key' => 'user_id'],
            ['meta_value' => $value]
        );
    }
}