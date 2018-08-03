<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlogCommentRequest;
use App\Repositories\BlogCommentRepository;
use App\Repositories\BlogRepository;
use App\Repositories\OrganizationSettingsRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\UserRepository;

class BlogController extends Controller
{
    private $settingsRepository;
    private $userRepository;
    private $organizationSettingsRepository;
    private $blogRepository;
    private $blogCommentRepository;
    private $tags;

    public function __construct(
        SettingsRepository $settingsRepository,
        UserRepository $userRepository,
        OrganizationSettingsRepository $organizationSettingsRepository,
        BlogRepository $blogRepository,
        BlogCommentRepository $blogCommentRepository
    ) {
        parent::__construct();
        $this->settingsRepository = $settingsRepository;
        $this->userRepository = $userRepository;
        $this->organizationSettingsRepository = $organizationSettingsRepository;
        $this->blogRepository = $blogRepository;
        $this->blogCommentRepository = $blogCommentRepository;
        $this->tags = $this->blogRepository->getAll()->allTags();
        view()->share('no_vue', true);
    }


    public function index()
    {
        $title = trans('blog.blog');
        $nav_id = 'contact_page1';
        $navbar_custom = trans('frontend.custom_navbar');
        $blogs = $this->blogRepository->getAll()->latest()->paginate(5);
        $tags = $this->tags;
        return view('frontend.blog', compact('title','nav_id','navbar_custom','blogs','tags'));
    }

    public function getBlog($slug = '')
    {
        $title = $slug;
        $nav_id = 'contact_page1';
        $navbar_custom = trans('frontend.custom_navbar');
        $blog = $this->blogRepository->all()->where('slug', $slug)->first();
        if (!isset($blog)){
            abort('404');
        }
        $tags = $this->tags;
        $user = $this->userRepository->getUser();
        return view('frontend.blogitem', compact('blog','title','nav_id','navbar_custom','tags','user'));
    }

    /**
     * @param $tag
     * @return \Illuminate\View\View
     */
    public function getBlogTag($tag)
    {
        $title = $tag;
        $nav_id = 'contact_page1';
        $navbar_custom = trans('frontend.custom_navbar');
        $blogs = $this->blogRepository->getAll()->withAnyTags($tag)->paginate(5);
        $tags = $this->tags;
        return view('frontend.blog', compact('blogs', 'tags','title','nav_id','navbar_custom'));
    }

    public function storeComment(BlogCommentRequest $request, $blog)
    {
        $blog = $this->blogRepository->find($blog);
        $request->merge([
           'blog_id' => $blog->id
        ]);
        $this->blogCommentRepository->create($request->all());
        return redirect('blogitem/' . $blog->slug);
    }


}
