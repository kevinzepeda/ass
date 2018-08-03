<div class="card">
    <div class="card-header bg-white">
        <h4><i class="fa fa-fw fa-flag"></i> Levels</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <tbody>
                <tr>
                    @foreach($log->menu() as $level => $item)
                        @if ($item['count'] === 0)
                            <td>
                                <a href="#" class="list-group-item disabled">
                                    <span class="badge">
                                        {{ $item['count'] }}
                                    </span>
                                    {!! $item['icon'] !!} {{ $item['name'] }}
                                </a>
                            </td>
                        @else
                            <td>
                                <a href="{{ $item['url'] }}" class="list-group-item {{ $level }}">
                                    <span class="badge level-{{ $level }}">
                                        {{ $item['count'] }}
                                    </span>
                                    <span class="level level-{{ $level }}">
                                        {!! $item['icon'] !!} {{ $item['name'] }}
                                    </span>
                                </a>
                            </td>
                        @endif
                    @endforeach
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
