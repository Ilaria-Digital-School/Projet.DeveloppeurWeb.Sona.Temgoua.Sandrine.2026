<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search/ajax', name: 'app_search_ajax', methods: ['GET'])]
    public function ajaxSearch(Request $request, ArticleRepository $articleRepository): JsonResponse
    {
        $term = $request->query->get('q', '');
        
        if (strlen($term) < 2) {
            return $this->json([
                'success' => false,
                'message' => 'Le terme de recherche doit contenir au moins 2 caractères',
                'results' => []
            ]);
        }

        $articles = $articleRepository->searchByTerm($term);
        
        $results = [];
        foreach ($articles as $article) {
            $results[] = [
                'id' => $article->getId(),
                'title' => $article->getTitle(),
                'summary' => $article->getSummary() ? substr($article->getSummary(), 0, 100) . '...' : '',
                'slug' => $article->getSlug(),
                'category' => $article->getCathegory() ? $article->getCathegory()->getName() : '',
                'transactionType' => $article->getTransactionType(),
                'image' => $article->getImage() ? '/images/' . $article->getImage() : null,
                'price' => $this->extractPrice($article->getContent()),
                'url' => $this->generateUrl('app_article_show', ['slug' => $article->getSlug()]),
                'publishedAt' => $article->getPublishedAt() ? $article->getPublishedAt()->format('d/m/Y') : null,
            ];
        }

        return $this->json([
            'success' => true,
            'results' => $results,
            'total' => count($results),
            'term' => $term
        ]);
    }

    

    #[Route('/search', name: 'app_search_page')]
    public function searchPage(Request $request, ArticleRepository $articleRepository): Response
    {
        $term = $request->query->get('q', '');
        $articles = $term ? $articleRepository->searchByTerm($term) : [];
        
        return $this->render('search/results.html.twig', [
            'term' => $term,
            'articles' => $articles
        ]);
    }

    private function extractPrice(?string $content): ?string
    {
        if (!$content) return null;
        
        // Pattern pour trouver un prix (ex: 10€, 10.50€, 1000€)
        preg_match('/(\d+[\.,]?\d*\s*[€$]|[€$]\s*\d+[\.,]?\d*)/', $content, $matches);
        return $matches[0] ?? null;
    }
}