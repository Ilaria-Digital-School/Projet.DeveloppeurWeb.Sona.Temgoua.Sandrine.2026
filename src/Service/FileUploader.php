<?php
// src/Service/FileUploader.php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileUploader
{
    private string $targetDirectory;
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger, ParameterBagInterface $params)
    {
        $this->slugger = $slugger;
        // Récupérer le paramètre depuis la configuration
        $this->targetDirectory = $params->get('kernel.project_dir') . '/public/images';
        
        // Créer le répertoire s'il n'existe pas
        if (!is_dir($this->targetDirectory)) {
            mkdir($this->targetDirectory, 0777, true);
        }
    }

    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->targetDirectory, $fileName);
        } catch (FileException $e) {
            throw new \Exception('Erreur lors du téléchargement du fichier: ' . $e->getMessage());
        }

        return $fileName;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
    
    public function delete(string $fileName): bool
    {
        $filePath = $this->targetDirectory . '/' . $fileName;
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }
}