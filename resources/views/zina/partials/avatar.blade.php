{{-- Initials avatar. Decorative: the person's name is always shown next to it. --}}
<span class="as-avatar as-avatar--{{ ($index ?? 0) % 5 }} {{ $class ?? '' }}" aria-hidden="true">{{ app(\App\Support\ZinaDemoData::class)->initials($name) }}</span>
