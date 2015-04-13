<?php

namespace AppBundle\Form;

use AppBundle\Entity\Question;
use AppBundle\Entity\Vote;
use Symfony\Component\Form\DataTransformerInterface;

class VoteTransformer implements DataTransformerInterface
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
     *
     * @return Vote[] An array of votes
     */
    public function reverseTransform($values)
    {
        $votes   = array();
        $answers = $this->question->getAnswers();

        foreach ($answers as $i => $answer) {
            if ($values[$i]['selected'] == false) {
                continue;
            }

            $vote = new Vote();
            $vote->setAnswer($answer);

            if ($answer->getOther()) {
                $vote->setOther($values[$i]['other']);
            }

            $votes[] = $vote;
        }

        return $votes;
    }
}
