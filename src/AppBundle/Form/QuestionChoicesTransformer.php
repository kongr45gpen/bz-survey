<?php

namespace AppBundle\Form;

use AppBundle\Entity\Question;
use AppBundle\Entity\Vote;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class QuestionChoicesTransformer implements DataTransformerInterface
{
    /**
     * @var Question
     */
    private $question;

    public function __construct(Question $question)
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
            $data[$i]['selected'] = $values[$i];

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
