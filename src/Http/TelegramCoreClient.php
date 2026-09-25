<?php

namespace KatebSaber\TelegramCore\Http;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use KatebSaber\TelegramCore\Exceptions\TelegramCoreException;
use KatebSaber\TelegramCore\Exceptions\UpstreamException;
use KatebSaber\TelegramCore\Support\Signer;

final class TelegramCoreClient
{
    /** @var array<string,mixed> */
    private $config;

    /** @var ClientInterface|null */
    private $http;

    public function __construct(array $config, ClientInterface $http = null)
    {
        $this->config = $config;
        $this->http = $http;
    }

    public function capabilities(?string $botUuid = null): array
    {
        return $this->json('GET', $this->path($botUuid).'/capabilities', [], null, $botUuid);
    }

    public function callForBot(string $botUuid, string $method, array $parameters = [], ?string $idempotencyKey = null): array
    {
        return $this->call($method, $parameters, $idempotencyKey, $botUuid);
    }

    public function call(string $method, array $parameters = [], ?string $idempotencyKey = null, ?string $botUuid = null): array
    {
        return $this->json('POST', $this->path($botUuid).'/telegram/'.rawurlencode($method), $parameters, $idempotencyKey, $botUuid);
    }

    /** @param int|string $chatId */
    public function sendMessage($chatId, string $text, array $options = [], ?string $idempotencyKey = null): array
    {
        return $this->call('sendMessage', ['chat_id' => $chatId, 'text' => $text] + $options, $idempotencyKey);
    }

    /** @param int|string $chatId */
    public function sendPhoto($chatId, string $photo, array $options = [], ?string $idempotencyKey = null): array
    {
        return $this->call('sendPhoto', ['chat_id' => $chatId, 'photo' => $photo] + $options, $idempotencyKey);
    }

    /** @param int|string $chatId */
    public function sendLocation($chatId, float $latitude, float $longitude, array $options = [], ?string $idempotencyKey = null): array
    {
        return $this->call('sendLocation', ['chat_id' => $chatId, 'latitude' => $latitude, 'longitude' => $longitude] + $options, $idempotencyKey);
    }

    public function stageUpload(string $path, ?string $fieldName = null, ?string $idempotencyKey = null, ?string $botUuid = null): array
    {
        if (! is_file($path)) {
            throw new TelegramCoreException('Upload file not found: '.$path);
        }
        if ($fieldName !== null && $fieldName !== '' && $fieldName !== 'file') {
            throw new TelegramCoreException('Staged upload field must be `file`.');
        }

        $bot = $this->bot($botUuid);
        $timestamp = time();
        $files = ['file' => ['path' => $path, 'name' => basename($path)]];
        $signature = Signer::multipart($this->secret(), $timestamp, [], $files, $idempotencyKey);
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new TelegramCoreException('Upload file is not readable: '.$path);
        }

        try {
            $response = $this->client()->request('POST', $this->path($bot).'/uploads', [
                'headers' => $this->headers($bot, $timestamp, $signature, $idempotencyKey),
                'multipart' => [[
                    'name' => 'file',
                    'contents' => $handle,
                    'filename' => basename($path),
                ]],
            ]);
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }

        return $this->decode($response->getStatusCode(), $this->decodeJson((string) $response->getBody()));
    }

    public function downloadFile(string $fileId, ?string $botUuid = null): string
    {
        $bot = $this->bot($botUuid);
        $body = (string) json_encode(['file_id' => $fileId], JSON_UNESCAPED_SLASHES);
        $timestamp = time();
        $signature = Signer::json($this->secret(), $timestamp, $body);
        $response = $this->client()->request('POST', $this->path($bot).'/files/download', [
            'headers' => $this->headers($bot, $timestamp, $signature) + ['Content-Type' => 'application/json'],
            'body' => $body,
        ]);
        $status = $response->getStatusCode();
        if ($status >= 400) {
            throw new UpstreamException($status, $this->decodeJson((string) $response->getBody()));
        }
        return (string) $response->getBody();
    }

    private function json(string $verb, string $path, array $payload, ?string $idempotencyKey, ?string $botUuid): array
    {
        $bot = $this->bot($botUuid);
        $body = $verb === 'GET' ? '' : (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $timestamp = time();
        $signature = Signer::json($this->secret(), $timestamp, $body, $idempotencyKey);
        $headers = $this->headers($bot, $timestamp, $signature, $idempotencyKey);
        $options = ['headers' => $headers];
        if ($verb !== 'GET') {
            $options['headers']['Content-Type'] = 'application/json';
            $options['body'] = $body;
        }
        $response = $this->client()->request($verb, $path, $options);
        return $this->decode($response->getStatusCode(), $this->decodeJson((string) $response->getBody()));
    }

    private function decode(int $status, array $body): array
    {
        if ($status >= 400) {
            throw new UpstreamException($status, $body);
        }
        return $body;
    }

    private function decodeJson(string $body): array
    {
        $decoded = json_decode($body, true);
        return is_array($decoded) ? $decoded : ['raw' => $body];
    }

    private function client(): ClientInterface
    {
        if ($this->http instanceof ClientInterface) {
            return $this->http;
        }
        $this->http = new Client([
            'base_uri' => $this->base(),
            'timeout' => max(1, (int) ($this->config['timeout'] ?? 30)),
            'connect_timeout' => max(1, (int) ($this->config['connect_timeout'] ?? 7)),
            'http_errors' => false,
        ]);
        return $this->http;
    }

    private function headers(string $bot, int $timestamp, string $signature, ?string $idempotencyKey = null): array
    {
        return array_filter([
            'Accept' => 'application/json',
            'X-TGCore-Bot-UUID' => $bot,
            'X-TGCore-Timestamp' => (string) $timestamp,
            'X-TGCore-Signature' => $signature,
            'X-TGCore-Idempotency-Key' => $idempotencyKey,
        ], static function ($value) { return $value !== null && $value !== ''; });
    }

    private function path(?string $botUuid = null): string
    {
        return '/api/tgcore/v2/consumer/bots/'.rawurlencode($this->bot($botUuid));
    }

    private function bot(?string $botUuid = null): string
    {
        $value = $botUuid ?: (string) ($this->config['bot_uuid'] ?? '');
        if ($value === '') {
            throw new TelegramCoreException('TGCORE_BOT_UUID is not configured.');
        }
        return $value;
    }

    private function secret(): string
    {
        $value = (string) ($this->config['secret'] ?? '');
        if ($value === '') {
            throw new TelegramCoreException('TGCORE_CONSUMER_SECRET is not configured.');
        }
        return $value;
    }

    private function base(): string
    {
        $value = rtrim((string) ($this->config['url'] ?? ''), '/').'/';
        if (! filter_var($value, FILTER_VALIDATE_URL) || strtolower((string) parse_url($value, PHP_URL_SCHEME)) !== 'https') {
            throw new TelegramCoreException('TGCORE_URL must be a valid HTTPS URL.');
        }
        return $value;
    }
}
