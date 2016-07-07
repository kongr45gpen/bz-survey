<?php

namespace AppBundle\Form;

use AppBundle\Entity\Question as QuestionEntity;
use Symfony\Component\Form\DataTransformerInterface;

class QuestionChoicesTransformer implements DataTransformerInterface
{
    /**
     * @var QuestionEntity
     */
    private $question;

    public function __construct(QuestionEntity $question)
    {
        $this->question = $question;
    }

    /**
     * {@inheritDoc}
     */
    public function transform($data)
    {
        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($values)
    {
        $data = array();

        foreach ($this->question->getAnswers() as $i => $answer) {
            $data[$i]['selected'] = ($values[$i] === null || $values[$i] === false) ? false : true;

            if ($answer->getOther()) {
                $other = $data[$i]['other'] = $values[$i . '-other'];

                if (trim($other) != '') {
                    // The user provided 'other' input but forgot to check the box
                    $data[$i]['selected'] = true;
                };
            }
        }

        return $data;
    }
}
