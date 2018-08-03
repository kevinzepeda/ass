<div class="card">
    <div class="card-body">
        @if (isset($blogCategory))
            {!! Form::model($blogCategory, ['url' => 'admin/'.$type . '/' . $blogCategory->id, 'method' => 'put', 'files'=> true]) !!}
        @else
            {!! Form::open(['url' => 'admin/'.$type, 'method' => 'post', 'files'=> true]) !!}
        @endif
        <div class="form-group required {{ $errors->has('title') ? 'has-error' : '' }}">
            {!! Form::label('name', trans('blog.blog_category_name'), ['class' => 'control-label required']) !!}
            <div class="controls">
                {!! Form::text('title', null, ['class' => 'form-control']) !!}
                <span class="help-block">{{ $errors->first('title', ':message') }}</span>
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
