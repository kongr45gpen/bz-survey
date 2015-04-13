<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Entity\Survey;
use AppBundle\Form\QuestionType;

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
            $form->add($question->getId(), new QuestionType(), array(
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
            'survey'      => $survey,
            'form' => $form->createView()
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
            'survey'      => $survey
        ));
    }
}
