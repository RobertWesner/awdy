<?php

namespace RobertWesner\AWDY\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use RobertWesner\AWDY\AWDY;
use RobertWesner\AWDY\Template\Area;
use RobertWesner\AWDY\Template\Border;
use RobertWesner\AWDY\Template\Buffer;
use RobertWesner\AWDY\Template\BufferLogger;
use RobertWesner\AWDY\Template\Connection;
use RobertWesner\AWDY\Template\Templates\SimpleTemplate;

#[CoversClass(AWDY::class)]
#[UsesClass(Area::class)]
#[UsesClass(Border::class)]
#[UsesClass(Buffer::class)]
#[UsesClass(BufferLogger::class)]
#[UsesClass(Connection::class)]
final class AWDYTest extends BaseTest
{
    public function test(): void
    {
        ob_start();
        AWDY::setUp(new SimpleTemplate(), 56, 18);
        self::assertSame(<<<EOF
        .------------------------------------------------------.
        | [>                                                 ] |
        +------------------------------------------------------+
        |                                                      |
        |                                                      |
        |                                                      |
        |                                                      |
        |                                                      |
        |                                                      |
        |                                                      |
        |                                                      |
        |                                                      |
        |                                                      |
        |                                                      |
        |                                                      |
        |                                                      |
        |                                                      |
        '------------------------------------------------------'
        EOF, $this->renderAwdyOutput(ob_get_clean()));

        // this does not render the entire thing, only the changed area
        ob_start();
        AWDY::printf('test');
        self::assertSame(<<<EOF
        
          test                                                
                                                              
                                                              
                                                              
                                                              
                                                              
                                                              
                                                              
                                                              
                                                              
                                                              
                                                              
                                                              
                                                              
        EOF, $this->renderAwdyOutput(ob_get_clean()));

        ob_start();
        AWDY::progress(1 / 100, 1, 100);
        self::assertSame(<<<EOF
        
            1/100 [>                                         ]
        EOF, $this->renderAwdyOutput(ob_get_clean()));

        ob_start();
        AWDY::progress(33 / 100, 33, 100);
        self::assertSame(<<<EOF
        
           33/100 [==============>                           ]
        EOF, $this->renderAwdyOutput(ob_get_clean()));

        ob_start();
        AWDY::progress(75 / 100, 75, 100);
        self::assertSame(<<<EOF
        
           75/100 [================================>         ]
        EOF, $this->renderAwdyOutput(ob_get_clean()));

        ob_start();
        AWDY::progress(99 / 100, 99, 100);
        self::assertSame(<<<EOF
        
           99/100 [==========================================]
        EOF, $this->renderAwdyOutput(ob_get_clean()));

        ob_start();
        AWDY::progress(1, 100, 100);
        self::assertSame(<<<EOF
        
          100/100 [==========================================]
        EOF, $this->renderAwdyOutput(ob_get_clean()));
    }
}
