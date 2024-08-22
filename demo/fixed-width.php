<?php

use RobertWesner\AWDY\AWDY;
use RobertWesner\AWDY\Template\Templates\SimpleTemplate;

require __DIR__ . '/../vendor/autoload.php';

const LIMIT = 1337;

AWDY::setUp(new SimpleTemplate(), 62, 16);

$i = 0;
while (true) {
    if ($i >= LIMIT) {
        break;
    }

    usleep(rand(100, 500));

    if (($i % 77) === 0) {
        AWDY::printf('%d is your lucky number!' . PHP_EOL, $i);
    }

    $i++;

    AWDY::progress($i / LIMIT);
}
