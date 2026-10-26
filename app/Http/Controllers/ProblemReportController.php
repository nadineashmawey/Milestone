<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProblemReportRequest;
use App\Models\ProblemReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ProblemReportController extends Controller
{
    public function store(StoreProblemReportRequest $request): RedirectResponse
    {
        $helpReportUrl = route('help').'#report';

        try {
            $report = ProblemReport::create([
                ...$request->validated(),
                'user_id' => $request->user()?->id,
                'reference' => ProblemReport::newReference(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->to($helpReportUrl)->withInput()->with('report_failed', true);
        }

        // TODO: email the reporter a confirmation once mail is in scope. The SRS keeps email simulated in this phase.
        Log::info('Problem report received; confirmation email simulated.', [
            'reference' => $report->reference,
            'category' => $report->category,
        ]);

        return redirect()->to($helpReportUrl)->with('report_sent', [
            'reference' => $report->reference,
            'first_name' => Str::before($report->name, ' '),
            'email' => $report->email,
        ]);
    }
}
