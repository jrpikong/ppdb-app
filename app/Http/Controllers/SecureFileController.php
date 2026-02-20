<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Document;
use App\Models\Payment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
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

    public function acceptanceLetter(Application $application): Response
    {
        $this->authorizeApplicationAccess($application, auth()->user());

        abort_unless(
            in_array($application->status, ['accepted', 'enrolled'], true),
            403,
            'Acceptance letter is only available for accepted or enrolled applications.'
        );

        $application->loadMissing(['school', 'level', 'academicYear', 'user']);

        $school = $application->school;
        $level = $application->level;
        $academicYear = $application->academicYear;

        // Encode logo as base64 (PNG primary, webp fallback)
        $logoBase64 = null;
        $pngPath = public_path('site-logo-vis-150x150.png');
        $webpPath = public_path('logo/logo.webp');

        if (file_exists($pngPath)) {
            $logoBase64 = base64_encode(file_get_contents($pngPath));
        } elseif (file_exists($webpPath)) {
            $logoBase64 = base64_encode(file_get_contents($webpPath));
        }

        // Encode principal signature as base64 if it exists
        $signatureBase64 = null;
        if ($school && filled($school->principal_signature)) {
            $disk = Storage::disk('local')->exists($school->principal_signature) ? 'local' : 'public';
            if (Storage::disk($disk)->exists($school->principal_signature)) {
                $signatureBase64 = base64_encode(Storage::disk($disk)->get($school->principal_signature));
            }
        }

        $pdf = Pdf::loadView('pdfs.acceptance-letter', compact(
            'application',
            'school',
            'level',
            'academicYear',
            'logoBase64',
            'signatureBase64'
        ))->setPaper('a4', 'portrait');

        $filename = 'acceptance-letter-' . $application->application_number . '.pdf';

        return $pdf->stream($filename);
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
