<?php

namespace App\Infrastructure\Observability\IntegrationLog;

/**
 * Strips secrets from what the integration log stores: header values, query parameters and JSON
 * keys whose names look like credentials. Over-redacting a harmless field is fine; storing one
 * API key in a table every admin can read is not.
 */
class Redactor
{
    public const MASK = '[redacted]';

    /** @var list<string> */
    private array $keywords;

    /**
     * @param  list<string>|null  $keywords
     */
    public function __construct(?array $keywords = null)
    {
        $this->keywords = array_map('strtolower', $keywords ?? (array) config('observability.integration_log.redact', []));
    }

    public function isSensitive(string $name): bool
    {
        $name = strtolower($name);

        foreach ($this->keywords as $keyword) {
            if ($keyword !== '' && str_contains($name, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $headers
     * @return array<string, mixed>
     */
    public function headers(array $headers): array
    {
        $clean = [];

        foreach ($headers as $name => $value) {
            $clean[$name] = $this->isSensitive((string) $name) ? self::MASK : $value;
        }

        return $clean;
    }

    public function url(string $url): string
    {
        $query = parse_url($url, PHP_URL_QUERY);

        if (! is_string($query) || $query === '') {
            return $this->stripUserInfo($url);
        }

        parse_str($query, $params);
        $clean = http_build_query($this->array($params));

        return $this->stripUserInfo(str_replace('?'.$query, $clean === '' ? '' : '?'.$clean, $url));
    }

    /**
     * Redact a body: JSON and form-encoded bodies key by key, anything else as-is, then cap it.
     */
    public function body(?string $body, int $maxBytes): ?string
    {
        if ($body === null || $body === '') {
            return $body;
        }

        $decoded = json_decode($body, true);

        if (is_array($decoded)) {
            $body = (string) json_encode($this->array($decoded), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } elseif (preg_match('/^[^\s=&]+=[^\s]*(&[^\s=&]+=[^\s]*)*$/', $body)) {
            parse_str($body, $form);
            $body = http_build_query($this->array($form));
        }

        return strlen($body) > $maxBytes ? substr($body, 0, $maxBytes).'…[truncated]' : $body;
    }

    /**
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    public function array(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($key) && $this->isSensitive($key)) {
                $data[$key] = self::MASK;
            } elseif (is_array($value)) {
                $data[$key] = $this->array($value);
            }
        }

        return $data;
    }

    private function stripUserInfo(string $url): string
    {
        return (string) preg_replace('#^([a-z][a-z0-9+.-]*://)[^/@]+@#i', '$1', $url);
    }
}
