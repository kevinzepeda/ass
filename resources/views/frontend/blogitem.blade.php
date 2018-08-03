@extends('layouts.frontend.user')
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
    <style>

    </style>
@stop
@section('content')
    <div class="features_section m-b-30">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="section-heading">
                        <h2 class="text-primary">{{ $blog->title }}</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-9">
                    @if(isset($blog))
                        <div class="card">
                            <div class="card-body comment_section">
                                @if($blog->image)
                                    <img src="{{ url('/uploads/blogs/'.$blog->image)  }}" class="img-fluid mb-3" alt="Image">
                                @endif
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
                                <p class="text_light">
                                    {!! $blog->content !!}
                                </p>
                                <p>
                                    <strong>Tags: </strong>
                                    @foreach($blog->tags as $tag)
                                        <a href="{{ url('blog/'.mb_strtolower($tag).'/tag') }}" class="text-primary">{{ $tag }}</a>
                                        @if(!$loop->last)
                                            ,
                                        @endif
                                    @endforeach
                                </p>
                            </div>
                        </div>
                    @else
                        <h3>No Posts Exists!</h3>
                    @endif
                    <div class="card">
                        <div class="card-header bg-white">
                            <h4 class="comments">{{$blog->comments->count()}} Comments</h4>
                        </div>
                        <div class="card-body">
                            @foreach($blog->comments as $comment)
                                <div class="media-body">
                                    <h4 class="media-heading"><i>{{$comment->name}}</i></h4>
                                    <div class="comment_section">{!! $comment->comment !!}</div>
                                    <div class="text_light">
                                        <i class="fa fa-clock-o"></i><small> {!! $comment->created_at!!}</small>
                                    </div>
                                </div>
                                @if(!$loop->last)
                                    <hr>
                                @endif
                            @endforeach
                        </div>
                    </div>
                        <div class="card">
                            <div class="card-header bg-white">
                                <h4 class="comments">Leave Comment</h4>
                            </div>
                            <div class="card-body">
                                {!! Form::open(['url' => URL::to('blogitem/'.$blog->id.'/comment'), 'method' => 'post', 'files'=> true]) !!}
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group required {{ $errors->has('name') ? 'has-error' : '' }}">
                                            {!! Form::label('name', trans('blog.name'), ['class' => 'control-label required']) !!}
                                            <div class="controls">
                                                {!! Form::text('name', isset($user)?$user->first_name.' '.$user->last_name:null, ['class' => 'form-control','placeholder'=>trans('blog.name')]) !!}
                                                <span class="help-block">{{ $errors->first('name', ':message') }}</span>
                                            </div>
                                        </div>
                                        <i class="fa fa-check check"></i>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group required {{ $errors->has('email') ? 'has-error' : '' }}">
                                            {!! Form::label('email', trans('blog.email'), ['class' => 'control-label required']) !!}
                                            <div class="controls">
                                                {!! Form::text('email', isset($user)?$user->email:null, ['class' => 'form-control','placeholder'=>trans('blog.email')]) !!}
                                                <span class="help-block">{{ $errors->first('email', ':message') }}</span>
                                            </div>
                                        </div>
                                        <i class="fa fa-check check"></i>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-group {{ $errors->has('website') ? 'has-error' : '' }}">
                                            {!! Form::label('website', trans('blog.website'), ['class' => 'control-label']) !!}
                                            <div class="controls">
                                                {!! Form::text('website', isset($user)?$user->website:null, ['class' => 'form-control','placeholder'=>trans('blog.website')]) !!}
                                                <span class="help-block">{{ $errors->first('website', ':message') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group required {{ $errors->has('comment') ? 'has-error' : '' }}">
                                            {!! Form::label('comment', trans('blog.comment'), ['class' => 'control-label required']) !!}
                                            <div class="controls">
                                                {!! Form::textarea('comment', null, ['class' => 'form-control','placeholder'=>trans('blog.comment')]) !!}
                                                <span class="help-block">{{ $errors->first('comment', ':message') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button  class="btn btn-success button_postcomment" type="submit"><i class="fa fa-comment"></i> Submit</button>
                                    </div>
                                </div>
                                {!! Form::close() !!}
                            </div>
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
    <script src="{{ asset('js/tagsinput/bootstrap-tagsinput.js') }}"></script>
    <script src="{{ asset('js/summernote/summernote-bs4.min.js') }}"></script>
    <script>
        $(document).ready(function(){
            $('#comment').summernote({
                height:200,
                placeholder: 'Comment...'
            });
        })
    </script>
@stop