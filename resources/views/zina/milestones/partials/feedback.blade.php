{{-- TA and professor feedback, visible to the whole team (SUB-7, SUB-11). --}}
<h3 class="ml-subhead">Feedback for the team</h3>
<ul class="ml-feedback">
    @foreach ($feedback as $note)
        <li @class(['is-professor' => $note['role'] === 'Professor'])>
            @include('zina.partials.avatar', ['name' => $note['author'], 'index' => $note['role'] === 'Professor' ? 0 : 2])
            <div>
                <p class="ml-feedback-by"><strong>{{ $note['author'] }}</strong> <span>{{ $note['role'] }} · {{ $note['when'] }}</span></p>
                <blockquote>{{ $note['text'] }}</blockquote>
            </div>
        </li>
    @endforeach
</ul>
