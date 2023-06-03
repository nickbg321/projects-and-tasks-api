<?php

declare(strict_types=1);

namespace App\Project\Validator;

use App\Project\ApiResource\ProjectResource;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ClientOrCompanyValidator extends ConstraintValidator
{
    /**
     * @param ProjectResource $value
     * @param ClientOrCompany $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof ProjectResource) {
            throw new UnexpectedValueException($value, ProjectResource::class);
        }

        $isClientSet = $value->client !== null && trim($value->client) !== '';
        $isCompanySet = $value->company !== null && trim($value->company) !== '';

        if (!$isClientSet && !$isCompanySet) {
            $this->context->buildViolation($constraint->message)
                ->setCode(ClientOrCompany::CLIENT_AND_COMPANY_NOT_SET_ERROR)
                ->addViolation();
        }
    }
}
