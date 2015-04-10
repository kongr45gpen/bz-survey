<?php

namespace AppBundle\Form;

use AppBundle\Entity\Question as QuestionEntity;
use Symfony\Component\Validator\Constraint;

/**
 * Question form constraint
 */
class Question extends Constraint
{
    /**
     * @var QuestionEntity
     */
    public $question;

    /**
     * Create new question form constraint
     * @param QuestionEntity $question The question
     */
    public function __construct(QuestionEntity $question)
    {
        $this->question = $question;
    }

    /**
     * @var string
     */
    public $message = 'This question is invalid';

    /**
     * @var string
     */
    public $requiredMessage = 'The "%question%" question requires an answer';

    /**
     * @var string
     */
    public $requiredOtherMessage = "The answer you chose requires you to type something in the 'Other' field";

    /**
     * @todo Not implemented yet.
     *
     * @var string
     */
    public $extraOtherMessage = "You can't provide 'other' text for an option you have not selected";
}
