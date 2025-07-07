<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * Cette méthode filtre les posts en fonction du tag précisé.
     *
     * @return array<int, Post>
     */
    public function filterPostByTag(int $tag_id): array
    {
        return $this->createQueryBuilder('p') // On fait une requete sur la table des posts
                 ->join('p.tags', 't') // jointure entre post et tag
                 ->select('p') // Je récupere les posts associés aux tag
                 ->where('t.id = :id') // la ou les id des tag correspondent a la valeur qui suit
                 ->andWhere('p.isPublished = :val') // Ou les post ont un isPublished = a la valeur qui suit
                 ->setParameter('id', $tag_id) // On défini la valeur
                 ->setParameter('val', true) // On défini la valeur
                 ->orderBy('p.publishedAt', 'DESC') // On défini l'ordre
                 ->getQuery() // On effectue la requete
                 ->getResult() // Recuperer la reponse
        ;
    }

    //    /**
    //     * @return Post[] Returns an array of Post objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Post
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
