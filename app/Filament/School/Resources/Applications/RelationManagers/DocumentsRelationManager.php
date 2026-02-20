<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Applications\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Storage;
use Filament\Schemas\Schema;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Uploaded Documents';

    protected static string|null|\BackedEnum $icon = 'heroicon-o-document-text';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Document Information')
                    ->schema([
                        Forms\Components\Select::make('document_type')
                            ->label('Document Type')
                            ->options([
                                'birth_certificate' => 'Birth Certificate',
                                'passport' => 'Passport',
                                'family_card' => 'Family Card (Kartu Keluarga)',
                                'student_photo' => 'Student Photo (3x4)',
                                'previous_report_card' => 'Previous Report Card',
                                'health_certificate' => 'Health Certificate',
                                'immunization_record' => 'Immunization Record',
                                'recommendation_letter' => 'Recommendation Letter',
                                'proof_of_address' => 'Proof of Address',
                                'parent_id' => 'Parent ID Card',
                                'payment_proof' => 'Payment Proof',
                                'transcript' => 'Academic Transcript',
                                'certificate' => 'Certificate',
                                'other' => 'Other Document',
                            ])
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->columnSpanFull(),

                        FileUpload::make('file_path')
                            ->label('Upload Document')
                            ->required()
                            ->disk('local')
                            ->directory('documents')
                            ->visibility('private')
                            ->maxSize(5120) // 5MB
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/jpeg',
                                'image/jpg',
                                'image/png',
                            ])
                            ->helperText('Maximum file size: 5MB. Allowed types: PDF, JPG, PNG')
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Verification')
                    ->schema([
                        Select::make('verification_status')
                            ->label('Verification Status')
                            ->options([
                                'pending' => 'Pending',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false)
                            ->live()
                            ->columnSpan(1),

                        DateTimePicker::make('verified_at')
                            ->label('Verified At')
                            ->native(false)
                            ->disabled()
                            ->visible(fn (Get $get) => $get('verification_status') === 'verified')
                            ->columnSpan(1),

                        Textarea::make('verification_notes')
                            ->label('Verification Notes')
                            ->rows(2)
                            ->visible(fn (Get $get) => in_array($get('verification_status'), ['verified', 'rejected']))
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('document_type')
            ->columns([
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Document Type')
                    ->sortable()
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->formatStateUsing(fn (string $state): string => (string)str($state)->replace('_', ' ')->title()),

                Tables\Columns\TextColumn::make('file_name')
                    ->label('File Name')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->file_name),

                Tables\Columns\TextColumn::make('file_size')
                    ->label('Size')
                    ->formatStateUsing(fn ($state) => number_format($state / 1024, 2) . ' KB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('verification_status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('verifiedBy.name')
                    ->label('Verified By')
                    ->placeholder('Not verified')
                    ->limit(25),

                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Verified At')
                    ->dateTime('d M Y H:i')
                    ->placeholder('N/A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('document_type')
                    ->options([
                        'birth_certificate' => 'Birth Certificate',
                        'passport' => 'Passport',
                        'family_card' => 'Family Card',
                        'student_photo' => 'Student Photo',
                        'previous_report_card' => 'Report Card',
                        'health_certificate' => 'Health Certificate',
                        'immunization_record' => 'Immunization Record',
                        'recommendation_letter' => 'Recommendation Letter',
                        'proof_of_address' => 'Proof of Address',
                        'parent_id' => 'Parent ID',
                        'payment_proof' => 'Payment Proof',
                        'transcript' => 'Transcript',
                        'certificate' => 'Certificate',
                        'other' => 'Other',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('verification_status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ])
                    ->multiple(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->mutateDataUsing(function (array $data): array {
                        // Auto-set file metadata
                        if (isset($data['file_path'])) {
                            $filePath = $data['file_path'];
                            $data['file_name'] = basename($filePath);
                            if (Storage::disk('local')->exists($filePath)) {
                                $data['file_size'] = Storage::disk('local')->size($filePath);
                                $data['mime_type'] = Storage::disk('local')->mimeType($filePath);
                            } elseif (Storage::disk('public')->exists($filePath)) {
                                $data['file_size'] = Storage::disk('public')->size($filePath);
                                $data['mime_type'] = Storage::disk('public')->mimeType($filePath);
                            }
                        }
                        return $data;
                    }),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn ($record) => route('secure-files.documents.download', ['document' => $record->id]))
                    ->openUrlInNewTab(),

                Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Textarea::make('verification_notes')
                            ->label('Verification Notes')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'verification_status' => 'verified',
                            'verification_notes' => $data['verification_notes'],
                            'verified_at' => now(),
                            'verified_by' => auth()->id(),
                        ]);
                    })
                    ->successNotificationTitle('Document verified successfully')
                    ->visible(fn ($record) => $record->verification_status === 'pending'),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Textarea::make('verification_notes')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'verification_status' => 'rejected',
                            'verification_notes' => $data['verification_notes'],
                            'verified_at' => now(),
                            'verified_by' => auth()->id(),
                        ]);
                    })
                    ->successNotificationTitle('Document rejected')
                    ->visible(fn ($record) => $record->verification_status === 'pending'),

                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('verifyBulk')
                        ->label('Verify Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records): void {
                            $records->each(function ($record) {
                                if ($record->verification_status === 'pending') {
                                    $record->update([
                                        'verification_status' => 'verified',
                                        'verified_at' => now(),
                                        'verified_by' => auth()->id(),
                                    ]);
                                }
                            });
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Documents verified successfully'),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No documents uploaded yet')
            ->emptyStateDescription('Upload required documents for this application.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
