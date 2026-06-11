<?php 

namespace App\Enums\Asset;

enum ComputerComponent: string 
{
    case MONITOR = 'Monitor';
    case MOTHERBOARD = 'Motherboard';
    case PROCESSOR = 'Processor';
    case RAM = 'RAM';
    case STORAGE = 'Storage';
    case GPU = 'GPU';
    case KEYBOARD_MOUSE = 'Keyboard / Mouse';

    public function label(): string
    {
        return __('app.asset.computer_components.' . strtolower($this->name));
    }
}
