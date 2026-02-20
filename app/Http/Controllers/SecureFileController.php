<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Document;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
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
        Gate::forUser($user)->authorize('viewSensitiveFiles', $application);
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
