<?php

namespace FeedsReaderBundle\Controller;

use FeedsReaderBundle\Repository\FeedRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends Controller
{
    private function makeAddForm()
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('save-category'))
            ->setMethod('POST')
            ->add('feedCategory', TextareaType::class)
            ->add('add', SubmitType::class, ['label' => 'Submit'])
            ->getForm();
    }

    private function getList()
    {
        $categoryHandler = $this->get('feeds_reader.category.handler');
        $categories = $categoryHandler->getList();

        return $categories;
    }

    private function getOneById($id)
    {
        $categoryHandler = $this->get('feeds_reader.category.handler');
        $categories = $categoryHandler->getOneById($id);

        return $categories;
    }

    /**
     * @Route("/view/{page}/{id}", methods={"GET"}, name="view-category", defaults={"page" = 1})
     */
    public function indexAction($page = 1, $id = null)
    {
        $feedHandler = $this->get('feeds_reader.feed.handler');
        $feedList = $feedHandler->getList($id, $page);
        $categories = $this->getList();
        $categories = array_column($categories, 'title', 'id');

        $maxPages = ceil($feedList->count() / FeedRepository::FEEDS_PER_PAGE);

        return $this->render(
            'FeedsReaderBundle:Default:index.html.twig',
            [
                'categories' => $categories,
                'feedList' => $feedList,
                'selectedId' => $id,
                'maxPages' => $maxPages,
                'thisPage' => $page,
            ]
        );
    }

    /**
     * Add new category
     *
     * @Route("/add-category/", methods={"GET"}, name="add-category")
     */
    public function addCategoryAction()
    {
        $form = $this->makeAddForm();

        return $this->render(
            'FeedsReaderBundle:category:add.html.twig',
            [
                'form' => $form->createView(),
                'categories' => $this->getList(),
            ]
        );
    }

    /**
     * Save category
     *
     * @Route("/save-category", methods={"POST"}, name="save-category")
     */
    public function saveCategoryAction(Request $request)
    {
        $categoryHandler = $this->get('feeds_reader.category.handler');

        $feedCategories = $request->request->get('form')['feedCategory'];
        $result = $categoryHandler->save($feedCategories);
        if(!$result['status']) {
            $this->addFlash("error", $result['message']);
        }

        return $this->redirect('/feeds-reader/view');
    }

    /**
     * Delete category
     *
     * @Route("/delete-category/{id}", methods={"POST"}, name="delete-category")
     */
    public function deleteCategoryAction(Request $request, $id)
    {
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('delete-category', $submittedToken)) {
            $category = $this->getOneById($id);
            if(!$category) {
                $this->addFlash("error", 'The category you requested is not existed.');
            } else {
                $categoryHandler = $this->get('feeds_reader.category.handler');
                $feedHandler = $this->get('feeds_reader.feed.handler');
                try {
                    $categoryHandler->delete($id);
                    $feedHandler->deleteByCatId($id);
                    $this->addFlash("success", 'Delete category successful');
                } catch (Exception $e) {
                    $this->addFlash("error", $e->getMessage());
                }
            }
        }

        return $this->redirect('/feeds-reader/view');
    }
}
