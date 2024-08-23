<?php

namespace RobertWesner\AWDY\Tests\Template;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RobertWesner\AWDY\Template\Border;
use RobertWesner\AWDY\Template\Buffer;
use RobertWesner\AWDY\Template\Connection;
use RobertWesner\AWDY\Template\Facing;

#[CoversClass(Border::class)]
#[UsesClass(Buffer::class)]
final class BorderTest extends TestCase
{
    public static function dataProvider(): array
    {
        return [
            'empty' => [
                <<<'EOF'
                                                
                                                
                                                
                                                
                                                
                                                
                                                
                                                
                                                
                                                
                                                
                                                
                                                
                                                
                                                
                                                
                                                
                                                
                EOF,
                32,
                18,
                Border::create(),
            ],
            'simple' => [
                <<<'EOF'
                .------------------------------.
                |                              |
                |                              |
                |                              |
                |                              |
                |                              |
                |                              |
                |                              |
                |                              |
                |                              |
                |                              |
                |                              |
                |                              |
                |                              |
                |                              |
                |                              |
                |                              |
                '------------------------------'
                EOF,
                32,
                18,
                Border::create()
                    ->horizontal('-')
                    ->vertical('|')
                    ->corners('.', '.', '\'', '\''),
            ],
            'thick' => [
                <<<'EOF'
                  .--------------------------------------------------.  
                 /    \  \  \  \  \  \  \  \  \  \  \  \  \  \  \  \  \ 
                /   ------------------------------------------------   \
                |  |                                                |  |
                |\ |                                                |\ |
                | \|                                                | \|
                |  |                                                |  |
                |\ |                                                |\ |
                | \|                                                | \|
                |  |                                                |  |
                |\ |                                                |\ |
                | \|                                                | \|
                |  |                                                |  |
                |\ |                                                |\ |
                | \|                                                | \|
                |  |                                                |  |
                |\ |                                                |\ |
                | \|                                                | \|
                |  |                                                |  |
                \   ------------------------------------------------   /
                 \    \  \  \  \  \  \  \  \  \  \  \  \  \  \  \  \  / 
                  '--------------------------------------------------'  
                EOF,
                56,
                22,
                Border::create()
                    ->horizontal(<<<EOF
                    ---
                      \
                    ---
                    EOF)
                    ->vertical(<<<EOF
                    |  |
                    |\ |
                    | \|
                    EOF)
                    ->corners(
                        topLeft: <<<EOF
                          .-
                         /  
                        /   
                        EOF,
                        topRight: <<<EOF
                        -.  
                          \ 
                           \
                        EOF,
                        bottomLeft: <<<EOF
                        \   
                         \  
                          '-
                        EOF,
                        bottomRight: <<<EOF
                           /
                          / 
                        -'  
                        EOF,
                    )
                // TODO: test connections
            ],
            'connections' => [
                <<<EOF
                .------------------------------------------------------------------------------.
                |                                                                              |
                |   .---------------.   .--------------------------------------------------    |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   |               |   |                                                  |   |
                |   '---------------'   '--------------------------------------------------'   |
                |                                                                              |
                |   .----------------------------------------------------------------------.   |
                |   |                                                                      |   |
                |   |                                                                      |   |
                |   '----------------------------------------------------------------------'   |
                |                                                                              |
                '------------------------------------------------------------------------------'
                EOF,
                80,
                40,
                Border::create()
                    ->horizontal(<<<EOF
                    -
                     
                    -
                    EOF)
                    ->vertical(<<<EOF
                    |   |
                    EOF)
                    ->corners(
                        topLeft: <<<EOF
                        .----
                        |    
                        |   .
                        EOF,
                        topRight: <<<EOF
                        ----.
                            |
                            |
                        EOF,
                        bottomLeft: <<<EOF
                        |   '
                        |    
                        '----
                        EOF,
                        bottomRight: <<<EOF
                        '   |
                            |
                        ----'
                        EOF,
                    )
                    ->connectFacing(
                        left: <<<EOF
                        '   |
                            |
                        .   |
                        EOF,
                        right: <<<EOF
                        |   '
                        |    
                        |   .
                        EOF,
                        top: <<<EOF
                        '   '
                             
                        -----
                        EOF,
                        bottom: <<<EOF
                        -----
                             
                        .   .
                        EOF,
                        all: <<<EOF
                        '   '
                             
                        .   .
                        EOF,
                    )
                    ->connections([
                        Connection::horizontal()
                            ->begin(0, -8, Facing::RIGHT)
                            ->end(-5, -8, Facing::LEFT),
                        Connection::vertical()
                            ->begin(20, 0, Facing::BOTTOM)
                            ->end(20, -8, Facing::TOP),
                    ])
            ]
        ];
    }

    #[DataProvider('dataProvider')]
    public function test(string $expected, int $width, int $height, Border $border): void
    {
        self::assertSame($expected, (string)$border->getBuffer($width, $height));
    }
}
