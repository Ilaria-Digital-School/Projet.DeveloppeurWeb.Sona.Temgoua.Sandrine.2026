<?php

namespace App\Controller;

use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ImageController extends AbstractController
{
    #[Route('/image/delete/{id}', name: 'app_image_delete', methods: ['POST'])]
    public function delete(
        Image $image,
        Request $request,
        EntityManagerInterface $em
    ): Response
    {
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->request->get('_token'))) {
//            // ✅ OPTION 2 : suppression physique (hard delete)
        $imagePath = $this->getParameter('images_directory') . '/' . $image->getImage();
        if (file_exists($imagePath)) {
            unlink($imagePath);

        }// ✅ supprimer en base
        $em->remove($image);
        $em->flush();
        }
        return $this->redirectToRoute('app_article_show', [
            'slug' => $image->getArticle()->getSlug()
        ]);
    }

}