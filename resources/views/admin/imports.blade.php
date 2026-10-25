@extends('layouts.base')

@section('title', 'Admin Imports')

@push('styles')
    @vite('resources/css/academic-admin.css')
@endpush

@section('body')
<main class="workspace-page"><div class="workspace-container workspace-import-container">
    @if($result)
        <section class="workspace-success"><span class="workspace-success-mark">✓</span><p class="projects-eyebrow">Import complete</p><h1>{{ $result['count'] }} {{ strtolower($result['account_type']) }} are ready</h1><p>{{ $result['course'] }} · {{ $result['warnings'] }} rows need attention. Review the source file and re-import any corrected records.</p><a class="ms-button ms-button--primary" href="{{ route('admin.imports') }}">Import another CSV</a></section>
    @else
        <header class="workspace-heading"><div><p class="projects-eyebrow">Admin · Bulk import</p><h1>Import university users</h1><p>Upload, validate, review duplicates, then confirm the roster.</p></div></header>
        @if($errors->any())<p class="workspace-error" role="alert">{{ $errors->first() }}</p>@endif
        <div class="workspace-import-layout"><aside class="workspace-steps"><div class="is-active"><b>1</b><span><strong>Upload CSV</strong><small>Students, professors, or TAs</small></span></div><div class="{{ $preview ? 'is-active' : '' }}"><b>2</b><span><strong>Validate and preview</strong><small>Check required columns and rows</small></span></div><div><b>3</b><span><strong>Confirm import</strong><small>Review duplicate handling</small></span></div></aside>
            <section class="workspace-card workspace-import-card">
                @if(!$preview)
                    <form method="post" action="{{ route('admin.imports.preview') }}" enctype="multipart/form-data">@csrf
                        <div class="workspace-section-head"><div><h2>Upload CSV</h2><p>Required columns: first_name, last_name, email, university_id.</p></div></div>
                        <div class="workspace-fields"><label>Account type<select name="account_type" required><option>Students</option><option>Professors</option><option>Teaching Assistants</option></select></label><label>Semester roster<select name="course" required><option>Fall 2026 · CS 490</option><option>Fall 2026 · CS 342</option></select></label></div>
                        <label class="workspace-file"><strong>Choose a CSV file</strong><span>Maximum 5 MB · CSV format</span><input type="file" name="roster" accept=".csv,text/csv" required></label>
                        <div class="workspace-note"><strong>Invitation and verification</strong><p>New students can receive a verification invitation after import. Imported professor and TA accounts can be reviewed before assigning roles.</p></div>
                        <div class="workspace-actions"><button class="ms-button ms-button--primary" type="submit">Validate CSV</button></div>
                    </form>
                @else
                    <div class="workspace-section-head"><div><h2>Validation preview</h2><p>{{ count($preview['rows']) }} rows checked · {{ $preview['counts']['valid'] }} valid · {{ $preview['counts']['warnings'] }} warning</p></div></div>
                    <div class="workspace-summary"><span class="workspace-badge workspace-badge--success">{{ $preview['counts']['valid'] }} valid</span><span class="workspace-badge workspace-badge--warning">{{ $preview['counts']['duplicates'] }} duplicates</span><span class="workspace-badge workspace-badge--danger">{{ $preview['counts']['warnings'] }} warning</span></div>
                    <div class="workspace-import-table"><div class="workspace-import-row workspace-import-row--head"><span>Row</span><span>Name</span><span>Email</span><span>Result</span></div>@foreach($preview['rows'] as $row)<div class="workspace-import-row"><span>{{ $row['row'] }}</span><strong>{{ $row['name'] }}</strong><span>{{ $row['email'] }}</span><span class="workspace-badge workspace-badge--{{ $row['result'] === 'Valid' ? 'success' : 'danger' }}">{{ $row['result'] }}</span></div>@endforeach</div>
                    <form method="post" action="{{ route('admin.imports.confirm') }}">@csrf<label class="workspace-field">Duplicate handling<select name="duplicate_mode"><option>Skip duplicates</option><option>Update existing account details</option></select></label><div class="workspace-actions"><a class="ms-button" href="{{ route('admin.imports') }}">Start over</a><button class="ms-button ms-button--primary" type="submit">Confirm import</button></div></form>
                @endif
            </section>
        </div>
    @endif
</div></main>
@endsection
