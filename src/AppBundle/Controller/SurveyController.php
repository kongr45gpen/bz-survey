<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Survey;
use AppBundle\Entity\Vote;
use AppBundle\Form\QuestionType;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Survey controller.
 *
 * @Route("/survey")
 */
class SurveyController extends Controller
{
    /**
     * Lists all Survey entities.
     *
     * @Route("/", name="survey")
     *
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AppBundle:Survey')->findAll();

        return $this->render('survey/index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Finds and displays a Survey entity.
     *
     * @Route("/{id}", name="survey_show")
     */
    public function showAction(Survey $survey, Request $request)
    {
        $form = $this->createFormBuilder();

        foreach ($survey->getQuestions() as $question) {
            $form->add($question->getId(), QuestionType::class, array(
                'question' => $question,
            ));
        }

        $form = $form->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            if (!$survey->getEnabled()) {
                $this->addFlash(
                    'error',
                    'Sorry, but this survey isn\'t accepting responses any more.'
                );
            } elseif ($this->getUser()->hasSurvey($survey)) {
                $this->addFlash(
                    'error',
                    'Sorry, but you have already participated in this survey'
                );
            } else {
                $em = $this->getDoctrine()->getManager();

                foreach ($form as $question) {
                    foreach ($question->getData() as $vote) {
                        $vote->setUser($this->getUser());

                        $em->persist($vote);
                    }
                }

                $this->getUser()->addSurvey($survey);
                $em->flush();

                $this->addFlash(
                    'success',
                    'Your submission was successful'
                );

                return $this->redirect($this->generateUrl('survey'));
            }
        }

        return $this->render('survey/show.html.twig', array(
            'survey' => $survey,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays the results of a Survey entity.
     *
     * @Route("/{id}/results", name="survey_results")
     */
    public function resultsAction(Survey $survey)
    {
        if (!$survey->getShowResults()) {
            throw new AccessDeniedException('You cannot access this page');
        }

        return $this->render('survey/results.html.twig', array(
            'survey' => $survey,
        ));
    }

    /**
     * Allows resetting the submission of a survey
     *
     * @Route("/{id}/cancel", name="survey_cancel")
     */
    public function cancelAction(Survey $survey, Request $request)
    {
        if (!$survey->getAllowResetting()) {
            $error = 'This survey does not allow resetting its submissions.';
        }

        if (!$this->getUser()->hasSurvey($survey)) {
            $error = 'You haven\'t participated in this survey yet.';
        }

        if (!$survey->getEnabled()) {
            $error = 'Sorry, but this survey isn\'t accepting modifications in responses any more.';
        }

        if (isset($error)) {
            $request->getSession()->getFlashbag()->add('error', $error);
            return $this->redirectToRoute('homepage');
        }

        $form = $this->createFormBuilder()
            ->add('Yes', SubmitType::class)
            ->add('No', SubmitType::class)
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('Yes')->isClicked()) {
                $em = $this->getDoctrine()->getManager();

                $this->getUser()->removeSurvey($survey);
                $em->persist($survey);

                /** @var QueryBuilder $query */
                $query = $em->createQueryBuilder();
                $votes = $query->select('v')
                    ->from('AppBundle:Vote', 'v')
                    ->innerJoin('v.answer', 'a')
                    ->innerJoin('a.question', 'q', Join::WITH, 'q.survey = :survey')
                    ->where('v.user = :user')
                    ->getQuery()
                    ->setParameter('user', $this->getUser())
                    ->setParameter('survey', $survey)
                    ->getResult()
                ;
                $em->createQueryBuilder()
                    ->delete('AppBundle:Vote','v')
                    ->where('v in (?1)')
                    ->getQuery()
                    ->setParameter(1, $votes)
                    ->execute();

                $em->flush();
                $request->getSession()->getFlashbag()->add('success', 'Your submission has been cancelled.');
            }

            return $this->redirectToRoute('homepage');
        }

        return $this->render('survey/cancel.html.twig', array(
            'form' => $form->createView(),
            'survey' => $survey,
        ));
    }

    /**
     * Allows an admin to disable a survey
     *
     * @Route("/{id}/disable", name="survey_disable")
     */
    public function disableAction(Survey $survey, Request $request)
    {
        if ($survey->getOwner()->getId() !== $this->getUser()->getId()) {
            $error = 'You are not allowed to do this';
        } elseif (!$survey->getEnabled()) {
            $error = 'This survey is already disabled';
        }

        if (isset($error)) {
            $request->getSession()->getFlashbag()->add('error', $error);
            return $this->redirectToRoute('homepage');
        }

        $form = $this->createFormBuilder()
            ->add('Yes', SubmitType::class)
            ->add('No', SubmitType::class)
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isValid()) {
            $survey->setEnabled(false);

            $em = $this->getDoctrine()->getManager();
            $em->persist($survey);
            $em->flush();

            $request->getSession()->getFlashbag()->add('success', 'The survey has been disabled.');

            return $this->redirectToRoute('homepage');
        }

        return $this->render('survey/enable.html.twig', array(
            'action' => 'disable',
            'form' => $form->createView(),
            'survey' => $survey,
        ));
    }

    /**
     * Allows an admin to enable a survey
     *
     * @Route("/{id}/enable", name="survey_enable")
     */
    public function enableAction(Survey $survey, Request $request)
    {
        if ($survey->getOwner()->getId() !== $this->getUser()->getId()) {
            $error = 'You are not allowed to do this';
        } elseif ($survey->getEnabled()) {
            $error = 'This survey is already enabled';
        }

        if (isset($error)) {
            $request->getSession()->getFlashbag()->add('error', $error);
            return $this->redirectToRoute('homepage');
        }

        $form = $this->createFormBuilder()
            ->add('Yes', SubmitType::class)
            ->add('No', SubmitType::class)
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isValid()) {
            $survey->setEnabled(true);

            $em = $this->getDoctrine()->getManager();
            $em->persist($survey);
            $em->flush();

            $request->getSession()->getFlashbag()->add('success', 'The survey has been enabled.');

            return $this->redirectToRoute('homepage');
        }

        return $this->render('survey/enable.html.twig', array(
            'action' => 'enable',
            'form' => $form->createView(),
            'survey' => $survey,
        ));
    }
}
