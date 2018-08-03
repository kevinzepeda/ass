<?php

namespace App\Repositories;

use App\Helpers\Thumbnail;
use App\Models\Blog;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BlogRepositoryEloquent extends BaseRepository implements BlogRepository
{
    private $userRepository;
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return Blog::class;
    }

    /**
     * Boot up the repository, pushing criteria.
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function generateParams(){


        $this->userRepository = new UserRepositoryEloquent(app());
    }

    public function getAll()
    {
        return $this->model;
    }

    public function uploadLogo(UploadedFile $file)
    {
        $destinationPath = public_path().'/uploads/blogs/';
        $extension = $file->getClientOriginalExtension() ?: 'png';
        $fileName = str_random(10).'.'.$extension;

        return $file->move($destinationPath, $fileName);
    }

    public function generateThumbnail($file)
    {
        Thumbnail::generate_image_thumbnail(public_path().'/uploads/blogs/'.$file->getFileInfo()->getFilename(),
            public_path().'/uploads/blogs/'.'thumb_'.$file->getFileInfo()->getFilename());
    }
}
