<?php

namespace FeedsReaderBundle\Controller;

use FeedsReaderBundle\Entity\Feed;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

class FeedController extends Controller
{
    private function getOneById($id)
    {
        $feedHandler = $this->get('feeds_reader.feed.handler');
        $feed = $feedHandler->getOneById($id);

        return $feed;
    }

    private function makeForm(Feed $feed)
    {
        $categoryHandler = $this->get('feeds_reader.category.handler');
        $categories = $categoryHandler->getList();
        $categories = array_column($categories, 'id', 'title');

        return $this->createFormBuilder($feed)
            ->setAction($this->generateUrl('save-feed', ['id' => $feed->getId()]))
            ->setMethod('POST')
            ->add('title', TextType::class)
            ->add('description', TextareaType::class)
            ->add('link', TextType::class)
            ->add(
                'category',
                ChoiceType::class,
                [
                    'choices' => $categories,
                ]
            )
            ->add(
                'publishDate',
                DateTimeType::class,
                [
                    'input' => 'timestamp',
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
                ]
            )
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();
    }

    /**
     * Delete feed
     *
     * @Route("/delete-feed/{id}", methods={"POST"}, name="delete-feed")
     */
    public function deleteFeedAction(Request $request, $id)
    {
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('delete-feed', $submittedToken)) {
            $feed = $this->getOneById($id);
            $catId = $feed->getCategory();
            if (!$feed) {
                $this->addFlash("error", 'The feed you requested is not existed.');
            } else {
                $feedHandler = $this->get('feeds_reader.feed.handler');
                try {
                    $feedHandler->deleteById($id);
                    $this->addFlash("success", 'Delete feed successful');
                } catch (Exception $e) {
                    $this->addFlash("error", $e->getMessage());
                }
            }
        }

        return $this->redirect('/feeds-reader/view/1/'.$catId);
    }

    /**
     * Edit feed
     *
     * @Route("/edit-feed/{id}", methods={"GET"}, name="edit-feed")
     */
    public function editFeedAction($id)
    {
        $feed = $this->getOneById($id);
        if (empty($feed)) {
            $this->addFlash("error", 'The feed you requested is not existed.');

            return $this->redirect('/feeds-reader/view');
        }

        $form = $this->makeForm($feed);
        $categoryHandler = $this->get('feeds_reader.category.handler');
        $categories = $categoryHandler->getList();

        return $this->render(
            'FeedsReaderBundle:feed:form.html.twig',
            [
                'form' => $form->createView(),
                'categories' => $categories,
                'isAdd' => false,
                'selectedId' => $feed->getCategory(),
            ]
        );
    }

    /**
     * Add new feed
     *
     * @Route("/add-feed/", methods={"GET"}, name="add-feed")
     */
    public function addFeedAction()
    {
        $form = $this->makeForm(new Feed());
        $categoryHandler = $this->get('feeds_reader.category.handler');
        $categories = $categoryHandler->getList();

        return $this->render(
            'FeedsReaderBundle:feed:form.html.twig',
            [
                'form' => $form->createView(),
                'categories' => $categories,
                'isAdd' => true,
                'selectedId' => null
            ]
        );
    }

    /**
     * Save feed
     *
     * @Route("/save-feed/{id}", methods={"POST"}, name="save-feed")
     */
    public function saveFeedAction(Request $request, $id = null)
    {
        $feedHandler = $this->get('feeds_reader.feed.handler');
        try {
            $feedHandler->save($request, $id);
        } catch (Exception $e) {
            $this->addFlash("error", $e->getMessage());
        }

        return $this->redirect('/feeds-reader/view');
    }
}
