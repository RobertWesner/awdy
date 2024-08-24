<?php

// TODO: set up file watcher or git hook

declare(strict_types=1);

use PhpCodeMinifier\MinifierFactory;

require __DIR__ . '/vendor/autoload.php';

// TODO: enable minfying once this passes: https://github.com/alexandrmazur96/php-code-minifier/pull/3
//$minifier = MinifierFactory::create();

function getFiles(string $directory): array
{
    $results = [];
    foreach (array_diff(scandir($directory), ['.', '..']) as $value) {
        $path = realpath($directory . '/' . $value);
        if (is_dir($path)) {
            $results = array_merge($results, getFiles($path));
        } else {
            $results[] = $path;
        }
    }

    return $results;
}

$minified = '';
foreach (getFiles(__DIR__ . '/src') as $file) {
    $content = file_get_contents($file);
//    $content = $minifier->minifyString($file);

    $content = preg_replace('/declare\s*\(\s*strict_types\s*=\s*\d+\s*\)\s*;?/', '', $content);

    $minified .= substr($content, 6);
}
$minified = preg_replace('/namespace\s*(.*?)\s*;(.*?)(?=namespace|$)/s', 'namespace $1{$2}', $minified);

file_put_contents(__DIR__ . '/dist/AWDY.php', '<?php /** @noinspection ALL */ ' . $minified);
