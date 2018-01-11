<?php

namespace FeedsReaderBundle\Handler;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use FeedsReaderBundle\Entity\Feed;
use FeedsReaderBundle\Repository\FeedRepository;
use Symfony\Component\Config\Definition\Exception\Exception;

class FeedHandler
{
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getList($catId = null, $page = 1)
    {
        $feedRepo = $this->em->getRepository(Feed::class);
        $feedList = $feedRepo->createQueryBuilder('f');
        if (!is_null($catId)) {
            $feedList->where('f.category = :catId')
                ->setParameter('catId', $catId);
        }
        $feedList->orderBy('f.id','DESC');
        $dql = $feedList->getQuery();
        $paginator = new Paginator($dql);
        $paginator->getQuery()
            ->setFirstResult(FeedRepository::FEEDS_PER_PAGE * ($page - 1))// Offset
            ->setMaxResults(FeedRepository::FEEDS_PER_PAGE); // Limit

        return $paginator;
    }

    public function deleteByCatId($catId)
    {
        $q = $this->em->createQuery('delete from FeedsReaderBundle\Entity\Feed f where f.category = :catId')
            ->setParameter('catId', $catId);
        $q->execute();
    }

    public function deleteById($id)
    {
        $q = $this->em->createQuery('delete from FeedsReaderBundle\Entity\Feed f where f.id = :id')
            ->setParameter('id', $id);
        $q->execute();
    }

    public function getOneById($id)
    {
        $catRepo = $this->em->getRepository(Feed::class);

        return $catRepo->find($id);
    }

    public function save($request, $id)
    {
        if(!is_null($id)) {
            $feed = $this->getOneById($id);
            $created_at = $feed->getCreatedAt();
        } else {
            $feed = new Feed();
            $created_at = time();
        }

        $this->em->getConnection()->beginTransaction(); // suspend auto-commit
        try {
            $formData = $request->request->get('form');
            $feed->setTitle($formData['title']);
            $feed->setDescription($formData['description']);
            $feed->setLink($formData['link']);
            $feed->setCategory($formData['category']);
            $publishedDate = implode(' ', array_values($formData['publishDate']));
            $feed->setPublishDate(strtotime($publishedDate));
            $feed->setCreatedAt($created_at);
            $feed->setUpdatedAt(time());
            $this->em->persist($feed);
            $this->em->flush();
            $this->em->clear();
            $this->em->getConnection()->commit();
        } catch (Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }
    }
}