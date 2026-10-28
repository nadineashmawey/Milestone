@extends('layouts.app-shell')

@section('title', 'Notifications')

@push('styles')
    @vite('resources/css/pages/notifications.css')
@endpush

@section('content')
    @php
        // Email preferences shown on the Email tab: the same settings the Profile page saves (DLN-6).
        $profile = app(\App\Support\ZinaDemoData::class)->profile();
        $emailPreferences = collect(\App\Support\ZinaDemoData::NOTIFICATION_CATEGORIES)->map(fn ($category, $key) => [
            'label' => $category[0],
            'hint' => $category[1],
            'email' => (bool) ($profile['values']['notifications'][$key]['email'] ?? false),
        ]);
    @endphp
    <div class="as-page notifications-page" data-notifications-app>
        <header class="as-head">
            <div>
                <p class="as-eyebrow">Your updates</p>
                <h1>Notifications</h1>
                <p class="as-head-lede">Personal alerts are separate from each project's activity feed.</p>
            </div>
            {{-- The role is the navbar's one current role (Navigation::role()); this page never picks its own. --}}
            <input type="hidden" id="notification-role" value="{{ \App\Support\Navigation::role() }}">
        </header>

        <div class="notifications-tabs" role="tablist" aria-label="Notification channel">
            <button type="button" role="tab" id="notifications-tab-in-app" aria-controls="notifications-panel-in-app" aria-selected="true" data-channel="in-app">In-app</button>
            <button type="button" role="tab" id="notifications-tab-email" aria-controls="notifications-panel-email" aria-selected="false" tabindex="-1" data-channel="email">Email</button>
        </div>

        <x-card class="notifications-card">
            <div class="notifications-panel" id="notifications-panel-in-app" role="tabpanel" aria-labelledby="notifications-tab-in-app" data-channel-panel="in-app">
                <div class="notifications-panel__header">
                    <div>
                        <h2 id="notification-list-title">Recent notifications</h2>
                        <p class="notifications-unread-count" data-unread-count aria-live="polite">0 unread</p>
                    </div>
                    <x-button type="button" variant="secondary" size="small" data-mark-all>Mark all as read</x-button>
                </div>

                <div class="notifications-filters" role="group" aria-label="Filter notifications">
                    <button type="button" data-filter="all" aria-pressed="true">All <span data-filter-count="all">0</span></button>
                    <button type="button" data-filter="unread" aria-pressed="false">Unread <span data-filter-count="unread">0</span></button>
                    <button type="button" data-filter="read" aria-pressed="false">Read <span data-filter-count="read">0</span></button>
                </div>

                <div class="notifications-list" data-notification-list aria-live="polite" aria-relevant="additions removals text"></div>
                <p class="notifications-empty" data-notification-empty hidden>No notifications in this view.</p>
            </div>

            <div class="notifications-panel is-inactive" id="notifications-panel-email" role="tabpanel" aria-labelledby="notifications-tab-email" data-channel-panel="email">
                <div class="notifications-panel__header">
                    <div>
                        <h2>Email preferences</h2>
                        <p class="notifications-unread-count">Which updates also reach you by email.</p>
                    </div>
                    <x-button variant="secondary" size="small" :href="route('profile.edit').'#pf-notify-title'">Change in Profile</x-button>
                </div>
                <ul class="notifications-settings" role="list">
                    @foreach ($emailPreferences as $preference)
                        <li class="notifications-setting">
                            <div>
                                <strong>{{ $preference['label'] }}</strong>
                                <span>{{ $preference['hint'] }}</span>
                            </div>
                            <x-badge :tone="$preference['email'] ? 'success' : 'neutral'">{{ $preference['email'] ? 'Email on' : 'Email off' }}</x-badge>
                        </li>
                    @endforeach
                </ul>
            </div>
        </x-card>

        <dialog class="notification-detail" data-notification-detail aria-labelledby="notification-detail-title">
            <div class="notification-detail__topline">
                <span class="notification-detail__icon" data-detail-icon aria-hidden="true"></span>
                <button type="button" class="notification-detail__close" data-close-detail aria-label="Close notification details">×</button>
            </div>
            <p class="as-eyebrow" data-detail-category></p>
            <h2 id="notification-detail-title" data-detail-title></h2>
            <p class="notification-detail__description" data-detail-description></p>
            <p class="notification-detail__time" data-detail-time></p>
            <div class="notification-detail__actions">
                <x-button type="button" variant="secondary" data-detail-dismiss>Dismiss</x-button>
                <x-button type="button" data-detail-mark-read>Mark as read</x-button>
            </div>
        </dialog>
        <p class="notifications-toast" data-notification-toast role="status" aria-live="polite" hidden></p>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/notifications.js')
@endpush
