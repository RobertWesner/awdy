<?php

namespace RobertWesner\AWDY\Template;

class BufferLogger
{
    private string $log = '';

    public function append(string $message): void
    {
        $this->log .= $message;
    }

    public function renderTo(Buffer $buffer): void
    {
        $lines = [];
        foreach (explode(PHP_EOL, rtrim($this->log, PHP_EOL)) as $line) {
            if (strlen($line) > $buffer->getWidth()) {
                foreach (str_split($line, $buffer->getWidth()) as $splitLine) {
                    $lines[] = $splitLine;
                }
            } else {
                $lines[] = $line;
            }
        }

        foreach (array_slice($lines, - $buffer->getHeight()) as $i => $line) {
            $buffer->draw(0, $i, $line);
        }
    }
}
