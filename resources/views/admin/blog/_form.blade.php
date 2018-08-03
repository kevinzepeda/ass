@section('styles')
    <link rel="stylesheet" href="{{ asset('css/tagsinput/bootstrap-tagsinput.css') }}">
    <link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
@stop
<div class="card">
    <div class="card-body">
        @if (isset($blog))
            {!! Form::model($blog, ['url' => 'admin/'.$type . '/' . $blog->id, 'method' => 'put', 'files'=> true]) !!}
        @else
            {!! Form::open(['url' => 'admin/'.$type, 'method' => 'post', 'files'=> true]) !!}
        @endif
        <div class="row">
            <div class="col-md-8">
                <div class="form-group required {{ $errors->has('title') ? 'has-error' : '' }}">
                    {!! Form::label('name', trans('blog.title'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('title', null, ['class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('title', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group required {{ $errors->has('content') ? 'has-error' : '' }}">
                    {!! Form::label('content', trans('blog.description'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::textarea('content', null, ['class' => 'form-control', 'placeholder' => 'Please some text here']) !!}
                        <span class="help-block">{{ $errors->first('content', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group required {{ $errors->has('blog_category_id') ? 'has-error' : '' }}">
                    {!! Form::label('blog_category_id', trans('blog.blog_category'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('blog_category_id', $blogCategories, null, ['class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('blog_category_id', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group {{ $errors->has('tags') ? 'has-error' : '' }}">
                    {!! Form::label('tags', trans('blog.tags'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::text('tags', isset($blog)?$blog->tagList:null, ['class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('tags', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group required {{ $errors->has('blog_avatar') ? 'has-error' : '' }}">
                    {!! Form::label('blog_avatar', trans('blog.featured_image'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        <div class="row">
                            @if(isset($blog->image))
                                <image-upload name="blog_avatar" old-image="{{ url('uploads/blogs/thumb_'.$blog->image) }}"></image-upload>
                            @else
                                <image-upload name="blog_avatar"></image-upload>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-group">
            <div class="controls">
                <button type="submit" class="btn btn-success"><i class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
                <a href="{{ route($type.'.index') }}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
            </div>
        </div>
        <!-- ./ form actions -->

        {!! Form::close() !!}
    </div>
</div>
@section('scripts')
    <script src="{{ asset('js/tagsinput/bootstrap-tagsinput.js') }}"></script>
    <script src="{{ asset('js/summernote/summernote-bs4.min.js') }}"></script>
    <script>
        $(document).ready(function(){
            $("#tags").tagsinput();
            $("#blog_category_id").select2({
                theme:'bootstrap',
                placeholder:'{{ trans('blog.blog_category') }}'
            });
            $('#content').summernote({
                height:200,
                placeholder: 'write content here...',
            });
        })
    </script>
@stop