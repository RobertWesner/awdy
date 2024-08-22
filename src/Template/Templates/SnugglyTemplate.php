<?php

namespace RobertWesner\AWDY\Template\Templates;

use RobertWesner\AWDY\AnsiEscape;
use RobertWesner\AWDY\Template\Border;
use RobertWesner\AWDY\Template\Buffer;

class SnugglyTemplate extends AbstractCanvasTemplate
{
    public const SNUGGLY_SPRITES = [<<<'EOF'
    #_##############
    | |#######/\/\##
    \ \______/ . .\#
    #|          ^ |#
    #|           /##
    #/ /______/ /\##
    /_/\_\###/_/\_\#
    EOF, <<<'EOF'
    #_##############
    | |#######/\/\##
    \ \______/ . .\#
    #|          ^ |#
    #|           /##
    #/ /______/ /\##
    /_/\_\###/_/\_\#
    EOF, <<<'EOF'
    #_##############
    | |#######/\/\##
    \ \______/ . .\#
    #|          ^ |#
    #|           /##
    #| |_____| | |##
    #|_|_|###|_|_|##
    EOF, <<<'EOF'
    #_##############
    | |#######/\/\##
    \ \______/ . .\#
    #|          ^ |#
    #|           /##
    #/\ \______\ \##
    /_/\_\###/_/\_\#
    EOF, <<<'EOF'
    #_##############
    | |#######/\/\##
    \ \______/ . .\#
    #|          ^ |#
    #|           /##
    #/\ \______\ \##
    /_/\_\###/_/\_\#
    EOF, <<<'EOF'
    #_##############
    | |#######/\/\##
    \ \______/ . .\#
    #|          ^ |#
    #|           /##
    #| |_____| | |##
    #|_|_|###|_|_|##
    EOF];

    public const STREET_SPRITE = <<<'EOF'
    -
     
     
    _
    EOF;

    private $snugglyCurrentSprite = 0;

    public function __construct(
        private string $snugglyColor = '',
    ) {
        parent::__construct();
    }

    protected function getCanvasDimensions(): array
    {
        return [6, 7, -7, -5];
    }

    public function getBorder(): Border
    {
        return Border::create()
            ->horizontal(<<<'EOF'
             
             
             
            -
             
             
            -
            EOF)
            ->vertical(<<<'EOF'
            |    |
            EOF)
            ->corners(
                topLeft: <<<'EOF'
                         ___       
                        /   \      
                       /     \     
                .-----'       '----
                |                  
                |                  
                |    .-------------
                EOF,
                topRight: <<<'EOF'
                        ___        
                       /   \       
                      /     \      
                -----'       '----.
                                  |
                                  |
                -------------.    |
                EOF,
                bottomLeft: <<<'EOF'
                |    '-------------
                |                  
                |                  
                '------------------
                EOF,
                bottomRight: <<<'EOF'
                --------------'    |
                                   |
                                   |
                -------------------'
                EOF,
            );
    }

    public function handleEcho(string $echo): void
    {
    }

    public function renderCanvas(Buffer $buffer): void
    {
        $this->snugglyCurrentSprite = ($this->snugglyCurrentSprite + 1) % count(self::SNUGGLY_SPRITES);
        $snugglySprite = self::SNUGGLY_SPRITES[$this->snugglyCurrentSprite];

        $snugglyX = $this->progress * ($buffer->getWidth() - strpos($snugglySprite, PHP_EOL));

        for ($i = 0; $i < $buffer->getWidth(); $i++) {
            $buffer->draw($i, -6, self::STREET_SPRITE, AnsiEscape::bg(16));
        }

        $buffer->draw(
            $snugglyX,
            -10,
            $snugglySprite,
            ansiEscape: $this->snugglyColor,
            transparency: '#',
        );
    }
}
