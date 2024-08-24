<h1 align="center">
AWDY
</h1>

<div align="center">

![](https://github.com/RobertWesner/awdy/actions/workflows/tests.yml/badge.svg)
![](https://raw.githubusercontent.com/RobertWesner/awdy/image-data/coverage.svg)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](../../raw/main/LICENSE.txt)

</div>

Are We Done Yet? Spice up your PHP-scripts with progress-bars and more!

## Installation

```bash
composer require robertwesner/awdy @dev
```

## Use

```php
<?php

use RobertWesner\AWDY\AWDY;
use RobertWesner\AWDY\Template\Templates\SimpleTemplate;

require __DIR__ . '/../vendor/autoload.php';

const LIMIT = 1337;
const PROGRESS_AFTER = 100;

// Set up AWDY with a simple template
AWDY::setUp(new SimpleTemplate());

$i = 0;
while (true) {
    if ($i >= LIMIT) {
        break;
    }

    if (($i % 77) === 0) {
        // print to the logging section
        AWDY::printf('%d is your lucky number!' . PHP_EOL, $i);
    }

    $i++;

    if ($i >= LIMIT || ($i % PROGRESS_AFTER) === 0) {
        // update the progress (floating point number 0 to 1)
        AWDY::progress($i / LIMIT);
    }
}
```

## Templates

### JustProgress

```
00:00:23  418/1337 [==========>                      ] Memory[00.12%]:  15659904/134217728
```

### Simple

```
.----------------------------------------------------------.
| 13370/13370 [==========================================] |
+----------------------------------------------------------+
| 0 is your lucky number!                                  |
| 777 is your lucky number!                                |
| 1554 is your lucky number!                               |
| 2331 is your lucky number!                               |
| 3108 is your lucky number!                               |
| 3885 is your lucky number!                               |
| 4662 is your lucky number!                               |
| 5439 is your lucky number!                               |
| 6216 is your lucky number!                               |
| 6993 is your lucky number!                               |
| 7770 is your lucky number!                               |
| 8547 is your lucky number!                               |
| 9324 is your lucky number!                               |
| 10101 is your lucky number!                              |
| 10878 is your lucky number!                              |
| 11655 is your lucky number!                              |
| 12432 is your lucky number!                              |
| 13209 is your lucky number!                              |
'----------------------------------------------------------'
```

## Create your own template

Templates are easy to create, have a look at the [official ones](src/Template/Templates).

[//]: # (I should create a wiki page for templating)

## Demo

### Keeping it simple

![](readme/1.gif)

### Adding some flair

![](readme/2.gif)

### Always with dynamic size

![](readme/3.gif)

## Templates
