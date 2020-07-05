<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @return int|mixed|string
     */
    public function findPublicArticles()
    {
        return $this->createQueryBuilder('a')
            ->where('a.isPublic = :isPublic')
            ->setParameter('isPublic', true)
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPublicArticlesQuery()
    {
        return $this->createQueryBuilder('a')
            ->where('a.isPublic = :isPublic')
            ->setParameter('isPublic', true);
    }

    /**
     * @param string $slug
     *
     * @return int|mixed|string|null
     * @throws NonUniqueResultException
     */
    public function findPublicArticle(string $slug)
    {
        return $this->createQueryBuilder('a')
            ->where('a.slug = :slug')
            ->andWhere('a.isPublic = :isPublic')
            ->setParameter('slug', $slug)
            ->setParameter('isPublic', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $slug
     *
     * @return int|mixed|string|null
     * @throws NonUniqueResultException
     */
    public function findPrivateArticle(string $slug)
    {
        return $this->createQueryBuilder('a')
            ->where('a.slug = :slug')
            ->andWhere('a.isPublic = :isPublic')
            ->setParameter('slug', $slug)
            ->setParameter('isPublic', false)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPublicArticlesBySearchQuery(string $query)
    {
        return $this->createQueryBuilder('a')
            ->where("a.title LIKE :query")
            ->andWhere('a.isPublic = :isPublic')
            ->setParameter('query', '%'.$query.'%')
            ->setParameter('isPublic', true);
    }

    public function findPublicArticlesByCategoryQuery(Category $category)
    {
        return $this->createQueryBuilder('a')
            ->where('a.category = :category')
            ->andWhere('a.isPublic = :isPublic')
            ->setParameter('category', $category)
            ->setParameter('isPublic', true);
    }

    public function findPublicArticlesByTagQuery(Tag $tag)
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.tags', 't')
            ->where('t.id = :tag')
            ->andWhere('a.isPublic = :isPublic')
            ->setParameter('tag', $tag)
            ->setParameter('isPublic', true);
    }

    public function findPublicArticlesByUserQuery(User $user)
    {
        return $this->createQueryBuilder('a')
            ->where('a.author = :username')
            ->andWhere('a.isPublic = :isPublic')
            ->setParameter('username', $user)
            ->setParameter('isPublic', true);
    }

    public function findPrivateArticlesByUserQuery(User $user)
    {
        return $this->createQueryBuilder('a')
            ->where('a.author = :username')
            ->andWhere('a.isPublic = :isPublic')
            ->setParameter('username', $user)
            ->setParameter('isPublic', false);
    }

    public function findArticlesByUserQuery(User $user)
    {
        return $this->createQueryBuilder('a')
            ->where('a.author = :username')
            ->setParameter('username', $user);
    }

    // /**
    //  * @return Article[] Returns an array of Article objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
