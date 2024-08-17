<?php

namespace RobertWesner\AWDY;

use RobertWesner\AWDY\Template\TemplateInterface;

final class AWDY
{
    private static int $previousWidth = 0;
    private static int $previousHeight = 0;

    private static TemplateInterface $template;

    private static function getWidth(): int
    {
        return (int)exec('tput cols');
    }

    private static function getHeight(): int
    {
        return (int)exec('tput lines');
    }

    private static function render(): void
    {
        $width = self::getWidth();
        $height = self::getHeight();

        if ($width !== self::$previousWidth || $height !== self::$previousHeight) {
            echo self::$template->defineBorder()->getBuffer(self::getWidth(), self::getHeight());
            echo AnsiEscape::moveToBeginning();

            self::$previousWidth = $width;
            self::$previousHeight = $height;
        }

        foreach (self::$template->getAreas() as $area) {
            $area->render($width, $height);
            echo AnsiEscape::resetColor();
            echo AnsiEscape::moveToBeginning();
        }
    }

    public static function setUp(TemplateInterface $template): void
    {
        self::$template = $template;
        self::render();
    }

    /**
     * Print to the Template.
     */
    public static function echo(string $echo): void
    {
        self::$template->handleEcho($echo);
        self::render();
    }

    public static function printf(string $string, mixed ...$args): void
    {
        self::echo(sprintf($string, ...$args));
    }

    /**
     * @param float $progress Progress from 0 to 1
     */
    public static function progress(float $progress, int $current = 0, int $total = 0): void
    {
        self::$template->handleProgress($progress, $current, $total);
        self::render();
    }
}
