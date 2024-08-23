<?php

// TODO: set up file watcher or git hook

declare(strict_types=1);

use PhpCodeMinifier\MinifierFactory;

require __DIR__ . '/vendor/autoload.php';

// TODO: change to static call once my pull request gets accepted
$minifier = (new MinifierFactory())->create();

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
    // TODO: error handling
    $minified .= substr($minifier->minifyString($file), 6);
}

// TODO: enable dist output once heredoc/nowdoc are minified correctly
//file_put_contents(__DIR__ . '/dist/AWDY.php', '<?php ' . $minified);
file_put_contents(__DIR__ . '/dist/AWDY.php', '<?php die(\'WIP\');');
