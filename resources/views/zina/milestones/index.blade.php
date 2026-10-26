@extends('layouts.app-shell', ['shellRole' => 'student', 'shellActive' => 'milestones', 'shellStates' => true])

@section('title', 'Milestones · '.$timeline['project']['title'])

@push('styles')
    @vite('resources/css/pages/milestones.css')
@endpush

@section('prototype-controls')
    @include('zina.partials.context-switch', [
        'field' => 'viewer',
        'label' => 'Viewing as',
        'options' => ['aya' => 'Aya · team leader', 'malek' => 'Malek · team member'],
        'current' => $context['viewer'],
    ])
@endsection

@section('content')
    @php
        $project = $timeline['project'];
        $canSubmit = $timeline['can_submit'];
        $leaderName = $timeline['leader']['name'];
        $statuses = \App\Support\ZinaDemoData::SUBMISSION_STATUSES;
    @endphp
    <div class="as-page ml">
        @if ($state === 'loading')
            @include('zina.partials.state-loading', ['label' => 'Loading milestones', 'blocks' => 2])
        @elseif ($state === 'error')
            @include('zina.partials.state-error', ['title' => 'We couldn’t load the milestones.'])
        @else
            <header class="as-head">
                <div>
                    <p class="as-eyebrow">{{ $project['title'] }}@if ($project['team']) · {{ $project['team'] }}@endif</p>
                    <h1>Project milestones</h1>
                    <div class="as-badges ml-head-badges">
                        @include('zina.partials.project-type', ['type' => $project['type']])
                        @if ($project['course'])<x-badge>{{ $project['course'] }}</x-badge>@endif
                        @if ($project['type'] === 'Academic')
                            <x-badge :tone="$canSubmit ? 'warning' : 'neutral'">{{ $canSubmit ? 'Team leader view' : 'Team member view' }}</x-badge>
                        @endif
                    </div>
                </div>
            </header>

            @if ($project['type'] === 'Independent')
                <section class="as-empty" aria-labelledby="ml-independent-title">
                    <span class="as-empty-icon" aria-hidden="true">@include('public.partials.icon', ['name' => 'lock', 'size' => 24])</span>
                    <h2 id="ml-independent-title">Independent projects don’t have milestone submissions.</h2>
                    <p>{{ $project['title'] }} is student-led, so there’s no professor review or grade. Track your own checkpoints as tasks instead.</p>
                    <div class="as-empty-actions">
                        <x-button :href="route('student.milestones.index', 'software-engineering-capstone')">Go to Capstone milestones</x-button>
                    </div>
                </section>
            @elseif (! count($timeline['milestones']))
                <section class="as-empty" aria-labelledby="ml-empty-title">
                    <span class="as-empty-icon" aria-hidden="true">@include('public.partials.icon', ['name' => 'calendar', 'size' => 24])</span>
                    <h2 id="ml-empty-title">No milestones yet.</h2>
                    <p>{{ $project['professor'] }} hasn’t published milestones for this project. When they do, the deadlines and deliverables appear here and on your calendar.</p>
                </section>
            @else
                <ol class="ml-road">
                    @foreach ($timeline['milestones'] as $milestone)
                        <li @class(['ml-stop', 'ml-stop--'.$milestone['state']]) aria-labelledby="ml-{{ strtolower($milestone['code']) }}-title" @if ($milestone['state'] === 'active') aria-current="step" @endif>
                            <span class="ml-node" aria-hidden="true">
                                @if ($milestone['state'] === 'graded') @include('public.partials.icon', ['name' => 'check', 'size' => 20])
                                @elseif ($milestone['state'] === 'locked') @include('public.partials.icon', ['name' => 'lock', 'size' => 18])
                                @else {{ $milestone['code'] }} @endif
                            </span>

                            <article class="as-panel ml-card">
                                @if ($milestone['state'] === 'graded')
                                    {{-- M1: graded and locked --}}
                                    <div class="ml-card-head">
                                        <div>
                                            <div class="as-badges"><x-badge tone="success">Graded · locked</x-badge></div>
                                            <h2 id="ml-m1-title">{{ $milestone['code'] }} · {{ $milestone['title'] }}</h2>
                                            <p class="as-meta">Due {{ $milestone['due_label'] }}</p>
                                        </div>
                                        <p class="ml-grade"><span class="ml-grade-score"><strong>{{ $milestone['grade']['score'] }}</strong> / {{ $milestone['grade']['out_of'] }}</span><small>{{ $milestone['grade']['released'] }}</small></p>
                                    </div>
                                    <h3 class="ml-subhead">Rubric breakdown</h3>
                                    <dl class="ml-rubric">
                                        @foreach ($milestone['rubric'] as $row)
                                            <div>
                                                <dt>{{ $row['criterion'] }}</dt>
                                                <dd>
                                                    <span class="as-bar" aria-hidden="true"><span style="width: {{ $row['score'] / $row['max'] * 100 }}%"></span></span>
                                                    <strong>{{ $row['score'] }} / {{ $row['max'] }}</strong>
                                                </dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                    @include('zina.milestones.partials.feedback', ['feedback' => $milestone['feedback']])
                                    @include('zina.milestones.partials.versions', ['versions' => $milestone['versions'], 'code' => $milestone['code']])

                                @elseif ($milestone['state'] === 'active')
                                    {{-- M2: the active milestone --}}
                                    <div class="ml-card-head">
                                        <div>
                                            <div class="as-badges">
                                                <x-badge :tone="$milestone['status'] === 'Needs resubmission' ? 'review' : 'info'">{{ $milestone['status'] }}</x-badge>
                                                <x-badge tone="info">Active milestone</x-badge>
                                            </div>
                                            <h2 id="ml-m2-title">{{ $milestone['code'] }} · {{ $milestone['title'] }}</h2>
                                            <p class="as-meta">Due <time datetime="{{ $milestone['due']->toIso8601String() }}">{{ $milestone['due_label'] }}</time></p>
                                        </div>
                                        <p class="ml-countdown ml-countdown--{{ $milestone['countdown']['tone'] }}">
                                            <strong>{{ $milestone['countdown']['days'] }}</strong>
                                            <span>{{ $milestone['countdown']['days'] === 1 ? 'day left' : 'days left' }}</span>
                                        </p>
                                    </div>
                                    <p class="ml-description">{{ $milestone['description'] }}</p>

                                    <h3 class="ml-subhead" id="ml-status-title">Submission status</h3>
                                    <ol class="ml-status" aria-labelledby="ml-status-title">
                                        @foreach ($statuses as $status)
                                            @php($reached = array_search($status, $statuses) <= array_search($milestone['status'], $statuses))
                                            <li @class(['is-reached' => $reached, 'is-current' => $status === $milestone['status']]) @if ($status === $milestone['status']) aria-current="step" @endif>
                                                <span aria-hidden="true"></span>{{ $status }}@if ($status === $milestone['status'])<em> · current</em>@endif
                                            </li>
                                        @endforeach
                                    </ol>
                                    <ul class="ml-history">
                                        @foreach ($milestone['timeline'] as $event)
                                            <li><strong>{{ $event['status'] }}</strong> <span>{{ $event['when'] }} · {{ $event['by'] }}</span></li>
                                        @endforeach
                                    </ul>

                                    @include('zina.milestones.partials.feedback', ['feedback' => $milestone['feedback']])

                                    <section class="ml-submit" id="submit" aria-labelledby="ml-submit-title">
                                        <h3 class="ml-subhead" id="ml-submit-title">Required deliverables</h3>

                                        @if (session('submission_receipt'))
                                            @php($receipt = session('submission_receipt'))
                                            <div id="receipt" tabindex="-1">
                                                <x-alert tone="success" title="Version {{ $receipt['number'] }} received">
                                                    <p>Submitted by {{ $receipt['submitter'] }} on {{ $receipt['when'] }} and sent to {{ $project['professor'] }} and {{ $project['ta'] }} for review. Every earlier version is kept.@if ($receipt['late']) It’s marked Late, with no automatic penalty.@endif</p>
                                                    <p class="as-session-tag">Prototype: saved for this session</p>
                                                </x-alert>
                                            </div>
                                        @endif
                                        @if (session('submission_missing'))
                                            <x-alert tone="error" title="There was nothing to submit.">Add your deliverables again, then review and submit.</x-alert>
                                        @endif

                                        @if (! $canSubmit)
                                            <ul class="ml-deliverables">
                                                @foreach ($milestone['deliverables'] as $deliverable)
                                                    <li><span class="ml-deliverable-icon" aria-hidden="true">@include('public.partials.icon', ['name' => $deliverable['kind'] === 'link' ? 'link' : 'file', 'size' => 18])</span><span><strong>{{ $deliverable['label'] }}</strong><small>{{ $deliverable['help'] }}</small></span></li>
                                                @endforeach
                                            </ul>
                                            <ul class="ml-checklist-readonly" aria-label="Checklist before submitting">
                                                @foreach ($milestone['checklist'] as $item)
                                                    <li>@include('public.partials.icon', ['name' => 'check', 'size' => 16]) {{ $item }}</li>
                                                @endforeach
                                            </ul>
                                            <p class="as-restriction">@include('public.partials.icon', ['name' => 'lock', 'size' => 18]) <span><strong>Only your team leader, {{ $leaderName }}, can submit.</strong> You can see every requirement and all feedback here. Share your files with {{ $timeline['leader']['first'] }} in the project chat.</span></p>
                                        @elseif ($milestone['draft'])
                                            {{-- Confirm step --}}
                                            @php($next = $milestone['versions'][0]['number'] + 1)
                                            <div class="ml-confirm" id="confirm" tabindex="-1">
                                                @if (session('submission_failed'))
                                                    <x-alert tone="error" title="We couldn’t store version {{ $next }}.">
                                                        <p>Nothing was submitted, and version {{ $next - 1 }} is still the current one. Your draft is kept below, so you can retry now.</p>
                                                    </x-alert>
                                                @endif
                                                <h4>Review version {{ $next }} before you submit</h4>
                                                <ul class="ml-deliverables">
                                                    @foreach ($milestone['draft']['files'] as $file)
                                                        <li><span class="ml-deliverable-icon is-ready" aria-hidden="true">@include('public.partials.icon', ['name' => 'file', 'size' => 18])</span><span><strong>{{ $file['label'] }}</strong><small>{{ $file['name'] }} · {{ $file['size'] }}</small></span><x-badge tone="success">Ready</x-badge></li>
                                                    @endforeach
                                                    <li><span class="ml-deliverable-icon is-ready" aria-hidden="true">@include('public.partials.icon', ['name' => 'link', 'size' => 18])</span><span><strong>GitHub repository</strong><small class="ml-break">{{ $milestone['draft']['link'] }}</small></span><x-badge tone="success">Ready</x-badge></li>
                                                </ul>
                                                <p class="as-note">Submitting sends version {{ $next }} to {{ $project['professor'] }} and {{ $project['ta'] }}. You can still resubmit until the deadline. Prototype: only file names and sizes are kept, for this session.</p>
                                                <form method="POST" action="{{ route('student.milestones.submit', [$project['id'], 'm2']) }}" class="ml-confirm-form">
                                                    @csrf
                                                    @if ($simulateFailures)
                                                        <label class="as-check ml-simulate"><input type="checkbox" name="simulate_failure" value="1" @checked(session('submission_failed'))> <span>Prototype: simulate a storage failure on submit</span></label>
                                                    @endif
                                                    <div class="ml-confirm-actions">
                                                        <x-button type="submit" data-as-busy="Submitting…">
                                                            @if (session('submission_failed'))@include('public.partials.icon', ['name' => 'refresh', 'size' => 18]) Retry submission @else Submit version {{ $next }} to {{ $project['professor'] }} @endif
                                                        </x-button>
                                                    </div>
                                                </form>
                                                <form method="POST" action="{{ route('student.milestones.discard', [$project['id'], 'm2']) }}">
                                                    @csrf
                                                    <x-button type="submit" variant="ghost" size="small">Change the files</x-button>
                                                </form>
                                            </div>
                                        @else
                                            {{-- Upload form --}}
                                            @if ($errors->any())
                                                <x-alert tone="error" title="Check {{ $errors->count() === 1 ? 'one thing' : $errors->count().' things' }} before you continue.">
                                                    <p>Files can’t be kept after an error, so choose them again.</p>
                                                </x-alert>
                                            @endif
                                            <form method="POST" action="{{ route('student.milestones.draft', [$project['id'], 'm2']) }}" enctype="multipart/form-data" class="as-form ml-form" novalidate>
                                                @csrf
                                                @foreach ($milestone['deliverables'] as $field => $deliverable)
                                                    @if ($deliverable['kind'] === 'file')
                                                        @php($message = $errors->first($field))
                                                        <div class="ms-field ml-file">
                                                            <label for="ml-{{ $field }}" class="ms-field__label">{{ $deliverable['label'] }} <span class="ml-required">Required</span></label>
                                                            <input id="ml-{{ $field }}" name="{{ $field }}" type="file" accept="{{ $deliverable['accept'] }}" class="ml-file-input" required
                                                                aria-describedby="ml-{{ $field }}-message" @if ($message) aria-invalid="true" @endif>
                                                            <small id="ml-{{ $field }}-message" class="{{ $message ? 'ms-field__error' : 'ms-field__help' }}">{{ $message ?: $deliverable['help'] }}</small>
                                                        </div>
                                                    @else
                                                        <x-input id="ml-{{ $field }}" :name="$field" type="url" inputmode="url" :label="$deliverable['label'].' (required)'" :help="$deliverable['help']" :value="old($field, $milestone['versions'][0]['link'])" required />
                                                    @endif
                                                @endforeach

                                                <fieldset class="as-fieldset">
                                                    <legend>Before you submit</legend>
                                                    @foreach ($milestone['checklist'] as $key => $item)
                                                        @php($message = $errors->first('checklist.'.$key))
                                                        <label class="as-check">
                                                            <input type="checkbox" name="checklist[{{ $key }}]" value="1" @checked(old('checklist.'.$key)) @if ($message) aria-invalid="true" aria-describedby="ml-check-{{ $key }}-error" @endif>
                                                            <span>{{ $item }}@if ($message)<small id="ml-check-{{ $key }}-error" class="ms-field__error ml-check-error">{{ $message }}</small>@endif</span>
                                                        </label>
                                                    @endforeach
                                                </fieldset>
                                                <p class="as-note">Allowed: {{ strtoupper(implode(', ', $extensions)) }}. Up to 25 MB per file. Every version you submit is kept.</p>
                                                <div><x-button type="submit" data-as-busy="Checking files…">Review submission @include('public.partials.icon', ['name' => 'arrow', 'size' => 18])</x-button></div>
                                            </form>
                                        @endif
                                    </section>

                                    @include('zina.milestones.partials.versions', ['versions' => $milestone['versions'], 'code' => $milestone['code'], 'open' => true])

                                @else
                                    {{-- M3: locked --}}
                                    <div class="ml-card-head">
                                        <div>
                                            <div class="as-badges"><x-badge tone="warning">Locked</x-badge><x-badge>{{ $milestone['status'] }}</x-badge></div>
                                            <h2 id="ml-m3-title">{{ $milestone['code'] }} · {{ $milestone['title'] }}</h2>
                                            <p class="as-meta">{{ $milestone['lock_reason'] }} Due {{ $milestone['due_label'] }}.</p>
                                        </div>
                                    </div>
                                    <h3 class="ml-subhead">What you’ll hand in</h3>
                                    <ul class="ml-locked-list">
                                        @foreach ($milestone['requirements'] as $requirement)<li>{{ $requirement }}</li>@endforeach
                                    </ul>
                                @endif
                            </article>
                        </li>
                    @endforeach
                </ol>
            @endif
        @endif
    </div>
@endsection
