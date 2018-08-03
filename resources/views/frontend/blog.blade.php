@extends('layouts.frontend.user')
@section('styles')
    <style>

    </style>
@stop
@section('content')
    <div class="features_section m-b-30">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="section-heading">
                        <h2>{{ trans('blog.blog') }}</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-9">
                    @if(isset($blogs))
                        @foreach($blogs as $blog)
                            <div class="card">
                                <div class="card-body">
                                    @if($blog->image)
                                        <img src="{{ url('/uploads/blogs/'.$blog->image)  }}" class="img-fluid mb-3" alt="Image">
                                    @endif
                                    <h3 class="text-primary"><a href="{{ URL::to('blogitem/'.$blog->slug) }}" class="text-primary">{{$blog->title}}</a></h3>
                                    <div class="text_light comment_section">
                                        {!! $blog->content !!}
                                    </div>
                                    <p>
                                        <strong>Tags: </strong>
                                        @foreach($blog->tags as $tag)
                                            <a href="{{ url('blog/'.mb_strtolower($tag).'/tag') }}" class="text-primary">{{ $tag }}</a>
                                            @if(!$loop->last)
                                                ,
                                            @endif
                                        @endforeach
                                    </p>
                                    <p class="additional-post-wrap">
                                        <span class="additional-post mr-3">
                                            <i class="fa fa-user text-info"></i> <span class="text_light">by</span>&nbsp;
                                            <a href="#" class="text-primary">
                                                {{ $blog->author->full_name }}
                                            </a>
                                        </span>
                                        <span class="additional-post mr-3">
                                            <i class="fa fa-clock-o text-info"></i><a href="#" class="text-primary"> {{$blog->created_at->diffForHumans()}}</a>
                                        </span>
                                        <span class="additional-post">
                                            <i class="fa fa-comment text-info"></i><a href="#" class="text-primary"> {{$blog->comments->count()}} comments</a>
                                        </span>
                                    </p>
                                    <hr>
                                    <p class="text-right">
                                        <a href="{{ url('blogitem/'.$blog->slug) }}" class="btn btn-primary text-white">Read more</a>
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <h3>No Posts Exists!</h3>
                    @endif
                        <div class="pager">
                            {!! $blogs->render() !!}
                        </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h4>Tags</h4>
                        </div>
                        <div class="card-body">
                            @if(isset($tags))
                                @foreach($tags as $tag)
                                    <a href="{{ URL::to('blog/'.$tag.'/tag') }}" class="text-primary">{{ $tag }}</a>
                                    @if(!$loop->last)
                                        ,
                                    @endif
                                @endforeach
                            @else
                                No Tags
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('scripts')
    <script src="{{ asset('front/vendors/isotope/js/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('front/vendors/imagesloaded/js/imagesloaded.pkgd.js') }}"></script>
    <script src="{{ asset('front/js/home.js') }}"></script>
    <script>
        new WOW().init();

    </script>
@stop
