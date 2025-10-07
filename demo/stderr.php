<?php

declare(strict_types=1);

use RobertWesner\AWDY\AWDY;
use RobertWesner\AWDY\Template\Templates\SimpleTemplate;

require __DIR__ . '/../vendor/autoload.php';

const LIMIT = 13370;
const PROGRESS_AFTER = 100;

AWDY::setUp(new SimpleTemplate(), output: STDERR);

// none of this should show up in stdout
$i = 0;
while (true) {
    if ($i >= LIMIT) {
        break;
    }

    usleep(rand(100, 500));

    if (($i % 777) === 0) {
        AWDY::printf('%d is your lucky number!' . PHP_EOL, $i);
    }

    $i++;

    if ($i >= LIMIT || ($i % PROGRESS_AFTER) === 0) {
        AWDY::progress($i / LIMIT, $i, LIMIT);
    }
}

AWDY::clear();

echo "This should be greppable!";
