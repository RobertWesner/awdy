<?php

namespace RobertWesner\AWDY\Template\Templates;

use RobertWesner\AWDY\Template\Area;
use RobertWesner\AWDY\Template\Border;
use RobertWesner\AWDY\Template\Buffer;
use RobertWesner\AWDY\Template\Connection;
use RobertWesner\AWDY\Template\Facing;
use RobertWesner\AWDY\Template\TemplateInterface;

class DefaultTemplate implements TemplateInterface
{
    private Area $logArea;
    private Area $progressArea;

    private float $progress = 0;
    private string $log = '';

    public function __construct()
    {
        $this->logArea = Area::create(5, 3, -5, -10, function (Buffer $buffer) {
            // TODO: scroll log area when overflowing :^)
            foreach (explode(PHP_EOL, $this->log) as $i => $line) {
                $buffer->draw(0, $i, $line);
            }
        });

        $this->progressArea = Area::create(5, -6, -5, -4, function (Buffer $buffer) {
            $buffer->draw(1, 0, '.');
            $buffer->draw(2, 0, str_repeat('-',  $buffer->getWidth() - 4));
            $buffer->draw($buffer->getWidth() - 2, 0, '.');

            $buffer->draw(1, 1, '|');
            $progressBarWidth = $buffer->getWidth() - 4;
            $progress = $progressBarWidth * $this->progress;
            $buffer->draw(2, 1, str_repeat('#', $progress));
            $buffer->draw($buffer->getWidth() - 2, 1, '|');

            $buffer->draw(1, 2, '\'');
            $buffer->draw(2, 2, str_repeat('-',  $buffer->getWidth() - 4));
            $buffer->draw($buffer->getWidth() - 2, 2, '\'');
        });
    }

    public function defineBorder(): Border
    {
        return Border::create()
            ->horizontal(<<<'EOF'
            -
             
            -
            EOF)
            ->vertical(<<<'EOF'
            |   |
            EOF)
            ->corners(
                topLeft: <<<'EOF'
                .----
                |    
                |   .
                EOF,
                topRight: <<<'EOF'
                ----. 
                    |
                .   |
                EOF,
                bottomLeft: <<<'EOF'
                |   '
                |    
                '----
                EOF,
                bottomRight: <<<'EOF'
                '   |
                    |
                ----'
                EOF,
            )
            ->connectFacing(
                left: <<<'EOF'
                '   |
                    |
                .   |
                EOF,
                right: <<<'EOF'
                |   '
                |    
                |   .
                EOF,
                top: <<<'EOF'
                .   .
                     
                -----
                EOF,
                bottom: <<<'EOF'
                -----
                     
                .   .
                EOF,
                all: <<<'EOF'
                '   '
                     
                .   .
                EOF,
            )
            ->connections([
                Connection::horizontal()
                    ->begin(0, -9, Facing::RIGHT)
                    ->end(-5, -9, Facing::LEFT),
            ]);
    }

    public function getAreas(): array
    {
        return [
            $this->logArea,
            $this->progressArea,
        ];
    }

    public function handleEcho(string $echo): void
    {
        $this->log .= $echo . PHP_EOL;
        $this->logArea->dirty();
    }

    public function handleProgress(float $progress): void
    {
        $this->progress = $progress;
        $this->progressArea->dirty();
    }
}
