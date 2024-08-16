<?php

namespace RobertWesner\AWDY\Template;

interface TemplateInterface
{
    public function defineBorder(): Border;

    /**
     * @return Area[]
     */
    public function getAreas(): array;

    public function handleEcho(string $echo): void;

    /**
     * @param float $progress 0 - 1
     */
    public function handleProgress(float $progress): void;
}