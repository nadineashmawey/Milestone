@extends('layouts.public', ['full' => false])

@section('title', 'Help center')
@section('description', 'Answers about Milestone accounts, teams, milestones and grades, and a form to report a problem.')

@section('content')
    <section class="hp-help-hero" aria-labelledby="hp-help-title">
        <p class="hp-eyebrow">Help center</p>
        <h1 id="hp-help-title">How can we help?</h1>
        <form class="hp-search" role="search" method="GET" action="{{ route('help') }}" data-hp-search-form>
            @include('public.partials.icon', ['name' => 'search'])
            <label for="hp-help-search" class="hp-sr">Search help articles</label>
            <input id="hp-help-search" type="search" name="q" value="{{ $query }}" autocomplete="off"
                placeholder="Search, for example “leave team” or “resubmit”">
            <x-button type="submit" variant="secondary" size="small" class="hp-search-submit">Search</x-button>
        </form>
        <p class="hp-sr" aria-live="polite" data-hp-search-status></p>
    </section>

    <div class="hp-help-layout">
        <section class="hp-help-topics" aria-label="Help articles">
            @foreach ($topics as $topic)
                <div class="hp-help-topic" data-hp-topic @if ($topic['matches'] === 0) hidden @endif>
                    <h2>{{ $topic['name'] }}</h2>
                    @foreach ($topic['articles'] as $article)
                        <details data-hp-article @unless ($article['match']) hidden @endunless>
                            <summary>{{ $article['question'] }}</summary>
                            <p>{{ $article['answer'] }}</p>
                        </details>
                    @endforeach
                </div>
            @endforeach

            <x-empty-state :title="'No articles match “'.$query.'”.'" :hidden="$resultCount > 0" data-hp-search-empty>
                <p>Try a shorter word, or describe the problem in the form and we’ll reply by email.</p>
                <x-slot:action>
                    <x-button variant="secondary" :href="route('help')" data-hp-search-clear>Clear search</x-button>
                </x-slot:action>
            </x-empty-state>
        </section>

        <aside class="hp-report" id="report" aria-labelledby="hp-report-title">
            <span class="hp-report-icon">@include('public.partials.icon', ['name' => 'message'])</span>
            <h2 id="hp-report-title">Report a problem</h2>
            <p class="hp-report-intro">Tell us what went wrong. Don’t include passwords or grades.</p>

            @if ($sent = session('report_sent'))
                <x-alert tone="success" title="Report sent">
                    <p>Thanks, {{ $sent['first_name'] }}. We’ll reply to <strong>{{ $sent['email'] }}</strong>. Your reference is <strong>{{ $sent['reference'] }}</strong>.</p>
                </x-alert>
            @endif

            @if (session('report_failed'))
                <x-alert tone="error" title="We couldn’t send your report.">
                    <p>Your details are still here. Try again in a moment.</p>
                </x-alert>
            @elseif ($errors->any())
                <x-alert tone="error" title="Check the highlighted fields.">
                    <ul>
                        @foreach ($errors->keys() as $field)
                            <li><a href="#report-{{ $field }}">{{ $errors->first($field) }}</a></li>
                        @endforeach
                    </ul>
                </x-alert>
            @endif

            @php($firstError = $errors->keys()[0] ?? null)
            <form class="hp-report-form" method="POST" action="{{ route('help.reports.store') }}" novalidate data-hp-report-form>
                @csrf
                <x-input id="report-name" name="name" label="Name" autocomplete="name" maxlength="100" required
                    :value="old('name', $prefill['name'])" :autofocus="$firstError === 'name'" />
                <x-input id="report-email" name="email" label="Email" type="email" autocomplete="email" maxlength="150" required
                    :value="old('email', $prefill['email'])" :autofocus="$firstError === 'email'" />
                <x-select id="report-category" name="category" label="What is it about?" placeholder="Choose one" required
                    :options="$categories" :selected="old('category')" :autofocus="$firstError === 'category'" />
                <x-textarea id="report-description" name="description" label="What happened?" rows="5" maxlength="2000" required
                    placeholder="What were you trying to do, and what did you see instead?" help="At least 20 characters."
                    :value="old('description')" :autofocus="$firstError === 'description'" />
                <x-button type="submit" size="large" class="hp-report-submit" data-loading-text="Sending…">Send report</x-button>
            </form>
        </aside>
    </div>
@endsection
