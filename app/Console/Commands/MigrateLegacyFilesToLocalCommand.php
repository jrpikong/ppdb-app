<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class MigrateLegacyFilesToLocalCommand extends Command
{
    protected $signature = 'app:files:migrate-public-to-local
        {--dry-run : Simulate migration without writing or deleting files}
        {--chunk=200 : Chunk size for batch processing}';

    protected $description = 'Migrate legacy sensitive files from public disk to local disk (documents and payment proofs).';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunkSize = max((int) $this->option('chunk'), 1);

        $this->info('Starting legacy file migration: public -> local');
        $this->line('Mode: ' . ($dryRun ? 'DRY RUN' : 'EXECUTE'));
        $this->newLine();

        $stats = [
            'scanned' => 0,
            'migrated' => 0,
            'already_local' => 0,
            'missing_public' => 0,
            'errors' => 0,
        ];

        $this->migrateModel(
            query: Document::query()->whereNotNull('file_path'),
            pathField: 'file_path',
            chunkSize: $chunkSize,
            dryRun: $dryRun,
            stats: $stats,
            label: 'documents',
        );

        $this->migrateModel(
            query: Payment::query()->whereNotNull('proof_file'),
            pathField: 'proof_file',
            chunkSize: $chunkSize,
            dryRun: $dryRun,
            stats: $stats,
            label: 'payments',
        );

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Scanned', (string) $stats['scanned']],
                ['Migrated', (string) $stats['migrated']],
                ['Already in local', (string) $stats['already_local']],
                ['Missing in public', (string) $stats['missing_public']],
                ['Errors', (string) $stats['errors']],
            ]
        );

        if ($dryRun) {
            $this->warn('Dry run finished. No files were changed.');
        } else {
            $this->info('Migration finished.');
        }

        return $stats['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @param array{scanned:int,migrated:int,already_local:int,missing_public:int,errors:int} $stats
     */
    private function migrateModel(
        Builder $query,
        string $pathField,
        int $chunkSize,
        bool $dryRun,
        array &$stats,
        string $label
    ): void {
        $this->info(sprintf('Processing %s...', $label));

        $query
            ->orderBy('id')
            ->chunkById($chunkSize, function ($records) use ($pathField, $dryRun, &$stats): void {
                foreach ($records as $record) {
                    $path = (string) ($record->{$pathField} ?? '');

                    if ($path === '') {
                        continue;
                    }

                    $stats['scanned']++;

                    if (Storage::disk('local')->exists($path)) {
                        $stats['already_local']++;
                        continue;
                    }

                    if (! Storage::disk('public')->exists($path)) {
                        $stats['missing_public']++;
                        continue;
                    }

                    if ($dryRun) {
                        $stats['migrated']++;
                        continue;
                    }

                    try {
                        $readStream = Storage::disk('public')->readStream($path);
                        if ($readStream === false) {
                            $stats['errors']++;
                            $this->error("Failed reading stream: {$path}");
                            continue;
                        }

                        $writeOk = Storage::disk('local')->writeStream($path, $readStream);

                        if (is_resource($readStream)) {
                            fclose($readStream);
                        }

                        if ($writeOk !== true) {
                            $stats['errors']++;
                            $this->error("Failed writing stream: {$path}");
                            continue;
                        }

                        Storage::disk('public')->delete($path);
                        $stats['migrated']++;
                    } catch (\Throwable $e) {
                        $stats['errors']++;
                        $this->error("Error migrating {$path}: {$e->getMessage()}");
                    }
                }
            });
    }
}
