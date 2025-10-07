<?php

declare(strict_types=1);

namespace RobertWesner\AWDY;

use RobertWesner\AWDY\Template\Area;
use RobertWesner\AWDY\Template\TemplateInterface;

final class AWDY
{
    private static mixed $outputHandle;
    private static ?int $fixedWidth = null;
    private static ?int $fixedHeight = null;
    private static int $previousWidth = 0;
    private static int $previousHeight = 0;

    private static TemplateInterface $template;

    public static function setUp(
        TemplateInterface $template,
        ?int $width = null,
        ?int $height = null,
        mixed $output = null
    ): void {
        self::$template = $template;
        self::$fixedWidth = $width;
        self::$fixedHeight = $height;
        self::$outputHandle = $output;

        self::__out(AnsiEscape::clear());
        self::__out(AnsiEscape::moveToBeginning());

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

    public static function __out(string $text): void
    {
        if (self::$outputHandle === null) {
            // deliberately not using STDOUT as default, since it does not work with ob_*()
            echo $text;
        } else {
            fwrite(self::$outputHandle, $text);
        }
    }

    /**
     * If you so wish as to clear the screen after being done.
     */
    public static function clear(): void
    {
        self::__out(AnsiEscape::clear());
    }

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
            self::__out((string)self::$template->getBorder()->getBuffer(self::getWidth(), self::getHeight()));
            self::__out(AnsiEscape::moveToBeginning());

            self::$previousWidth = $width;
            self::$previousHeight = $height;

            // mark all as dirty on resize so everything will be properly rendered again
            array_map(fn (Area $area) => $area->dirty(), self::$template->getAreas());
        }

        foreach (self::$template->getAreas() as $area) {
            $area->render($width, $height);
            self::__out(AnsiEscape::resetColor());
            self::__out(AnsiEscape::moveToBeginning());
        }
    }
}
