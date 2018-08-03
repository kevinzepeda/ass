<?php
namespace App\Repositories;
use Prettus\Repository\Contracts\RepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface BlogRepository extends RepositoryInterface
{
    public function getAll();
    public function uploadLogo(UploadedFile $file);
    public function generateThumbnail($file);
}