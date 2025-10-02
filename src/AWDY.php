<?php

declare(strict_types=1);

namespace RobertWesner\AWDY;

use RobertWesner\AWDY\Template\Area;
use RobertWesner\AWDY\Template\TemplateInterface;

final class AWDY
{
    private static ?int $fixedWidth = null;
    private static ?int $fixedHeight = null;
    private static int $previousWidth = 0;
    private static int $previousHeight = 0;

    private static TemplateInterface $template;

    private static function getWidth(): int
    {
        return self::$fixedWidth ?? (int)exec('tput cols');
    }

    private static function getHeight(): int
    {
        return self::$fixedHeight ?? (int)exec('tput lines');
    }

    private static function render(): void
    {
        $width = self::getWidth();
        $height = self::getHeight();

        if ($width !== self::$previousWidth || $height !== self::$previousHeight) {
            echo self::$template->getBorder()->getBuffer(self::getWidth(), self::getHeight());
            echo AnsiEscape::moveToBeginning();

            self::$previousWidth = $width;
            self::$previousHeight = $height;

            // mark all as dirty on resize so everything will be properly rendered again
            array_map(fn (Area $area) => $area->dirty(), self::$template->getAreas());
        }

        foreach (self::$template->getAreas() as $area) {
            $area->render($width, $height);
            echo AnsiEscape::resetColor();
            echo AnsiEscape::moveToBeginning();
        }
    }

    public static function setUp(TemplateInterface $template, ?int $width = null, ?int $height = null): void
    {
        self::$template = $template;
        self::$fixedWidth = $width;
        self::$fixedHeight = $height;

        echo AnsiEscape::clear();
        echo AnsiEscape::moveToBeginning();

        self::render();
    }

    /**
     * Print to the Template.
     */
    public static function echo(string ...$echo): void
    {
        self::$template->handleEcho(implode('', $echo));
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
