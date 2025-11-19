<?php

namespace App\Enums;

enum VendorStage: string
{
    case NEW = 'new';
    case WITH_VENDOR = 'with_vendor';
    case CHECKER_REVIEW = 'checker_review';
    case PROCUREMENT_REVIEW = 'procurement_review';
    case LEGAL_REVIEW = 'legal_review';
    case FINANCE_REVIEW = 'finance_review';
    case DIRECTORS_REVIEW = 'directors_review';
    case APPROVED = 'approved';
    
    public function label(): string
    {
        return match($this) {
            self::NEW => 'New',
            self::WITH_VENDOR => 'With Vendor',
            self::CHECKER_REVIEW => 'Checker Review',
            self::PROCUREMENT_REVIEW => 'Procurement Review',
            self::LEGAL_REVIEW => 'Legal Review',
            self::FINANCE_REVIEW => 'Finance Review',
            self::DIRECTORS_REVIEW => 'Directors Review',
            self::APPROVED => 'Approved',
        };
    }
    
    public function nextStage(): ?self
    {
        return match($this) {
            self::NEW => self::WITH_VENDOR,
            self::WITH_VENDOR => self::CHECKER_REVIEW,
            self::CHECKER_REVIEW => self::PROCUREMENT_REVIEW,
            self::PROCUREMENT_REVIEW => self::LEGAL_REVIEW,
            self::LEGAL_REVIEW => self::FINANCE_REVIEW,
            self::FINANCE_REVIEW => self::DIRECTORS_REVIEW,
            self::DIRECTORS_REVIEW => self::APPROVED,
            self::APPROVED => null,
        };
    }
}