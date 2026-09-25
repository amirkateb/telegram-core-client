<?php

namespace KatebSaber\TelegramCore\Support;

final class CanonicalJson
{
    public static function encode(array $data): string
    {
        return (string) json_encode(self::normalize($data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /** @param mixed $value @return mixed */
    private static function normalize($value)
    {
        if (! is_array($value)) {
            return $value;
        }

        $keys = array_keys($value);
        $isList = $keys === range(0, count($value) - 1);

        if ($value === [] || $isList) {
            return array_map([self::class, 'normalize'], $value);
        }

        ksort($value);
        $out = [];
        foreach ($value as $key => $item) {
            $out[(string) $key] = self::normalize($item);
        }

        return $out;
    }
}
