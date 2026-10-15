<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Country;
use App\Models\Destination;
use App\Services\ContentWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WorkflowController extends Controller
{
    public function __construct(
        protected ContentWorkflowService $workflowService
    ) {}

    public function transition(Request $request, string $type, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $model = match ($type) {
            'destination' => Destination::findOrFail($id),
            'article' => Article::findOrFail($id),
            'country' => Country::findOrFail($id),
            default => abort(404, 'Tipe konten tidak didukung untuk workflow.'),
        };

        try {
            $this->workflowService->transition(
                $model,
                $validated['action'],
                $request->user(),
                $validated['note'] ?? null
            );

            $actionLabels = [
                'submit' => 'diajukan untuk review editorial.',
                'approve' => 'telah disetujui.',
                'request_changes' => 'dikembalikan untuk revisi.',
                'publish' => 'berhasil diterbitkan ke publik.',
                'unpublish' => 'ditarik kembali ke status draf.',
                'archive' => 'berhasil diarsipkan.',
            ];

            $msg = 'Konten '.($actionLabels[$validated['action']] ?? 'berhasil diperbarui.');

            return back()->with('success', $msg);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }
}
