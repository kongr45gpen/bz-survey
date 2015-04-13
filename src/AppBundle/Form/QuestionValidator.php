<?php

namespace AppBundle\Form;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Question validator.
 */
class QuestionValidator extends ConstraintValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($data, Constraint $constraint)
    {
        if ($constraint->question->getRequired() && empty($data)) {
            $this->context->buildViolation($constraint->requiredMessage)
                ->setParameter('%question%', $constraint->question->getTitle())
                ->addViolation();
        }

        if (!$constraint->question->getMultiple() && count($data) > 1) {
            $this->context->buildViolation($constraint->extraChoiceMessage)
                ->addViolation();
        }

        foreach ($data as $i => $vote) {
            $other = trim($vote->getOther());

            if ($vote->getAnswer()->getOther() && $constraint->question->getRequired() && empty($other)) {
                $this->context->buildViolation($constraint->requiredOtherMessage)
                    ->addViolation();
            }
        }
    }
}
