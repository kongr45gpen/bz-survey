<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\ChoiceToBooleanArrayTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\ChoicesToBooleanArrayTransformer;
use Symfony\Component\Form\Extension\Core\EventListener\FixRadioInputListener;
use Symfony\Component\Form\Extension\Core\EventListener\FixCheckboxInputListener;
use Symfony\Component\Form\Extension\Core\EventListener\MergeCollectionListener;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class QuestionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $type = $options['question']->getMultiple() ? 'checkbox' : 'radio';

        $choices = array();

        foreach ($options['question']->getAnswers() as $i => $answer) {
            $builder->add($i, $type, array(
                'attr' => array('data-other' => $answer->getOther() ? $type : false),
                'block_name' => 'entry',
                'label' => $answer->getTitle(),
                'required' => false,
                'value' => $i
            ));

            if ($answer->getOther()) {
                $name = $i . '-other';

                $builder->add($name, 'text', array(
                    'attr' => array(
                        'data-other' => 'text',
                        'placeholder' => $answer->getTitle(),
                    ),
                    'block_name' => 'entry',
                    'label' => 'Other',
                    'required' => false
                ));
            }

            $choices[$i] = $i;
        }

        $builder->addEventSubscriber(new OtherListener(), 10);

        $builder->addViewTransformer(new QuestionChoicesTransformer($options['question']));
        $builder->addModelTransformer(new VoteTransformer($options['question']));
    }

    /**
    * {@inheritdoc}
    */
   public function buildView(FormView $view, FormInterface $form, array $options)
   {
       // The decision, whether a choice is selected, is potentially done
       // thousand of times during the rendering of a template. Provide a
       // closure here that is optimized for the value of the form, to
       // avoid making the type check inside the closure.
       if ($options['question']->getMultiple()) {
           $view->vars['is_selected'] = function ($choice, array $values) {
               return in_array($choice, $values, true);
           };
       } else {
           $view->vars['is_selected'] = function ($choice, $value) {
               return $choice === $value;
           };
       }

       $view->vars['url'] = $options['url'];
   }

   /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        // Radio buttons should have the same name as the parent
        $childName = $view->vars['full_name'] . '[choices]';

        // Checkboxes should append "[]" to allow multiple selection
        if ($options['question']->getMultiple()) {
            $childName .= '[]';
        }

        foreach ($view as $childView) {
            $other = $childView->vars['attr']['data-other'];

            if ($other !== 'text') {
                // Only radios and checkboxes should be affected
                $childView->vars['full_name'] = $childName;
            }

            if ($other !== false) {
                $childView->vars['block_prefixes'][] = '_other';
                $childView->vars['block_prefixes'][] = '_other_' . $other;

                if ($other === 'radio') {
                    // Radios are checkboxes
                    $childView->vars['block_prefixes'][] = '_other_checkbox';
                }
            }

            unset($childView->vars['attr']['data-other']);
        }
    }


    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'compound' => true,
            'constraints' => function(Options $options) {
                return new Question($options['question']);
            },
            'label' => function(Options $options) {
                return $options['question']->getTitle();
            },
            'required' => function(Options $options) {
                return $options['question']->getRequired();
            },
            'url' => function(Options $options) {
                return $options['question']->getUrl();
            }
        ));

        $resolver->setRequired('question');

        $resolver->setAllowedTypes(array(
            'question' => array('AppBundle\Entity\Question'),
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'app_question';
    }
}
