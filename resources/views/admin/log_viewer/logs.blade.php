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
            <a href="{{ url('admin/log_viewer') }}" class="btn btn-warning m-b-10">
                <i class="fa fa-arrow-left"></i> {{ trans('table.back') }}</a>
        </div>
    </div>
    <div class="card">
        <div class="card-header bg-white">
            <h4>{{ trans('log_viewer.logs') }}</h4>
        </div>
        <div class="card-body">
            {!! $rows->render() !!}

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        @foreach($headers as $key => $header)
                            <th class="{{ $key == 'date' ? 'text-left' : 'text-center' }}">
                                @if ($key == 'date')
                                    <span class="label label-info">{{ $header }}</span>
                                @else
                                    <span class="level level-{{ $key }}">
                                {!! log_styler()->icon($key) . ' ' . $header !!}
                            </span>
                                @endif
                            </th>
                        @endforeach
                        <th class="text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if ($rows->count() > 0)
                        @foreach($rows as $date => $row)
                            <tr>
                                @foreach($row as $key => $value)
                                    <td class="{{ $key == 'date' ? 'text-left' : 'text-center' }}">
                                        @if ($key == 'date')
                                            <span class="label label-primary">{{ $value }}</span>
                                        @elseif ($value == 0)
                                            <span class="level level-empty">{{ $value }}</span>
                                        @else
                                            <a href="{{ url('admin/log_viewer/logs/'.$date.'/'.$key) }}">
                                                <span class="level level-{{ $key }}">{{ $value }}</span>
                                            </a>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-right">
                                    <a href="{{ url('admin/log_viewer/logs/'.$date) }}" >
                                        <i class="fa fa-fw fa-eye text-primary"></i>
                                    </a>
                                    <a href="{{ url('admin/log_viewer/download/'.$date) }}">
                                        <i class="fa fa-download text-success"></i>
                                    </a>
                                    <a href="#delete-log-modal" data-log-date="{{ $date }}">
                                        <i class="fa fa-trash-o text-danger"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="11" class="text-center">
                                <span class="badge badge-success">{{ trans('log-viewer::general.empty-logs') }}</span>
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>

            {!! $rows->render() !!}
        </div>
    </div>
    {{-- DELETE MODAL --}}
    <div id="delete-log-modal" class="modal fade">
        <div class="modal-dialog">
            <form id="delete-log-form" action="{{ url('admin/log_viewer/logs/delete') }}" method="POST">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="date" value="">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">{{ trans('log_viewer.delete_log_file') }}</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p></p>
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

            $("a[href=\\#delete-log-modal]").on('click', function(event) {
                event.preventDefault();
                var date = $(this).data('log-date');
                deleteLogForm.find('input[name=date]').val(date);
                deleteLogModal.find('.modal-body p').html(
                    'Are you sure you want to <span class="badge badge-danger">DELETE</span> this log file <span class="badge badge-primary">' + date + '</span> ?'
                );

                deleteLogModal.modal('show');
            });

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
                            location.reload();
                        }
                        else {
                            alert('AJAX ERROR ! Check the console !');
                            console.error(data);
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

            deleteLogModal.on('hidden.bs.modal', function() {
                deleteLogForm.find('input[name=date]').val('');
                deleteLogModal.find('.modal-body p').html('');
            });
        });
    </script>
@endsection
