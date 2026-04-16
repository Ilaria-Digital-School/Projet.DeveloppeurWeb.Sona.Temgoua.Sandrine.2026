<?php

// Déclaration du namespace du fichier.
// Cela permet d'organiser les classes du projet et d'éviter les conflits de noms.
namespace App\Repository;

// Import de l'entité Article qui correspond à la table en base de données.
use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 * 
 * Cette classe est le repository de l'entité Article.
 * Elle permet d'effectuer des requêtes personnalisées sur la table Article.
 */
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

    /**
     * Alternative avec QueryBuilder (plus flexible)
     */
    public function findPaginatedArticlesQB(int $page, int $limit = 10, ?string $search = null, ?User $user = null): array
    {
        $qb = $this->createQueryBuilder('a');
        
        // Gestion des permissions (admin voit tout, sinon ses articles)
        if ($user && !in_array('ROLE_ADMIN', $user->getRoles())) {
            $qb->andWhere('a.author = :user')
               ->setParameter('user', $user);
        }
        
        // Recherche dynamique
        if ($search && $search !== '') {
            $qb->andWhere('a.title LIKE :search OR a.content LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        
        // Tri et pagination
        $qb->orderBy('a.publishedAt', 'DESC')
           ->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);
        
        $paginator = new Paginator($qb);
        
        return [
            'articles' => $paginator,
            'total' => count($paginator),
            'currentPage' => $page,
            'totalPages' => ceil(count($paginator) / $limit)
        ];
    }

    public function findPaginatedArticles(int $page, int $limit = 10, ?string $search = null, $user = null): array
    {
        $queryBuilder = $this->createQueryBuilder('a');
        
        // Condition pour l'auteur ou admin
        if ($user && !in_array('ROLE_ADMIN', $user->getRoles())) {
            $queryBuilder->andWhere('a.author = :user')
                ->setParameter('user', $user);
        }
        
        // Recherche
        if ($search && $search !== '') {
            $queryBuilder->andWhere('a.title LIKE :search OR a.content LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        
        // Tri et pagination
        $queryBuilder->orderBy('a.publishedAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        
        $paginator = new Paginator($queryBuilder);
        
        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $limit);
        
        return [
            'items' => $paginator,
            'total' => $totalItems,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'limit' => $limit
        ];
    }

    // --------------------------------------------------------------------
    // EXEMPLES DE MÉTHODES FOURNIES PAR DOCTRINE (commentées par défaut)
    // --------------------------------------------------------------------

    //    /**
    //     * @return Article[] Returns an array of Article objects
    //     *
    //     * Exemple de méthode personnalisée permettant de récupérer
    //     * plusieurs articles selon un champ spécifique.
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    /**
    //     * Exemple de méthode permettant de récupérer un seul article
    //     * correspondant à une valeur précise.
    //     */
    //    public function findOneBySomeField($value): ?Article
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    // --------------------------------------------------------------------
    // MÉTHODE PERSONNALISÉE
    // --------------------------------------------------------------------

    /**
     * Cette méthode permet de récupérer tous les articles
     * correspondant à un type de transaction donné.
     * 
     * Exemple : "vente" ou "location".
     */
    public function findByTransactionType(string $type): array
    {
        return $this->createQueryBuilder('a') // Création d'un QueryBuilder avec l'alias "a" pour Article
            ->andWhere('a.transactionType = :type') // Condition WHERE sur le champ transactionType
            ->setParameter('type', $type) // Sécurisation de la valeur grâce à un paramètre
            ->orderBy('a.publishedAt', 'DESC') // Tri des résultats par date de publication (du plus récent au plus ancien)
            ->getQuery() // Génération de la requête Doctrine
            ->getResult(); // Exécution de la requête et récupération des résultats
    }

}

//findByTransactionType()

//sert à :"récupérer tous les articles correspondant 
//à un type de transaction spécifique (par exemple vente ou location) et les trier du plus récent au plus ancien."