<?php

declare(strict_types=1);

use RobertWesner\AWDY\AWDY;
use RobertWesner\AWDY\Template\Templates\NullTemplate;

require __DIR__ . '/../vendor/autoload.php';

const LIMIT = 1337;
const PROGRESS_AFTER = 10;

AWDY::setUp(new NullTemplate());
AWDY::progress(0);
AWDY::echo('Scream into the void!', PHP_EOL);
AWDY::progress(0.5);
AWDY::echo('Pure silence.', PHP_EOL);
AWDY::progress(1);

echo 'Nothing ever happens around here.', PHP_EOL;
