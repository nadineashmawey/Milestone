@extends('layouts.app')
@section('title','Sign in')
@push('styles')
    @vite('resources/css/auth.css')
@endpush
@section('content')
<div class="auth-layout"><section class="auth-intro"><span class="eyebrow">UNIVERSITY PROJECT MANAGEMENT</span><h1>Group projects that don’t fall apart.</h1><p>One focused workspace for academic supervision and student-led independent projects.</p><ul><li>One clear source for tasks, files, and deadlines</li><li>Review workflows with transparent permissions</li><li>Academic and independent projects together</li></ul></section>
<section class="panel auth-card"><span class="eyebrow">WELCOME BACK</span><h2>Sign in to Milestone</h2><p class="muted">Use your MIU or independent-project account to continue.</p>
@if ($errors->any())
    <div class="auth-errors" role="alert"><strong>Sign in failed</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
@if (session('status'))<p class="auth-success" role="status">{{ session('status') }}</p>@endif
<form method="POST" action="{{ route('login.submit') }}" class="auth-form">@csrf
<label>Email address<input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus></label>
<label>Password<input type="password" name="password" autocomplete="current-password" required></label>
<label class="check-inline"><input type="checkbox" name="remember" value="1"> Remember me</label>
<button class="btn primary" type="submit">Sign in</button></form>
<p class="auth-switch">Don’t have an account? <a href="{{ route('register') }}">Create account</a></p>
<div class="note"><strong>Prototype conversion note</strong><p>Use an account stored in your database. Demo-role shortcuts from the React prototype are intentionally not used for authentication.</p></div>
</section></div>
@endsection
