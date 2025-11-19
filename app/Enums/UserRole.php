<?php

namespace App\Enums;

enum UserRole: string
{
    case INITIATOR = 'initiator';
    case VENDOR = 'vendor';
    case CHECKER = 'checker';
    case PROCUREMENT = 'procurement';
    case LEGAL = 'legal';
    case FINANCE = 'finance';
    case DIRECTOR = 'director';
    
    public function label(): string
    {
        return match($this) {
            self::INITIATOR => 'Initiator',
            self::VENDOR => 'Vendor',
            self::CHECKER => 'Checker',
            self::PROCUREMENT => 'Procurement',
            self::LEGAL => 'Legal',
            self::FINANCE => 'Finance',
            self::DIRECTOR => 'Director',
        };
    }
}