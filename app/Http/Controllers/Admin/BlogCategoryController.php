<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlogCategoryRequest;
use App\Repositories\BlogCategoryRepository;
use DataTables;

class BlogCategoryController extends Controller
{

    private $blogCategoryRepository;

    public function __construct(
        BlogCategoryRepository $blogCategoryRepository
    ) {
        $this->blogCategoryRepository = $blogCategoryRepository;
        view()->share('type', 'blog_category');
    }

    public function index()
    {
        $title = trans('blog.blog_categories_list');
        return view('admin.blog_category.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('blog.create_blog_category');
        return view('admin.blog_category.create',compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BlogCategoryRequest $request)
    {
        $this->blogCategoryRepository->create($request->all());
        return redirect('admin/blog_category');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $blogCategory = $this->blogCategoryRepository->find($id);
        $action = trans('action.show');
        $title = trans('blog.show_blog_category');

        return view('admin.blog_category.show', compact('title', 'blogCategory', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $blogCategory = $this->blogCategoryRepository->find($id);
        $title = trans('blog.edit_blog_category');

        return view('admin.blog_category.edit', compact('title', 'blogCategory'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(BlogCategoryRequest $request, $id)
    {
        $blogCategory = $this->blogCategoryRepository->find($id);
        $blogCategory->update($request->all());

        return redirect('admin/blog_category');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $blogCategory = $this->blogCategoryRepository->find($id);
        $action = '';
        $title = trans('blog.delete_blog_category');

        return view('admin.blog_category.delete', compact('title', 'blogCategory', 'action'));
    }

    public function destroy($id)
    {
        $blogCategory = $this->blogCategoryRepository->find($id);
        $blogCategory->delete();
        return redirect('admin/blog_category');
    }

    public function data()
    {
        $blogCategories = $this->blogCategoryRepository->all()
            ->map(function ($blogCategory) {
                return [
                    'id' => $blogCategory->id,
                    'title' => $blogCategory->title,
                    'no_of_blogs' => isset($blogCategory->blog)?$blogCategory->blog->count():0,
                    'created_at' => $blogCategory->created_at->diffForHumans(),
                ];
            });

        return DataTables::of($blogCategories)
            ->addColumn('actions', '
<a href="{{ url(\'admin/blog_category/\' . $id . \'/edit\' ) }}"  title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning"></i> </a>
                                            @if($no_of_blogs==0 )
                                     <a href="{{ url(\'admin/blog_category/\' . $id . \'/delete\' ) }}"  title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                     @endif')
            ->removeColumn('id')
            ->rawColumns(['actions'])
            ->make();
    }
}
