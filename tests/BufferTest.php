<?php

namespace RobertWesner\AWDY\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RobertWesner\AWDY\Template\Buffer;

#[CoversClass(Buffer::class)]
final class BufferTest extends TestCase
{
    public static function drawDataProvider(): array
    {
        return [
            '1x1 buffer no draw' => [
                <<<EOF
                 
                EOF,
                1,
                1,
                function (Buffer $buffer): void {
                }
            ],
            '1x1 buffer' => [
                <<<EOF
                !
                EOF,
                1,
                1,
                function (Buffer $buffer): void {
                    $buffer->draw(0, 0, '!');
                }
            ],
            '5x3 buffer' => [
                <<<EOF
                A   B
                     
                C   D
                EOF,
                5,
                3,
                function (Buffer $buffer): void {
                    $buffer->draw(0, 0, 'A');
                    $buffer->draw(-1, 0, 'B');
                    $buffer->draw(0, -1, 'C');
                    $buffer->draw(-1, -1, 'D');
                }
            ],
        ];
    }

    #[DataProvider('drawDataProvider')]
    public function testDraw(string $expected, int $bufferWidth, int $bufferHeight, callable $draw): void
    {
        $buffer = new Buffer($bufferWidth, $bufferHeight);
        $draw($buffer);

        self::assertSame($bufferWidth, $buffer->getWidth());
        self::assertSame($bufferHeight, $buffer->getHeight());
        self::assertSame($expected, (string)$buffer);
    }
}
