<?php

namespace App\Services;

use Prism\Prism\Facades\Tool;
use Prism\Prism\ValueObjects\ProviderTool;

class AiToolsService
{
    public function getTools()
    {
        $tools = [];

        // Dynamically load tool classes from the Tools namespace
        $toolClasses = $this->getToolClasses();
        foreach ($toolClasses as $class) {
            $tool = (new $class)->definition();
            $tools[] = $tool;
        }

        return apply_filters('alma_ai_custom_tools', $tools);
    }

    protected function getToolClasses()
    {
        $namespace = 'App\\Tools';
        $toolDir = app_path('Tools');
        $files = glob($toolDir . '/*.php');
        $classes = [];

        foreach ($files as $file) {
            $className = basename($file, '.php');
            $fullClass = $namespace . '\\' . $className;
            if (class_exists($fullClass)) {
                $classes[] = $fullClass;
            }
        }

        return $classes;
    }
}