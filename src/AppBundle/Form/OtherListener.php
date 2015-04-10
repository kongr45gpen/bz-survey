<?php

namespace AppBundle\Form;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class OtherListener implements EventSubscriberInterface
{
    private $others;

    /**
     * Constructor.
     *
     * @param ChoiceListInterface $choiceList
     */
    // public function __construct(ChoiceListInterface $choiceList)
    // {
    //     $this->choiceList = $choiceList;
    // }

    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();

        if (isset($data['choices'])) {
            $choices = $data['choices'];
            unset($data['choices']);
        } else {
            $choices = array();
        }

        if (!is_array($choices)) {
            $choices = array($choices);
        }

        $choices = array_flip($choices);

        if (!empty(array_intersect_key($choices, $data))) {
            throw new TransformationFailedException('Unkown fields present in form');
        }

        $event->setData($choices + $data);
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SUBMIT => array(
                array('preSubmit', 20)
            )
        );
    }
}
