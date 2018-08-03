<?php
namespace App\Repositories;
use Prettus\Repository\Contracts\RepositoryInterface;

interface BlogCommentRepository extends RepositoryInterface
{
    public function getAll();
}