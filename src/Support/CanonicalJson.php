<?php
namespace KatebSaber\TelegramCore\Support;
final class CanonicalJson
{
    public static function encode(array $data): string
    { return json_encode(self::normalize($data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); }
    private static function normalize(mixed $value): mixed
    {
        if (! is_array($value)) return $value;
        $isList = array_keys($value) === range(0, count($value) - 1);
        if ($isList) return array_map([self::class, 'normalize'], $value);
        ksort($value); $out=[]; foreach ($value as $k=>$v) $out[(string)$k]=self::normalize($v); return $out;
    }
}
