<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlogCommentRequest;
use App\Http\Requests\BlogRequest;
use App\Repositories\BlogCategoryRepository;
use App\Repositories\BlogCommentRepository;
use App\Repositories\BlogRepository;
use App\Repositories\UserRepository;
use DataTables;

class BlogController extends Controller
{

    private $blogCategoryRepository;
    private $blogRepository;
    private $userRepository;
    private $blogCommentRepository;

    public function __construct(
        BlogCategoryRepository $blogCategoryRepository,
        BlogRepository $blogRepository,
        UserRepository $userRepository,
        BlogCommentRepository $blogCommentRepository
    ) {
        $this->blogCategoryRepository = $blogCategoryRepository;
        $this->blogRepository = $blogRepository;
        $this->userRepository = $userRepository;
        $this->blogCommentRepository = $blogCommentRepository;
        view()->share('type', 'blog');
    }

    public function index()
    {
        $title = trans('blog.blog_list');
        return view('admin.blog.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('blog.create_blog');
        $this->generateParams();
        return view('admin.blog.create',compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BlogRequest $request)
    {
        if ($request->hasFile('blog_avatar')) {
            $file = $request->file('blog_avatar');
            $file = $this->blogRepository->uploadLogo($file);

            $request->merge([
                'image' => $file->getFileInfo()->getFilename(),
            ]);
            $this->blogRepository->generateThumbnail($file);
        }
        $user = $this->userRepository->getUser();
        $request->merge(['user_id' => $user->id]);
        $blog = $this->blogRepository->create($request->except('blog_avatar','tags'));
        $blog->tag($request->tags?$request->tags:'');
        return redirect('admin/blog');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $blog = $this->blogRepository->find($id);
        $action = trans('action.show');
        $title = trans('blog.show_blog');

        return view('admin.blog.show', compact('title', 'blog', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $blog = $this->blogRepository->find($id);
        $title = trans('blog.edit_blog');
        $this->generateParams();

        return view('admin.blog.edit', compact('title', 'blog'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(BlogRequest $request, $id)
    {
        if ($request->hasFile('blog_avatar')) {
            $file = $request->file('blog_avatar');
            $file = $this->blogRepository->uploadLogo($file);

            $request->merge([
                'image' => $file->getFileInfo()->getFilename(),
            ]);
            $this->blogRepository->generateThumbnail($file);
        }
        $blog = $this->blogRepository->find($id);
        $blog->update($request->except('blog_avatar','files','tags'));
        $blog->retag($request->tags?$request->tags:'');
        return redirect('admin/blog');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $blog = $this->blogRepository->find($id);
        $action = '';
        $title = trans('blog.delete_blog');

        return view('admin.blog.delete', compact('title', 'blog', 'action'));
    }

    public function destroy($id)
    {
        $blog = $this->blogRepository->find($id);
        $blog->delete();
        return redirect('admin/blog');
    }

    public function data()
    {
        $blogs = $this->blogRepository->all()
            ->map(function ($blog) {
                return [
                    'id' => $blog->id,
                    'title' => $blog->title,
                    'created_at' => $blog->created_at->diffForHumans(),
                    'no_of_comments' => isset($blog->comments)?$blog->comments->count():0,
                ];
            });

        return DataTables::of($blogs)
            ->addColumn('actions', '
<a href="{{ url(\'admin/blog/\' . $id . \'/edit\' ) }}"  title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning"></i> </a>
                                            <a href="{{ url(\'admin/blog/\' . $id . \'/show\' ) }}"  title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                            @if($no_of_comments==0)
                                     <a href="{{ url(\'admin/blog/\' . $id . \'/delete\' ) }}"  title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                     @endif')
            ->removeColumn('id')
            ->rawColumns(['actions'])
            ->make();
    }

    public function storeComment(BlogCommentRequest $request, $blog)
    {
        $blog = $this->blogRepository->find($blog);
        $request->merge([
            'blog_id' => $blog->id
        ]);
        $this->blogCommentRepository->create($request->all());
        return redirect('admin/blog/'.$blog->id.'/show');
    }

    private function generateParams(){
        $blogCategories = $this->blogCategoryRepository->all()->pluck('title','id')->prepend(trans('blog.blog_category'), '');
        view()->share('blogCategories',$blogCategories);
    }
}
