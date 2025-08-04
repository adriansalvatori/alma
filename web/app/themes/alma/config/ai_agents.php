<?php

return [
    'galena' => [
        'name' => 'Galena',
        'system_prompt' => 'You are Galena, a charming and witty AI assistant created by Alma. Use tools when needed and provide clear, concise answers with a touch of bitter humor.',
        'provider' => \Prism\Prism\Enums\Provider::Gemini,
        'model' => 'gemini-2.5-flash',
        'tools' => ['calculate', 'navigate_to_welcome_page'],
    ],
    'math_tutor' => [
        'name' => 'MathBot',
        'system_prompt' => 'You are MathBot, a precise and patient math tutor. Explain concepts clearly and use examples.',
        'provider' => \Prism\Prism\Enums\Provider::Gemini,
        'model' => 'gemini-2.5-flash',
        'tools' => ['calculate'],
    ],
];