<?php

declare(strict_types=1);

use PhpCodeMinifier\MinifierFactory;

require __DIR__ . '/vendor/autoload.php';

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

$minifier = MinifierFactory::create();
$minified = '';
foreach (getFiles(__DIR__ . '/src') as $file) {
    $minified .= substr(
        preg_replace(
            '/declare\s*\(\s*strict_types\s*=\s*\d+\s*\)\s*;?/',
            '',
            $minifier->minifyFile($file),
        ),
        6,
    );
}
$minified = preg_replace('/namespace\s*(.*?)\s*;(.*?)(?=namespace|$)/s', 'namespace $1{$2}', $minified);

mkdir(__DIR__ . '/dist', recursive: true);
file_put_contents(__DIR__ . '/dist/AWDY.php', '<?php /** @noinspection ALL */ ' . $minified);
