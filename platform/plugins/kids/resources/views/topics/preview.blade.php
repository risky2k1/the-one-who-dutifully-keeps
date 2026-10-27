@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">{{ $topic->name }} (Grade {{ $topic->grade }}) — Total {{ $totalQuestions }} questions generated</h4>
                <a href="{{ route('kids.topics.edit', $topic->getKey()) }}" class="btn btn-outline-primary btn-sm">Edit Topic</a>
            </div>
            <div class="card-body">
                @if ($totalQuestions === 0)
                    <div class="alert alert-info">
                        This topic has no generator configured or is a category. Preview is only available for leaf topics with a generator.
                    </div>
                @else
                    @foreach ($levels as $lvl => $questions)
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 text-primary">Level {{ $lvl }} ({{ count($questions) }} questions)</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px" class="text-center">#</th>
                                            <th>Prompt</th>
                                            <th style="width: 160px" class="text-center">Answer</th>
                                            <th>Hints</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($questions as $idx => $q)
                                            <tr class="preview-question-row">
                                                <td class="text-center font-monospace">{{ $idx + 1 }}</td>
                                                <td>
                                                    @if (isset($q->prompt['text']))
                                                        <span class="fs-6">{{ $q->prompt['text'] }}</span>
                                                    @elseif (isset($q->prompt['left']) && isset($q->prompt['right']))
                                                        <span class="fs-6 font-monospace">{{ $q->prompt['left'] }} [ ? ] {{ $q->prompt['right'] }}</span>
                                                    @else
                                                        <code>{{ json_encode($q->prompt, JSON_UNESCAPED_UNICODE) }}</code>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success-subtle text-success fs-6 font-monospace">{{ $q->answer->display() }}</span>
                                                </td>
                                                <td>
                                                    @if (! empty($q->hints))
                                                        <ul class="mb-0 ps-3 small text-muted">
                                                            @foreach ($q->hints as $hint)
                                                                <li>{{ $hint }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <span class="text-muted fst-italic">None</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@stop
