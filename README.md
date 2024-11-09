<h1 align="center">
AWDY
</h1>

<div align="center">

![](https://github.com/RobertWesner/awdy/actions/workflows/tests.yml/badge.svg)
![](https://raw.githubusercontent.com/RobertWesner/awdy/image-data/coverage.svg)
![](https://img.shields.io/github/v/release/RobertWesner/awdy)
[![License: MIT](https://img.shields.io/github/license/RobertWesner/awdy)](../../raw/main/LICENSE.txt)

</div>

<div align="center">

<img src="readme/1.gif" width="300">

**Are We Done Yet**? Spice up your PHP-scripts with progress-bars and more!

</div>

## Installation

### Composer (preferred)

```bash
composer require robertwesner/awdy
```

### Single file download

1) Download [/dist/AWDY.php](https://github.com/RobertWesner/awdy/releases/latest/download/AWDY.php).
2) Include the bundled file in your script:
```php
require __DIR__ . '/AWDY.php';
```

### Require from URL

If you wish to not use composer or manually download a file, you can add following code to your script:

```php
$awdyPath = tempnam(sys_get_temp_dir(), 'awdy_');
file_put_contents($awdyPath, fopen('https://github.com/RobertWesner/awdy/releases/latest/download/AWDY.php', 'r'));
require $awdyPath;
```

## Use

More details avaiable in the [wiki page](https://github.com/RobertWesner/awdy/wiki/Using-AWDY).

### At the beginning of your script

Set up AWDY with your choice of template, optionally with a fixed width and height
```php
// Dynamic size takes the full shell window and reacts to size changes
AWDY::setUp(new SimpleTemplate());

// Fixed width 80 and height 32
AWDY::setUp(new SimpleTemplate(), 80, 32);
```

### Change progress

```php
// Only as percentage
AWDY::progress($i / $maxAmount);

// Including current and maximum value
AWDY::progress($i / $maxAmount, $i, $maxAmount);
```

### Append to log

```php
// Unformatted
AWDY::echo("This is some simple informative text!\n");

// Printf formatting
AWDY::printf('You are %d steps away from your destiny! ', $myNumber);
```

## Templates

If the pre-defined templates are not to your liking,
take a look at [how to create your own](https://github.com/RobertWesner/awdy/wiki/Creating-Templates).

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

[//]: # (I should create a wiki page for templating)

## Demo

### Keeping it simple

![](readme/1.gif)

### Adding some color

![](readme/2.gif)

### Allows dynamic size

![](readme/3.gif)
