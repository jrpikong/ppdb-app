<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Document;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SecureFileController extends Controller
{
    public function document(Document $document): BinaryFileResponse
    {
        $application = $document->application()->firstOrFail();
        $this->authorizeApplicationAccess($application, auth()->user());

        return $this->streamFile($document->file_path, $document->name ?: basename((string) $document->file_path));
    }

    public function paymentProof(Payment $payment): BinaryFileResponse
    {
        $application = $payment->application()->firstOrFail();
        $this->authorizeApplicationAccess($application, auth()->user());

        abort_if(blank($payment->proof_file), 404, 'Proof file not found.');

        return $this->streamFile($payment->proof_file, basename((string) $payment->proof_file));
    }

    private function authorizeApplicationAccess(Application $application, ?User $user): void
    {
        abort_if(! $user, 403);
        app(PermissionRegistrar::class)->setPermissionsTeamId((int) ($user->school_id ?: 0));

        if ($user->hasRole('super_admin')) {
            return;
        }

        if ($user->hasRole('parent') && $application->user_id === $user->id) {
            return;
        }

        if (
            $user->hasAnyRole(['school_admin', 'admission_admin', 'finance_admin'])
            && (int) $user->school_id === (int) $application->school_id
        ) {
            return;
        }

        abort(403);
    }

    private function streamFile(string $path, string $downloadName): BinaryFileResponse
    {
        $disk = $this->resolveDisk($path);
        abort_if($disk === null, 404, 'File not found.');

        $absolutePath = Storage::disk($disk)->path($path);
        $mimeType = Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream';

        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.$downloadName.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function resolveDisk(string $path): ?string
    {
        if (Storage::disk('local')->exists($path)) {
            return 'local';
        }

        if (Storage::disk('public')->exists($path)) {
            return 'public';
        }

        return null;
    }
}
