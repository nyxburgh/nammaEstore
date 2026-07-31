<?php
// app/helpers/config.php
//
// Dot-notation config reader, e.g. config('providers.payment.active').
// First segment = filename under config/ (without .php), rest = array path.

function config(string $key, mixed $default = null): mixed
{
    static $cache = [];

    $segments = explode('.', $key);
    $file     = array_shift($segments);

    if (!array_key_exists($file, $cache)) {
        $path = BASE_PATH . "/config/{$file}.php";
        $cache[$file] = file_exists($path) ? require $path : [];
    }

    $value = $cache[$file];
    foreach ($segments as $seg) {
        if (!is_array($value) || !array_key_exists($seg, $value)) {
            return $default;
        }
        $value = $value[$seg];
    }
    return $value;
}
