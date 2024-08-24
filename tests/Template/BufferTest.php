<?php

declare(strict_types=1);

namespace RobertWesner\AWDY\Tests\Template;

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
            '5x3 buffer multiple characters' => [
                <<<EOF
                     
                 :)  
                     
                EOF,
                5,
                3,
                function (Buffer $buffer): void {
                    $buffer->draw(1, 1, ':)');
                }
            ],
            '10x5 draw multiline' => [
                <<<EOF
                          
                 +^-^+    
                 |. .|    
                 | ^ |~   
                 +---+    
                          
                EOF,
                10,
                6,
                function (Buffer $buffer): void {
                    $buffer->draw(1, 1, <<<EOF
                    +^-^+
                    |. .|
                    | ^ |~
                    +---+
                    EOF);
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

    // TODO: test ansi escape injection

    // TODO: create a test for buffer draw overflow (both x and y)
}
