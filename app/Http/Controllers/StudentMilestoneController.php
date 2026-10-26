<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiscardMilestoneDraftRequest;
use App\Http\Requests\StoreMilestoneDraftRequest;
use App\Http\Requests\SubmitMilestoneRequest;
use App\Support\ZinaDemoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class StudentMilestoneController extends Controller
{
    public function index(Request $request, ZinaDemoData $demo, string $project): View
    {
        $timeline = $demo->milestones($project);
        abort_if($timeline === null, 404);

        $state = $demo->state($request);

        return view('zina.milestones.index', [
            'state' => $state,
            'timeline' => $state === 'empty' ? [...$timeline, 'milestones' => []] : $timeline,
            'context' => $demo->context(),
            'extensions' => ZinaDemoData::DELIVERABLE_EXTENSIONS,
            'simulateFailures' => $demo->statesEnabled(),
        ]);
    }

    /**
     * Step 1: validate the deliverables and keep them as a draft. Nothing is submitted yet.
     */
    public function draft(StoreMilestoneDraftRequest $request, ZinaDemoData $demo, string $project, string $milestone): RedirectResponse
    {
        $deliverables = $demo->m2Deliverables();

        $demo->saveMilestoneDraft($project, [
            'files' => collect(['design_document', 'presentation'])->map(function (string $field) use ($request, $demo, $deliverables) {
                /** @var UploadedFile $file */
                $file = $request->file($field);

                return ['label' => $deliverables[$field]['label'], 'name' => $file->getClientOriginalName(), 'size' => $demo->fileSize($file->getSize())];
            })->all(),
            'link' => $request->validated('repository'),
        ]);

        return redirect()->to(route('student.milestones.index', $project).'#confirm');
    }

    public function discard(DiscardMilestoneDraftRequest $request, ZinaDemoData $demo, string $project, string $milestone): RedirectResponse
    {
        $demo->discardMilestoneDraft($project);

        return redirect()->to(route('student.milestones.index', $project).'#submit');
    }

    /**
     * Step 2: the confirmed submission. A version is only shown as received once it's stored (SUB-2, NFR-5).
     */
    public function submit(SubmitMilestoneRequest $request, ZinaDemoData $demo, string $project, string $milestone): RedirectResponse
    {
        $back = route('student.milestones.index', $project);

        if ($demo->statesEnabled() && $request->boolean('simulate_failure')) {
            Log::warning('Prototype: simulated storage failure for a milestone submission.', ['project' => $project]);

            return redirect()->to($back.'#confirm')->with('submission_failed', true);
        }

        $version = $demo->submitMilestoneDraft($project);
        if ($version === null) {
            return redirect()->to($back.'#submit')->with('submission_missing', true);
        }

        // TODO: notify Professor Lee and Sara Ahmed (SUB-5, SUB-11) once notifications exist. Email stays simulated.
        return redirect()->to($back.'#receipt')->with('submission_receipt', $version);
    }
}
