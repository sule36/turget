<?php

namespace App\Services;

use App\Enums\ContentStatus;
use App\Models\AuditLog;
use App\Models\ContentRevision;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContentWorkflowService
{
    /**
     * Transition a publishable model through workflow states.
     *
     * @param Model $model Country|AdministrativeRegion|Destination|Article
     */
    public function transition(Model $model, string $action, ?User $user = null, ?string $note = null): Model
    {
        $user ??= auth()->user();
        $rules = ContentStatus::actions();

        if (! isset($rules[$action])) {
            throw ValidationException::withMessages([
                'action' => "Aksi workflow '{$action}' tidak dikenal.",
            ]);
        }

        $rule = $rules[$action];
        $currentStatus = $model->status instanceof ContentStatus ? $model->status : ContentStatus::from($model->status);

        if (! in_array($currentStatus, $rule['from'], true)) {
            $allowedFrom = implode(', ', array_map(fn ($s) => $s->value, $rule['from']));
            throw ValidationException::withMessages([
                'status' => "Aksi '{$action}' hanya dapat dilakukan dari status [{$allowedFrom}]. Status saat ini: {$currentStatus->value}.",
            ]);
        }

        // Check permission if user has roles
        if ($user && method_exists($user, 'can') && ! $user->hasRole('super_admin')) {
            $permission = $rule['permission'];
            if (! $user->can($permission)) {
                throw ValidationException::withMessages([
                    'authorization' => "Anda tidak memiliki izin '{$permission}' untuk menjalankan aksi ini.",
                ]);
            }
        }

        $targetStatus = $rule['to'];

        return DB::transaction(function () use ($model, $action, $currentStatus, $targetStatus, $user, $note) {
            $model->status = $targetStatus;
            $model->updated_by = $user?->id;

            if ($targetStatus === ContentStatus::Published && ! $model->published_at) {
                $model->published_at = now();
            }

            $model->save();

            // Record revision snapshot
            ContentRevision::create([
                'revisionable_type' => get_class($model),
                'revisionable_id' => $model->getKey(),
                'user_id' => $user?->id,
                'action' => $action,
                'from_status' => $currentStatus->value,
                'to_status' => $targetStatus->value,
                'note' => $note,
                'changes' => $model->getChanges(),
                'snapshot' => $model->toArray(),
                'created_at' => now(),
            ]);

            // Audit log
            AuditLog::record("content.workflow.{$action}", $model, [
                'from_status' => $currentStatus->value,
                'to_status' => $targetStatus->value,
                'note' => $note,
            ]);

            return $model;
        });
    }
}
