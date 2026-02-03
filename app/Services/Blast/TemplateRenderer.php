<?php

namespace App\Services\Blast;

class TemplateRenderer
{
    
    public function render(string $body, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $body = str_replace(
                '{' . $key . '}',
                (string) ($value ?? ''),
                $body
            );
        }

        return $body;
    }
}
