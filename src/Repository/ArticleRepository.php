<?php

// Déclaration du namespace du fichier.
// Cela permet d'organiser les classes du projet et d'éviter les conflits de noms.
namespace App\Repository;

// Import de l'entité Article qui correspond à la table en base de données.
use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
class ArticleRepository extends ServiceEntityRepository
{
    // Constructeur appelé automatiquement par Symfony
    // Il injecte le ManagerRegistry pour permettre à Doctrine
    // d'accéder au gestionnaire d'entités et à la base de données.
    public function __construct(ManagerRegistry $registry)
    {
        // On appelle le constructeur parent en lui passant :
        // - le registry
        // - la classe de l'entité gérée (Article)
        parent::__construct($registry, Article::class);
    }
    public function findByTransactionType(string $type): array
    {
        return $this->createQueryBuilder('a') // Création d'un QueryBuilder avec l'alias "a" pour Article
            ->andWhere('a.transactionType = :type') // Condition WHERE sur le champ transactionType
            ->setParameter('type', $type) // Sécurisation de la valeur grâce à un paramètre
            ->orderBy('a.publishedAt', 'DESC') // Tri des résultats par date de publication (du plus récent au plus ancien)
            ->getQuery() // Génération de la requête Doctrine
            ->getResult(); // Exécution de la requête et récupération des résultats
    }

    /**
     * Recherche des articles par terme
     */
    public function searchByTerm(string $term): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.cathegory', 'c')
            ->leftJoin('a.author', 'u')
            ->where('a.isDeleted = :isDeleted')
            ->andWhere('a.isVerified = :isVerified')
            ->andWhere('LOWER(a.title) LIKE LOWER(:term)')
            ->orWhere('LOWER(a.content) LIKE LOWER(:term)')
            ->orWhere('LOWER(a.summary) LIKE LOWER(:term)')
            ->orWhere('LOWER(c.name) LIKE LOWER(:term)')
            ->setParameter('isDeleted', false)
            ->setParameter('isVerified', true)
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults(10);

        return $qb->getQuery()->getResult();
    }

    /**
     * Recherche avancée avec filtres
     */
    public function advancedSearch(array $criteria): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.cathegory', 'c')
            ->leftJoin('a.author', 'u')
            ->where('a.isDeleted = :isDeleted')
            ->andWhere('a.isVerified = :isVerified')
            ->setParameter('isDeleted', false)
            ->setParameter('isVerified', true);

        if (!empty($criteria['term'])) {
            $qb->andWhere('LOWER(a.title) LIKE LOWER(:term) 
                OR LOWER(a.content) LIKE LOWER(:term) 
                OR LOWER(a.summary) LIKE LOWER(:term)')
                ->setParameter('term', '%' . $criteria['term'] . '%');
        }

        if (!empty($criteria['category'])) {
            $qb->andWhere('c.id = :category')
                ->setParameter('category', $criteria['category']);
        }

        if (!empty($criteria['transactionType'])) {
            $qb->andWhere('a.transactionType = :transactionType')
                ->setParameter('transactionType', $criteria['transactionType']);
        }

        return $qb->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

}
