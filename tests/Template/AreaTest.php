<?php

namespace RobertWesner\AWDY\Tests\Template;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use RobertWesner\AWDY\AnsiEscape;
use RobertWesner\AWDY\Template\Area;
use RobertWesner\AWDY\Template\Buffer;
use RobertWesner\AWDY\Tests\BaseTestCase;

#[CoversClass(Area::class)]
#[UsesClass(Buffer::class)]
final class AreaTest extends BaseTestCase
{
    private string $changingValue = 'Hello';

    public function test(): void
    {
        $area = Area::create(5, 2, -6, -3, function (Buffer $buffer) {
            $buffer->draw(0, 0, 'A');
            $buffer->draw(1, 1, $this->changingValue);
            $buffer->draw(-1, 0, 'B');
            $buffer->draw(0, -1, 'C');
            $buffer->draw(-1, -1, 'D');
        });

        // dirty by default
        ob_start();
        $area->render(20, 10);
        self::assertSame(
            sprintf(
                '%sA        B%s Hello    %s          %s          %s          %sC        D',
                AnsiEscape::moveTo(6, 3),
                AnsiEscape::moveTo(6, 4),
                AnsiEscape::moveTo(6, 5),
                AnsiEscape::moveTo(6, 6),
                AnsiEscape::moveTo(6, 7),
                AnsiEscape::moveTo(6, 8),
            ),
            ob_get_clean(),
        );

        // should not render since it is not dirty
        ob_start();
        $area->render(80, 32);
        self::assertSame('', ob_get_clean());

        // mark area as dirty and re-render it
        $this->changingValue = 'World';
        $area->dirty();
        ob_start();
        $area->render(20, 10);
        self::assertSame(
            sprintf(
                '%sA        B%s World    %s          %s          %s          %sC        D',
                AnsiEscape::moveTo(6, 3),
                AnsiEscape::moveTo(6, 4),
                AnsiEscape::moveTo(6, 5),
                AnsiEscape::moveTo(6, 6),
                AnsiEscape::moveTo(6, 7),
                AnsiEscape::moveTo(6, 8),
            ),
            ob_get_clean(),
        );
    }
}
