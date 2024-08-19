<?php

use RobertWesner\AWDY\AnsiEscape;
use RobertWesner\AWDY\AWDY;
use RobertWesner\AWDY\Template\Templates\SnugglyTemplate;

require __DIR__ . '/../vendor/autoload.php';

const LIMIT = 1337;
const PROGRESS_AFTER = 10;

AWDY::setUp(new SnugglyTemplate(AnsiEscape::fg(130)));

$i = 0;
while (true) {
    if ($i >= LIMIT) {
        break;
    }

    usleep(rand(3000, 20000));

    if (($i % 77) === 0) {
        AWDY::printf('%d is your lucky number!' . PHP_EOL, $i);
    }

   $i++;

    if ($i >= LIMIT || ($i % PROGRESS_AFTER) === 0) {
        AWDY::progress($i / LIMIT);
    }
}
