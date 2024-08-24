<?php

declare(strict_types=1);

namespace RobertWesner\AWDY\Tests\Template;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesTrait;
use RobertWesner\AWDY\Template\AbsoluteCoordinateTrait;
use RobertWesner\AWDY\Template\Buffer;
use RobertWesner\AWDY\Template\BufferLogger;
use RobertWesner\AWDY\Tests\BaseTest;

#[CoversClass(BufferLogger::class)]
#[UsesTrait(AbsoluteCoordinateTrait::class)]
#[UsesClass(Buffer::class)]
final class BufferLoggerTest extends BaseTest
{
    private function getRendered(BufferLogger $logger): string
    {
        $buffer = new Buffer(12, 4);
        $logger->renderTo($buffer);

        return (string)$buffer;
    }

    public function test(): void
    {
        $logger = new BufferLogger();
        self::assertSame(<<<EOF
                    
                    
                    
                    
        EOF, $this->getRendered($logger));

        $logger->append('Test ');
        self::assertSame(<<<EOF
        Test        
                    
                    
                    
        EOF, $this->getRendered($logger));

        $logger->append('1');
        $logger->append('33');
        $logger->append('.7' . PHP_EOL);
        self::assertSame(<<<EOF
        Test 133.7  
                    
                    
                    
        EOF, $this->getRendered($logger));

        $logger->append('This is a very long text and should break into a new line.');
        self::assertSame(<<<EOF
        ry long text
         and should 
        break into a
         new line.  
        EOF, $this->getRendered($logger));
    }
}
