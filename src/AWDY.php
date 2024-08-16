<?php

namespace RobertWesner\AWDY;

use RobertWesner\AWDY\Template\TemplateInterface;

final class AWDY
{
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

        foreach (self::$template->getAreas() as $area) {
            echo AnsiEscape::moveToBeginning();
            $area->render($width, $height);
        }
    }

    public static function setUp(TemplateInterface $template): void
    {
        self::$template = $template;

        // TODO: refresh border when resizing window
        echo self::$template->defineBorder()->getBuffer(self::getWidth(), self::getHeight());
        echo AnsiEscape::moveToBeginning();

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

    public static function sprintf(string $string, mixed ...$args): void
    {
        self::echo(sprintf($string, ...$args));
    }

    /**
     * @param float $progress Progress from 0 to 1
     */
    public static function progress(float $progress): void
    {
        self::$template->handleProgress($progress);
        self::render();
    }
}
