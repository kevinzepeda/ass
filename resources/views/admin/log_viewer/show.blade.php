@extends('layouts.user')
@section('title')
    {{ $title }}
@stop
@section('styles')
    <link rel="stylesheet" href="{{asset('css/log_viewer.css')}}">
@stop
@section('content')
    <div class="page-header clearfix">
        <div class="pull-right">
            <a href="{{ url('admin/log_viewer/logs') }}" class="btn btn-warning m-b-10">
                <i class="fa fa-arrow-left"></i> {{ trans('table.back') }}</a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            {{--@include('admin.log_viewer._partials.menu')--}}
        </div>
        <div class="col-md-12">
            {{-- Log Details --}}
            <div class="card">
                <div class="card-header bg-white">
                    <h4 class="pull-left">
                        {{ trans('log_viewer.log_info') }} :
                    </h4>
                    <div class="group-btns pull-right">
                        <a href="{{ url('admin/log_viewer/download/'.$log->date) }}" class="btn btn-xs btn-success">
                            <i class="fa fa-download"></i> {{ trans('log_viewer.download') }}
                        </a>
                        <a href="#delete-log-modal" class="btn btn-danger" data-toggle="modal">
                            <i class="fa fa-trash-o"></i> {{ trans('log_viewer.delete') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                            <tr>
                                <td>{{ trans('log_viewer.file_path') }} :</td>
                                <td colspan="7">{{ $log->getPath() }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('log_viewer.log_entries') }} : </td>
                                <td>
                                    <span class="badge badge-primary">{{ $entries->total() }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td>{{ trans('log_viewer.size') }} :</td>
                                <td>
                                    <span class="badge badge-primary">{{ $log->size() }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td>{{ trans('log_viewer.created_at') }} :</td>
                                <td>
                                    <span class="badge badge-primary">{{ $log->createdAt() }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td>{{ trans('log_viewer.updated_at') }} :</td>
                                <td>
                                    <span class="badge badge-primary">{{ $log->updatedAt() }}</span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    {{-- Search --}}
                    <form action="{{ url('admin/log_viewer/logs/'.$log->date.'/'.$level.'/search') }}" method="GET">
                        <div class=form-group">
                            <div class="input-group mb-3">
                                <input id="query" name="query" class="form-control"  value="{!! request('query') !!}" placeholder="typing something to search">
                                <div class="input-group-append">
                                    @if (request()->has('query'))
                                        <a href="{{ url('admin/log_viewer/logs/'.$log->date) }}" class="btn btn-danger"><span class="fa fa-trash"></span></a>
                                    @endif
                                    <button id="search-btn" class="btn btn-primary"><span class="fa fa-search"></span></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Log Entries --}}
            <div class="card">
                @if ($entries->hasPages())
                    <div class="card-header bg-white">
                        <div class="float-left">
                            {!! $entries->appends(compact('query'))->render() !!}
                        </div>
                        <div class="float-right">
                            <span class="label label-info pull-right">
                                Page {!! $entries->currentPage() !!} of {!! $entries->lastPage() !!}
                            </span>
                        </div>
                    </div>
                @endif
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="entries" class="table table-condensed">
                            <thead>
                            <tr>
                                <th>{{ trans('log_viewer.env') }}</th>
                                <th style="width: 120px;">{{ trans('log_viewer.level') }}</th>
                                <th style="width: 65px;">{{ trans('log_viewer.time') }}</th>
                                <th>{{ trans('log_viewer.header') }}</th>
                                <th class="text-right">{{ trans('log_viewer.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($entries as $key => $entry)
                                <tr>
                                    <td>
                                        <span class="level label label-env">{{ $entry->env }}</span>
                                    </td>
                                    <td>
                                        <span class="level level-{{ $entry->level }}">
                                            {!! $entry->level() !!}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-success">
                                            {{ $entry->datetime->format('H:i:s') }}
                                        </span>
                                    </td>
                                    <td>
                                        <p>{{ $entry->header }}</p>
                                    </td>
                                    <td class="text-right">
                                        @if ($entry->hasStack())
                                            <a class="btn btn-xs btn-default" role="button" data-toggle="collapse" href="#log-stack-{{ $key }}" aria-expanded="false" aria-controls="log-stack-{{ $key }}">
                                                <i class="fa fa-toggle-on"></i> Stack
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @if ($entry->hasStack())
                                    <tr>
                                        <td colspan="5" class="stack">
                                            <div class="stack-content collapse" id="log-stack-{{ $key }}">
                                                {!! $entry->stack() !!}
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <span class="badge badge-success">{{ trans('log-viewer::general.empty-logs') }}</span>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                    @if ($entries->hasPages())
                        <div class="card-footer bg-white">
                            <div class="float-left">
                                {!! $entries->appends(compact('query'))->render() !!}
                            </div>
                            <div class="float-right">
                                <span class="label label-info pull-right">
                                    Page {!! $entries->currentPage() !!} of {!! $entries->lastPage() !!}
                                </span>
                            </div>
                        </div>
                    @endif
            </div>
        </div>
    </div>
    {{-- DELETE MODAL --}}
    <div id="delete-log-modal" class="modal fade">
        <div class="modal-dialog">
            <form id="delete-log-form" action="{{ url('admin/log_viewer/logs/delete') }}" method="POST">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="date" value="{{ $log->date }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">{{ trans('log_viewer.delete_log_file') }}</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to <span class="badge badge-danger">{{ trans('log_viewer.delete') }}</span> this log file <span class="badge badge-primary">{{ $log->date }}</span> ?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-default pull-left" data-dismiss="modal">{{ trans('log_viewer.cancel') }}</button>
                        <button type="submit" class="btn btn-sm btn-danger" data-loading-text="Loading&hellip;">{{ trans('log_viewer.delete_file') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            var deleteLogModal = $('div#delete-log-modal'),
                deleteLogForm  = $('form#delete-log-form'),
                submitBtn      = deleteLogForm.find('button[type=submit]');

            deleteLogForm.on('submit', function(event) {
                event.preventDefault();
                submitBtn.button('loading');
                $.ajax({
                    url:      $(this).attr('action'),
                    type:     $(this).attr('method'),
                    dataType: 'json',
                    data:     $(this).serialize(),
                    success: function(data) {
                        submitBtn.button('reset');
                        if (data.result === 'success') {
                            deleteLogModal.modal('hide');
                            location.replace("{{ url('admin/log_viewer/logs') }}");
                        }
                        else {
                            alert('OOPS ! This is a lack of coffee exception !')
                        }
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        alert('AJAX ERROR ! Check the console !');
                        console.error(errorThrown);
                        submitBtn.button('reset');
                    }
                });

                return false;
            });

            @unless (empty(log_styler()->toHighlight()))
            $('.stack-content').each(function() {
                var $this = $(this);
                var html = $this.html().trim()
                    .replace(/({!! join(log_styler()->toHighlight(), '|') !!})/gm, '<strong>$1</strong>');

                $this.html(html);
            });
            @endunless
        });
    </script>
@endsection
