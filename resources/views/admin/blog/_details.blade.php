@section('styles')
    <link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
@stop
<div class="form-group">
    <div class="controls">
        @if (@$action == trans('action.show'))
            <a href="{{ url('admin/'.$type) }}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
        @else
            <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> {{trans('table.delete')}}</button>
            <a href="{{ url('admin/'.$type) }}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
        @endif
    </div>
</div>
@if(isset($blog))
    <div class="card">
        <div class="card-header bg-white">
            <h4>
                {{ $blog->title }}
            </h4>
        </div>
        <div class="card-body">
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
                    <span class="text_light">{{ $tag }}</span>
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
@if (@$action == trans('action.show'))
    <div class="card">
        <div class="card-header bg-white">
            <h4 class="comments">{{$blog->comments->count()}} Comments</h4>
        </div>
        <div class="card-body">
            @foreach($blog->comments as $comment)
                <div class="media-body">
                    <h4 class="media-heading"><i>{{$comment->name}}</i></h4>
                    <div>{!! $comment->comment !!}</div>
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
            {!! Form::open(['url' => URL::to('admin/blogitem/'.$blog->id.'/comment'), 'method' => 'post', 'files'=> true]) !!}
            <div class="row">
                <div class="col-12">
                    <div class="form-group required {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! Form::label('name', trans('blog.name'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('name', isset($user)?$user->first_name.' '.$user->last_name:null, ['class' => 'form-control','placeholder'=>trans('blog.name')]) !!}
                            <span class="help-block">{{ $errors->first('name', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group required {{ $errors->has('email') ? 'has-error' : '' }}">
                        {!! Form::label('email', trans('blog.email'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('email', isset($user)?$user->email:null, ['class' => 'form-control','placeholder'=>trans('blog.email')]) !!}
                            <span class="help-block">{{ $errors->first('email', ':message') }}</span>
                        </div>
                    </div>
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
@endif
@section('scripts')
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