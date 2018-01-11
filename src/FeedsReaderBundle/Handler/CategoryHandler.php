<?php

namespace FeedsReaderBundle\Handler;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use FeedsReaderBundle\Entity\Category;
use FeedsReaderBundle\Entity\Feed;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\UrlValidator;

class CategoryHandler
{
    protected $em;

    private $feedXmls = [];

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Defines write ability for property 'feedXmls'.
     *
     * @param \SimpleXMLElement $value
     */
    public function setFeedXmls(\SimpleXMLElement $value)
    {
        $this->feedXmls[] = $value;
    }

    /**
     * Defines read ability for property 'feedXmls'.
     *
     * @return array
     */
    public function getFeedXmls()
    {
        return $this->feedXmls;
    }

    /**
     * Defines read ability for property 'feedXmls'.
     *
     * @param string $feedCategories
     * @return array $result
     */
    public function validate($feedCategories)
    {
        $feedCategories = explode(',', $feedCategories);
        $validator = new UrlValidator();
        $result = ['code' => 200, 'message' => ''];
        foreach ($feedCategories as $feedCategory) {
            $feedCategory = trim($feedCategory);
            libxml_use_internal_errors(false);
            $violations = $validator->validate(
                $feedCategory,
                new Url()
            );
            if (0 !== count($violations)) {
                $result = ['code' => 400, 'message' => 'One of categories is invalid url. Here it is: '.$feedCategory];
                break;
            }
            try {
                $xml = simplexml_load_file($feedCategory);
                $this->setFeedXmls($xml);
            } catch (ContextErrorException $e) {
                $result = [
                    'code' => 204,
                    'message' => 'One of categories cannot be loaded. Here it is: ' . $feedCategory,
                ];
                break;
            }
        }

        return $result;
    }

    public function getList()
    {
        $catRepo = $this->em->getRepository(Category::class);
        $categories = $catRepo->createQueryBuilder('c')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);

        return $categories;
    }

    public function save($feedCategories)
    {
        $validated = $this->validate($feedCategories);
        if (200 != $validated['code']) {
            return ['status' => false, 'message' => $validated['message']];
        }

        $this->em->getConnection()->beginTransaction(); // suspend auto-commit
        try {
            // Create new category
            $feedXmls = $this->getFeedXmls();
            foreach ($feedXmls as $feedXml) {
                $category = new Category();
                $category->setTitle((string)$feedXml->channel->title);
                $category->setCreatedAt(time());
                $category->setUpdatedAt(time());
                $this->em->persist($category);
                $this->em->flush();

                // Collect feed items
                foreach ($feedXml->channel->item as $item) {
                    $feed = new Feed();
                    $feed->setTitle($item->title);
                    $feed->setDescription($item->description);
                    $feed->setLink($item->link);
                    $feed->setCategory($category->getId());
                    $feed->setPublishDate(strtotime($item->pubDate));
                    $feed->setCreatedAt(time());
                    $feed->setUpdatedAt(time());
                    $this->em->persist($feed);
                }
            }
            $this->em->flush();
            $this->em->getConnection()->commit();
            $this->em->clear();

            return ['status' => true, 'message' => ''];
        } catch (Exception $e) {
            $this->em->getConnection()->rollBack();
            $this->em->clear();
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function getOneById($id)
    {
        $catRepo = $this->em->getRepository(Category::class);

        return $catRepo->find($id);
    }

    public function delete($id)
    {
        $q = $this->em->createQuery('delete from FeedsReaderBundle\Entity\Category c where c.id = :id')
            ->setParameter('id', $id);
        $q->execute();
    }
}