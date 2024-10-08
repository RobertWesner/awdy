<?php

use RobertWesner\AWDY\AWDY;
use RobertWesner\AWDY\Template\Templates\DefaultTemplate;

require __DIR__ . '/../vendor/autoload.php';

const LIMIT = 133700;
const PROGRESS_AFTER = 100;

AWDY::setUp(new DefaultTemplate());

$i = 0;
while (true) {
    if ($i >= LIMIT) {
        break;
    }

    usleep(rand(100, 500));

    if (($i % 77) === 0) {
        AWDY::printf('%d ', $i);
    }

    $i++;

    if ($i >= LIMIT || ($i % PROGRESS_AFTER) === 0) {
        AWDY::progress($i / LIMIT);
    }
}
