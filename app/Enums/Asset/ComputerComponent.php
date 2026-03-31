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
}