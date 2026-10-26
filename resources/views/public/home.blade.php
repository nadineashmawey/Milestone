@extends('layouts.public')

@section('title', 'Group projects that don’t fall apart')

@section('content')
    <section class="hp-hero" id="top" aria-labelledby="hp-hero-title">
        <div class="hp-hero-copy">
            <p class="hp-kicker"><span aria-hidden="true"></span>For university project courses</p>
            <h1 id="hp-hero-title">Group projects that don’t fall apart.</h1>
            <p class="hp-lede">Milestone brings professors, teaching assistants and student teams into one workspace, from forming teams to the final grade. Everyone sees the same deadlines, and nobody has to ask who did what.</p>
            <div class="hp-hero-actions">
                @auth
                    <x-button size="large" :href="$accountLinks['dashboard']">Go to dashboard @include('public.partials.icon', ['name' => 'arrow', 'size' => 18])</x-button>
                @else
                    <x-button size="large" :href="$accountLinks['register']">Start with Milestone @include('public.partials.icon', ['name' => 'arrow', 'size' => 18])</x-button>
                @endauth
                <x-button variant="secondary" size="large" href="#how">See how it works</x-button>
            </div>
            <p class="hp-note">Prototype. Email, GitHub evidence and risk predictions are simulated.</p>
        </div>

        <div class="hp-preview" role="img" aria-label="Preview of a team workspace showing the M2 milestone, three tasks and a submission receipt">
            <div class="hp-window" aria-hidden="true">
                <div class="hp-window-bar"><span></span><span></span><span></span><em>milestone.university.edu</em></div>
                <div class="hp-window-body">
                    <div class="hp-win-head">
                        <div><small>Software Engineering Capstone</small><strong>Team 1</strong></div>
                        <x-badge tone="success">On track</x-badge>
                    </div>
                    <div class="hp-win-milestone">
                        <div class="hp-win-milestone-top"><span class="hp-tag">M2</span><strong>Design document</strong><small>Due Oct 18 · 5:00 PM</small></div>
                        <div class="hp-win-items">
                            <span class="done">SRS update</span><span class="done">Diagrams</span><span class="done">Prototype link</span><span>Reflection</span>
                        </div>
                        <div class="hp-bar"><span style="width: 75%"></span></div>
                        <small class="hp-win-meta">3 of 4 deliverables ready</small>
                    </div>
                    <ul class="hp-win-tasks">
                        @foreach ([['API contract', 'AH', 'In review', 'review', 'violet'], ['Database schema', 'MM', 'In progress', 'info', 'blue'], ['Use case diagrams', 'NS', 'Done', 'success', 'green']] as [$task, $initials, $status, $tone, $face])
                            <li><b class="hp-face {{ $face }}">{{ $initials }}</b><span class="hp-win-task">{{ $task }}</span><x-badge :tone="$tone">{{ $status }}</x-badge></li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="hp-receipt" aria-hidden="true">
                <span class="hp-receipt-icon">@include('public.partials.icon', ['name' => 'check', 'size' => 16])</span>
                <div><strong>M1 proposal · version 3</strong><small>Submitted by Aya Hassan · Sep 19, 4:52 PM</small></div>
            </div>
        </div>
    </section>

    <section class="hp-facts" aria-label="Milestone at a glance">
        <dl>
            @foreach ($content['facts'] as $fact)
                <div><dt>{{ $fact['value'] }}</dt><dd>{{ $fact['label'] }}</dd></div>
            @endforeach
        </dl>
    </section>

    <section class="hp-section" id="how" aria-labelledby="hp-how-title">
        <x-section-heading eyebrow="How it works" title="Structure the project once. Keep every team aligned." title-id="hp-how-title" />
        <ol class="hp-steps">
            @foreach ($content['steps'] as $step)
                <li>
                    <span class="hp-step-num" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <h3>{{ $step['title'] }}</h3>
                    <p>{{ $step['text'] }}</p>
                    <small>{{ $step['detail'] }}</small>
                </li>
            @endforeach
        </ol>
    </section>

    <section class="hp-section" id="types" aria-labelledby="hp-types-title">
        <x-section-heading class="hp-heading--split" eyebrow="Project types" title="Coursework or side project, same workspace." title-id="hp-types-title"
            description="Academic projects follow the course rules. Independent projects give students the same board, files and calendar for anything else they build together." />

        {{-- Tabs only show below 900px; on wider screens both columns are visible. --}}
        <div class="hp-type-switch" role="tablist" aria-label="Project type" data-hp-type-tabs>
            @foreach ($content['project_types'] as $key => $type)
                <button type="button" role="tab" id="hp-tab-{{ $key }}" aria-controls="hp-type-{{ $key }}"
                    aria-selected="{{ $loop->first ? 'true' : 'false' }}" tabindex="{{ $loop->first ? '0' : '-1' }}">{{ $type['name'] }}</button>
            @endforeach
        </div>
        <div class="hp-type-grid">
            @foreach ($content['project_types'] as $key => $type)
                <article @class(['hp-type', $key, 'active' => $loop->first]) id="hp-type-{{ $key }}" role="tabpanel" aria-labelledby="hp-type-title-{{ $key }}">
                    <header>
                        <span class="hp-type-badge">{{ $type['badge'] }}</span>
                        <h3 id="hp-type-title-{{ $key }}">{{ $type['name'] }}</h3>
                        <p>{{ $type['tagline'] }}</p>
                    </header>
                    <dl>
                        @foreach ($type['rows'] as $label => $value)
                            <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                        @endforeach
                    </dl>
                </article>
            @endforeach
        </div>
    </section>

    <section class="hp-section" id="roles" aria-labelledby="hp-roles-title">
        <x-section-heading eyebrow="Roles" title="Clear permissions for everyone involved." title-id="hp-roles-title" />
        <div class="hp-roles">
            @foreach ($content['roles'] as $role)
                <article class="hp-role {{ $role['tone'] }}">
                    <span class="hp-role-icon">@include('public.partials.icon', ['name' => $role['icon']])</span>
                    <h3>{{ $role['name'] }}</h3>
                    <ul>
                        @foreach ($role['points'] as $point)
                            <li>{{ $point }}</li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </div>
    </section>

    <section class="hp-signal" aria-labelledby="hp-signal-title">
        <div class="hp-signal-copy">
            <p class="hp-eyebrow light">Evidence, not guesswork</p>
            <h2 id="hp-signal-title">Know which team needs attention before a deadline slips.</h2>
            <p>Milestone combines task progress, activity, submissions and contribution evidence into signals that explain themselves. They inform the professor, they never decide a grade.</p>
            <ul>
                <li>Every risk level comes with its reason</li>
                <li>Contribution summaries are evidence, not scores</li>
                <li>Deadline, inactivity and workload signals in one place</li>
            </ul>
        </div>
        <div class="hp-signal-card" role="img" aria-label="Example risk signal: Team 3 may miss M2 because four tasks are open and activity slowed for three days">
            <div aria-hidden="true">
                <div class="hp-signal-top"><x-badge tone="warning">Medium risk</x-badge><small>Simulated</small></div>
                <strong>Team 3 may miss M2</strong>
                <p>Four tasks are still open and activity has slowed for three days.</p>
                <div class="hp-signal-bars">
                    <div><span>Tasks done</span><i><b style="width: 42%"></b></i><em>42%</em></div>
                    <div><span>Activity, 7 days</span><i><b class="violet" style="width: 28%"></b></i><em>Low</em></div>
                    <div><span>Time left</span><i><b class="coral" style="width: 18%"></b></i><em>3 days</em></div>
                </div>
                <p class="hp-signal-foot">Suggested: confirm owners for the remaining tasks today.</p>
            </div>
        </div>
    </section>

    <section class="hp-section" id="faq" aria-labelledby="hp-faq-title">
        <x-section-heading class="hp-heading--split" eyebrow="FAQ" title="Questions people ask first." title-id="hp-faq-title">
            <p class="ms-section-heading__description">Can’t find your answer? The <a href="{{ route('help') }}">Help center</a> has more, plus a form to report a problem.</p>
        </x-section-heading>
        <div class="hp-faq-list" data-hp-faq>
            @foreach ($content['faqs'] as $i => [$question, $answer])
                <div @class(['hp-faq-item', 'open' => $loop->first]) data-hp-faq-item>
                    <h3>
                        <button type="button" id="hp-faq-q-{{ $i }}" aria-controls="hp-faq-{{ $i }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                            <span>{{ $question }}</span><i aria-hidden="true"></i>
                        </button>
                    </h3>
                    <div class="hp-faq-answer" id="hp-faq-{{ $i }}" role="region" aria-labelledby="hp-faq-q-{{ $i }}" @unless ($loop->first) hidden @endunless>
                        <p>{{ $answer }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="hp-cta" aria-labelledby="hp-cta-title">
        <h2 id="hp-cta-title">Bring your next group project into one place.</h2>
        <p>Use your university email. Professors and TAs are verified before their role is active.</p>
        <div>
            @auth
                <x-button size="large" :href="$accountLinks['dashboard']">Go to dashboard</x-button>
            @else
                <x-button size="large" :href="$accountLinks['register']">Create an account</x-button>
                <x-button variant="secondary" size="large" :href="$accountLinks['login']">Sign in</x-button>
            @endauth
        </div>
    </section>
@endsection
