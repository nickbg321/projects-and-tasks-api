<?php

declare(strict_types=1);

namespace App\Project\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class ClientOrCompany extends Constraint
{
    public const CLIENT_AND_COMPANY_NOT_SET_ERROR = '0e172748-0c76-4984-965a-b49e953a8a5f';

    protected const ERROR_NAMES = [
        self::CLIENT_AND_COMPANY_NOT_SET_ERROR => 'CLIENT_AND_COMPANY_NOT_SET_ERROR',
    ];

    public string $message = 'Client or company must be set.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
