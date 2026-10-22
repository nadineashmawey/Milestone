@extends('layouts.app')
@section('title','Create account')
@section('content')
<div class="auth-layout"><section class="auth-intro"><span class="eyebrow">CREATE YOUR ACCOUNT</span><h1>Your next project starts here.</h1><p>Choose whether this account is connected to MIU or is for an independent project.</p><ul><li>Keep projects, tasks, and deadlines organized</li><li>Collaborate with teammates</li><li>Switch between academic and independent work</li></ul></section>
<section class="panel auth-card"><span class="eyebrow">STEP 1 OF 1</span><h2>Create your account</h2><p class="muted">MIU accounts must use a university email address.</p>
<form method="POST" action="{{ route('register.submit') }}" class="stack">@csrf
<div class="two-col"><label>First name<input name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name"></label><label>Last name<input name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name"></label></div>
<label>Email address<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></label>
<fieldset><legend>How will you use Milestone?</legend><label class="choice"><input type="radio" name="project_type" value="miu" @checked(old('project_type','miu') === 'miu')><span><strong>MIU project</strong><small>For university projects and course teams. Requires @miuegypt.edu.eg.</small></span></label><label class="choice"><input type="radio" name="project_type" value="independent" @checked(old('project_type') === 'independent')><span><strong>Independent project</strong><small>For projects outside a university course.</small></span></label></fieldset>
<label>Password (at least 8 characters)<input type="password" name="password" required autocomplete="new-password"></label><label>Confirm password<input type="password" name="password_confirmation" required autocomplete="new-password"></label>
<button class="btn primary" type="submit">Create account</button></form><p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
</section></div>
@endsection
