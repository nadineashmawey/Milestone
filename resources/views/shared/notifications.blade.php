@extends('layouts.app')

@section('title', 'Notifications')
@section('workspace-name', 'Notifications')

@section('navigation')
    <a href="{{ url('/') }}">Dashboard</a>
    <span class="ms-navigation-placeholder">Reviews <b>5</b></span>
    <span class="ms-navigation-placeholder">Issues</span>
    <span class="ms-navigation-placeholder">Invitations <b>1</b></span>
@endsection

@push('styles')
    @vite('resources/css/pages/notifications.css')
@endpush

@section('content')
    <div class="notifications-page" data-notifications-app>
        <div class="notifications-heading">
            <div>
                <p class="notifications-eyebrow">Your updates</p>
                <x-page-header title="Notifications" description="Personal alerts are separate from each project's activity feed." />
            </div>
            <div class="notifications-demo-role">
                <label for="notification-role">Preview role</label>
                <select id="notification-role" aria-describedby="role-demo-note">
                    <option value="student">Student</option>
                    <option value="ta">Teaching Assistant</option>
                    <option value="professor">Professor</option>
                    <option value="admin">Admin</option>
                </select>
                <small id="role-demo-note">Demo selector only · does not change your account.</small>
            </div>
        </div>

        <div class="notifications-channels" role="group" aria-label="Notification channel">
            <button type="button" data-channel="in-app" aria-pressed="true">In-app</button>
            <button type="button" data-channel="email" aria-pressed="false">Email</button>
        </div>

        <x-card class="notifications-panel" aria-labelledby="notification-list-title">
            <div class="notifications-panel__header">
                <div>
                    <h2 id="notification-list-title">Recent notifications</h2>
                    <p class="notifications-unread-count" data-unread-count aria-live="polite">0 unread</p>
                </div>
                <x-button type="button" variant="secondary" data-mark-all>Mark all as read</x-button>
            </div>

            <div class="notifications-filters" role="group" aria-label="Filter notifications">
                <button type="button" data-filter="all" aria-pressed="true">All <span data-filter-count="all">0</span></button>
                <button type="button" data-filter="unread" aria-pressed="false">Unread <span data-filter-count="unread">0</span></button>
                <button type="button" data-filter="read" aria-pressed="false">Read <span data-filter-count="read">0</span></button>
            </div>

            <div class="notifications-list" data-notification-list aria-live="polite" aria-relevant="additions removals text"></div>
            <p class="notifications-empty" data-notification-empty hidden>No notifications in this view.</p>
        </x-card>

        <dialog class="notification-detail" data-notification-detail aria-labelledby="notification-detail-title">
            <div class="notification-detail__topline">
                <span class="notification-detail__icon" data-detail-icon aria-hidden="true"></span>
                <button type="button" class="notification-detail__close" data-close-detail aria-label="Close notification details">×</button>
            </div>
            <p class="notifications-eyebrow" data-detail-category></p>
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
