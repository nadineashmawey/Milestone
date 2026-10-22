@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-class', 'page dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ route('admin.assets.css', ['path' => 'pages/admin-dashboard.css']) }}">
@endpush

@section('content')
<x-admin.page-header eyebrow="System administration" title="University workspace" description="Manage people, academic structure, and retained project records.">
                    <a class="btn" href="{{ route('admin.imports') }}"
                        ><svg viewBox="0 0 24 24">
                            <path
                                d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4"
                            /></svg
                        >Import users</a
                    >
</x-admin.page-header>
                <section class="metrics" aria-label="University overview">
                    <article class="card metric">
                        <span class="metric-icon"
                            ><svg viewBox="0 0 24 24">
                                <path
                                    d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"
                                /></svg
                        ></span>
                        <div>
                            <p>Active accounts</p>
                            <strong>486</strong>
                            <p>12 pending verification</p>
                        </div>
                    </article>
                    <article class="card metric">
                        <span class="metric-icon violet"
                            ><svg viewBox="0 0 24 24">
                                <path
                                    d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"
                                /></svg
                        ></span>
                        <div>
                            <p>Courses</p>
                            <strong>18</strong>
                            <p>Fall 2026</p>
                        </div>
                    </article>
                    <article class="card metric">
                        <span class="metric-icon green"
                            ><svg viewBox="0 0 24 24">
                                <rect x="3" y="3" width="7" height="7" />
                                <rect x="14" y="3" width="7" height="7" />
                                <rect x="3" y="14" width="7" height="7" />
                                <rect x="14" y="14" width="7" height="7" /></svg
                        ></span>
                        <div>
                            <p>Active projects</p>
                            <strong>31</strong>
                            <p>Across 6 departments</p>
                        </div>
                    </article>
                    <article class="card metric">
                        <span class="metric-icon yellow"
                            ><svg viewBox="0 0 24 24">
                                <path
                                    d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4"
                                /></svg
                        ></span>
                        <div>
                            <p>Needs review</p>
                            <strong>5</strong>
                            <p>Role, data, and duplicate issues</p>
                        </div>
                    </article>
                </section>
                <section class="main-grid">
                    <article class="card panel">
                        <header>
                            <h2>Administration queue</h2>
                            <p>Items that need an administrator</p>
                        </header>
                        <ul class="queue-list">
                            <li>
                                <a href="/admin/imports"
                                    ><i class="queue-marker"></i
                                    ><span class="queue-copy"
                                        ><strong>Assign professor role</strong
                                        ><span
                                            >Dr. Lina Haddad · Verified
                                            faculty</span
                                        ></span
                                    ><svg viewBox="0 0 24 24">
                                        <path d="m9 18 6-6-6-6" /></svg
                                ></a>
                            </li>
                            <li>
                                <a href="/admin/projects?transfer=1"
                                    ><i class="queue-marker"></i
                                    ><span class="queue-copy"
                                        ><strong
                                            >Resolve 3 duplicate CSV
                                            rows</strong
                                        ><span
                                            >students-fall-2026.csv</span
                                        ></span
                                    ><svg viewBox="0 0 24 24">
                                        <path d="m9 18 6-6-6-6" /></svg
                                ></a>
                            </li>
                            <li>
                                <a href="{{ route('admin.users') }}"
                                    ><i class="queue-marker"></i
                                    ><span class="queue-copy"
                                        ><strong
                                            >Transfer project ownership</strong
                                        ><span
                                            >CS 342 · Professor departure</span
                                        ></span
                                    ><svg viewBox="0 0 24 24">
                                        <path d="m9 18 6-6-6-6" /></svg
                                ></a>
                            </li>
                        </ul>
                    </article>
                    <article class="card panel">
                        <header class="semester-header">
                            <div>
                                <h2>Current semester</h2>
                                <p>Fall 2026 · Sep 1–Dec 20</p>
                            </div>
                            <span class="badge success"
                                ><i class="dot"></i>Active</span
                            >
                        </header>
                        <div class="progress-row">
                            <span>Teaching weeks</span><strong>6 of 16</strong>
                        </div>
                        <progress value="6" max="16"></progress>
                        <dl class="semester-stats">
                            <div>
                                <dt>Courses</dt>
                                <dd>18</dd>
                            </div>
                            <div>
                                <dt>Projects</dt>
                                <dd>31</dd>
                            </div>
                            <div>
                                <dt>Students</dt>
                                <dd>452</dd>
                            </div>
                        </dl>
                        <a
                            class="btn secondary"
                            href="/admin/courses"
                            data-manage-semester
                            >Manage semester</a
                        >
                    </article>
                </section>
                <section class="card panel">
                    <header class="activity-header">
                        <div>
                            <h2>Recent administrative activity</h2>
                            <p>Account and academic structure changes</p>
                        </div>
                        <a href="{{ route('admin.audit') }}">View audit history</a>
                    </header>
                    <ul class="activity-list">
                        <li>
                            <span class="avatar">SA</span>
                            <div>
                                <strong
                                    >University Admin assigned TA role to Sara
                                    Ahmed</strong
                                ><time>12 minutes ago</time>
                            </div>
                        </li>
                        <li>
                            <span class="avatar">UA</span>
                            <div>
                                <strong
                                    >Fall 2026 CS 490 roster imported · 24
                                    students</strong
                                ><time>Yesterday</time>
                            </div>
                        </li>
                        <li>
                            <span class="avatar">UA</span>
                            <div>
                                <strong
                                    >Nadia Karim’s account was deactivated ·
                                    history retained</strong
                                ><time>Oct 5</time>
                            </div>
                        </li>
                    </ul>
                </section>
@endsection
