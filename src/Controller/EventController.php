<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    #[Route('/events', name: 'app_event_index')]
    public function index(): Response
    {
        return $this->render('event/index.html.twig');
    }

    #[Route('/events/new', name: 'app_event_new')]
    public function new(): Response
    {
        return new Response('Page création événement');
    }

    #[Route('/events/{slug}', name: 'app_event_show')]
    public function show(string $slug): Response
    {
        return new Response('Événement : '.$slug);
    }

    #[Route('/events/{slug}/edit', name: 'app_event_edit')]
    public function edit(string $slug): Response
    {
        return new Response('Modification : '.$slug);
    }
}