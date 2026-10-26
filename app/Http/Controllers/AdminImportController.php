<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminImportController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.imports', ['preview' => $request->session()->get('import_preview'), 'result' => $request->session()->get('import_result')]);
    }

    public function preview(Request $request): RedirectResponse
    {
        $request->validate([
            'account_type' => ['required', 'in:Students,Professors,Teaching Assistants'],
            'course' => ['required', 'string', 'max:120'],
            'roster' => ['required', 'file', 'max:5120', function (string $attribute, mixed $file, \Closure $fail): void {
                if (!in_array(strtolower($file->getClientOriginalExtension()), ['csv', 'txt'], true)) $fail('Please choose a .csv file.');
            }],
        ]);
        $uploaded = $request->file('roster');
        if (!$uploaded || !$uploaded->isValid()) return back()->withErrors(['roster' => 'The CSV upload did not complete. Please choose the file again.']);
        $path = $uploaded->store('imports', 'local');
        if (!$path) return back()->withErrors(['roster' => 'The CSV could not be saved. Check server storage permissions and try again.']);
        $handle = fopen(Storage::disk('local')->path($path), 'rb');
        if (!$handle) {
            Storage::disk('local')->delete($path);
            return back()->withErrors(['roster' => 'The uploaded CSV could not be read.']);
        }
        $headers = fgetcsv($handle);
        $required = ['first_name', 'last_name', 'email', 'university_id'];
        $headers = $headers ?: [];
        if (isset($headers[0])) $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
        $headerMap = array_flip(array_map(fn ($header) => strtolower(trim((string)$header)), $headers));
        if (array_diff($required, array_keys($headerMap))) {
            fclose($handle);
            Storage::disk('local')->delete($path);
            return back()->withErrors(['roster' => 'Required columns: first_name, last_name, email, university_id.']);
        }
        $previewRows = [];
        $counts = ['valid' => 0, 'duplicates' => 0, 'warnings' => 0];
        $seenEmails = [];
        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false && count($previewRows) < 100) {
            $rowNumber++;
            $value = fn ($key) => trim((string)($row[$headerMap[$key]] ?? ''));
            $name = trim($value('first_name').' '.$value('last_name'));
            $email = strtolower($value('email'));
            $domainValid = filter_var($email, FILTER_VALIDATE_EMAIL) && (str_ends_with($email, '@university.edu') || str_ends_with($email, '@gmail.com'));
            $duplicate = $email !== '' && isset($seenEmails[$email]);
            if ($email !== '') $seenEmails[$email] = true;
            $result = !$name || !$email || !$value('university_id') || !$domainValid ? 'Invalid' : ($duplicate ? 'Duplicate' : 'Valid');
            if ($result === 'Valid') $counts['valid']++; elseif ($result === 'Duplicate') $counts['duplicates']++; else $counts['warnings']++;
            $previewRows[] = ['row' => $rowNumber, 'name' => $name ?: 'Missing name', 'email' => $email ?: 'Missing email', 'result' => $result];
        }
        fclose($handle);
        $preview = ['path' => $path, 'account_type' => $request->input('account_type'), 'course' => $request->input('course'), 'rows' => $previewRows, 'counts' => $counts];
        $request->session()->put('import_preview', $preview);
        $request->session()->forget('import_result');
        return redirect()->route('admin.imports');
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['duplicate_mode' => ['required', 'in:Skip duplicates,Update existing account details']]);
        $preview = $request->session()->get('import_preview');
        abort_if(!$preview, 422, 'Upload and validate a CSV before confirming the import.');
        Storage::disk('local')->delete($preview['path']);
        $importedCount = $request->input('duplicate_mode') === 'Update existing account details'
            ? $preview['counts']['valid'] + $preview['counts']['duplicates']
            : $preview['counts']['valid'];
        $result = ['count' => $importedCount, 'duplicates' => $preview['counts']['duplicates'], 'warnings' => $preview['counts']['warnings'], 'account_type' => $preview['account_type'], 'course' => $preview['course']];
        $request->session()->put('import_result', $result);
        $request->session()->forget('import_preview');
        return redirect()->route('admin.imports');
    }
}
