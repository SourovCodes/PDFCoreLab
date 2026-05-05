<?php

namespace App\Enums;

enum GhostscriptPreset: string
{
    case Screen = 'screen';
    case Ebook = 'ebook';
    case Printer = 'printer';
    case Prepress = 'prepress';
    case Default = 'default';

    public function cliValue(): string
    {
        return '/'.$this->value;
    }
}
