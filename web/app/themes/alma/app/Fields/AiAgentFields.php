<?php

namespace App\Fields;

use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Field;

class AiAgentFields extends Field
{
    public function fields(): array
    {
        $fields = Builder::make('ai_agent_fields');

        $fields
            ->setLocation('post_type', '==', 'ai_conversation');

        $fields
            ->addText('agent_id', [
                'label' => 'Agent ID',
                'required' => true,
            ])
            ->addText('thread_id', [
                'label' => 'Thread ID',
                'required' => true,
            ])
            ->addNumber('user_id', [
                'label' => 'User ID',
                'required' => false,
            ])
            ->addTextarea('messages', [
                'label' => 'Messages (JSON)',
                'required' => true,
            ]);

        return $fields->build();
    }
}