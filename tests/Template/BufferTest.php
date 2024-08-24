<?php

declare(strict_types=1);

namespace RobertWesner\AWDY\Tests\Template;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use RobertWesner\AWDY\Template\Buffer;
use RobertWesner\AWDY\Tests\BaseTestCase;

#[CoversClass(Buffer::class)]
final class BufferTest extends BaseTestCase
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

    public static function drawTransparencyDataProvider(): array
    {
        return [
            '10x8 buffer' => [
                <<<EOF
                X X X X X 
                 X X X X X
                X X.---.X 
                 X| . . |X
                X |  v  | 
                 X '---' X
                X X X X X 
                 X X X X X
                EOF,
                10,
                8,
                function (Buffer $buffer): void {
                    for ($x = 0; $x < 5; $x++) {
                        for ($y = 0; $y < 8; $y++) {
                            $buffer->draw($x * 2 + $y % 2, $y, 'X');
                        }
                    }

                    $buffer->draw(2, 2, <<<EOF
                    #.---.#
                    | . . |
                    |  v  |
                    #'---'#
                    EOF, transparency: '#');
                }
            ],
            '10x8 transparent center' => [
                <<<EOF
                .'.'.'.'.'.'.'.'.'.'
                '.'.'.'.'.'.'.'.'.'.
                .'.'.'.'.'.'.'.'.'.'
                '.'.'.'.'.'.'.'.'.'.
                .'..----------.'.'.'
                '.'|  .----.  |.'.'.
                .'.|  |'.'.|  |'.'.'
                '.'|  |.'.'|  |.'.'.
                .'.|  '----'  |'.'.'
                '.''----------'.'.'.
                .'.'.'.'.'.'.'.'.'.'
                '.'.'.'.'.'.'.'.'.'.
                .'.'.'.'.'.'.'.'.'.'
                '.'.'.'.'.'.'.'.'.'.
                EOF,
                20,
                14,
                function (Buffer $buffer): void {
                    for ($x = 0; $x < 20; $x++) {
                        for ($y = 0; $y < 14; $y++) {
                            $buffer->draw($x, $y, ['.', '\''][($x + $y) % 2]);
                        }
                    }

                    $buffer->draw(3, 4, <<<EOF
                    .----------.
                    |  .----.  |
                    |  |####|  |
                    |  |####|  |
                    |  '----'  |
                    '----------'
                    EOF, transparency: '#');
                }
            ],
        ];
    }

    #[DataProvider('drawTransparencyDataProvider')]
    public function testDrawTransparency(string $expected, int $bufferWidth, int $bufferHeight, callable $draw): void
    {
        $buffer = new Buffer($bufferWidth, $bufferHeight);
        $draw($buffer);

        self::assertSame($bufferWidth, $buffer->getWidth());
        self::assertSame($bufferHeight, $buffer->getHeight());
        self::assertSame($expected, $this->stripAnsiEscapes((string)$buffer));
    }

    public function testDebug(): void
    {
        $buffer = new Buffer(20, 10);
        $buffer->debug();

        self::assertSame(<<<EOF
        ####################
        ####################
        ####################
        ####################
        ####################
        ####################
        ####################
        ####################
        ####################
        ####################
        EOF, (string)$buffer);
    }

    // TODO: test ansi escape injection

    // TODO: create a test for buffer draw overflow (both x and y)
}
