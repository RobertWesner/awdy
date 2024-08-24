<?php

use RobertWesner\AWDY\AWDY;
use RobertWesner\AWDY\Template\Templates\JustProgressTemplate;

require __DIR__ . '/../vendor/autoload.php';

const LIMIT = 1337;
const CLEAR_UP_MEM_AFTER = 10;

AWDY::setUp(new JustProgressTemplate());

$memorySpam = [];

$i = 0;
while (true) {
    if ($i >= LIMIT) {
        break;
    }

    $memorySpam[] = str_repeat('fill memory up real good', rand(1, 100000));

    usleep(rand(1000, 100000));

    $i++;

    if (($i % CLEAR_UP_MEM_AFTER) === 0) {
        $clean = rand(1, CLEAR_UP_MEM_AFTER * 2);
        for ($memCounter = 0; $memCounter < $clean; $memCounter++) {
            if (isset($memorySpam[$memCounter])) {
                unset($memorySpam[$memCounter]);
            }
        }
        $memorySpam = array_values($memorySpam);

        gc_collect_cycles();
    }

    AWDY::progress($i / LIMIT, $i, LIMIT);
}
