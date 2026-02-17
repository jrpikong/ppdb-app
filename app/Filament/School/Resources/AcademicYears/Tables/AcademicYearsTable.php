<?php

namespace App\Filament\School\Resources\AcademicYears\Tables;

use App\Models\AcademicYear;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;


class AcademicYearsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tahun Ajaran')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-m-calendar')
                    ->iconColor('primary'),

                TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->date()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('end_date')
                    ->label('Tanggal Selesai')
                    ->date()
                    ->sortable()
                    ->alignCenter(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter()
                    ->sortable(),

//                TextColumn::make('total_registrations')
//                    ->label('Total Pendaftar')
//                    ->alignCenter()
//                    ->badge()
//                    ->color('success')
//                    ->icon('heroicon-m-user-group')
//                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->native(false),

                Filter::make('year_range')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('from_year')
                                    ->label('Dari Tahun')
                                    ->numeric()
                                    ->placeholder('2020'),
                                TextInput::make('to_year')
                                    ->label('Sampai Tahun')
                                    ->numeric()
                                    ->placeholder('2025'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_year'],
                                fn (Builder $query, $year): Builder => $query->where('start_year', '>=', $year),
                            )
                            ->when(
                                $data['to_year'],
                                fn (Builder $query, $year): Builder => $query->where('end_year', '<=', $year),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from_year'] ?? null) {
                            $indicators[] = 'Dari tahun: ' . $data['from_year'];
                        }
                        if ($data['to_year'] ?? null) {
                            $indicators[] = 'Sampai tahun: ' . $data['to_year'];
                        }
                        return $indicators;
                    }),

                TrashedFilter::make()
                    ->label('Data Terhapus')
                    ->native(false),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Lihat'),

                    EditAction::make()
                        ->label('Edit'),

                    Action::make('activate')
                        ->label('Aktifkan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->hidden(fn (AcademicYear $record) => $record->is_active)
                        ->requiresConfirmation()
                        ->modalHeading('Aktifkan Tahun Ajaran')
                        ->modalDescription(fn (AcademicYear $record) => "Yakin ingin mengaktifkan tahun ajaran {$record->name}? Tahun ajaran lain akan dinonaktifkan.")
                        ->modalSubmitActionLabel('Ya, Aktifkan')
                        ->action(function (AcademicYear $record) {
                            // Deactivate all other academic years
                            AcademicYear::query()->where('id', '!=', $record->id)->update(['is_active' => false]);

                            // Activate this one
                            $record->update(['is_active' => true]);

                            Notification::make()
                                ->success()
                                ->title('Tahun Ajaran Diaktifkan')
                                ->body("Tahun ajaran {$record->name} berhasil diaktifkan.")
                                ->send();
                        }),

                    Action::make('deactivate')
                        ->label('Nonaktifkan')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->visible(fn (AcademicYear $record) => $record->is_active)
                        ->requiresConfirmation()
                        ->modalHeading('Nonaktifkan Tahun Ajaran')
                        ->modalDescription(fn (AcademicYear $record) => "Yakin ingin menonaktifkan tahun ajaran {$record->name}?")
                        ->modalSubmitActionLabel('Ya, Nonaktifkan')
                        ->action(function (AcademicYear $record) {
                            $record->update(['is_active' => false]);

                            Notification::make()
                                ->warning()
                                ->title('Tahun Ajaran Dinonaktifkan')
                                ->body("Tahun ajaran {$record->name} berhasil dinonaktifkan.")
                                ->send();
                        }),

                    DeleteAction::make()
                        ->label('Hapus'),

                    ForceDeleteAction::make()
                        ->label('Hapus Permanen'),

                    RestoreAction::make()
                        ->label('Pulihkan'),
                ])->button()->label('Aksi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus yang dipilih'),

                    ForceDeleteBulkAction::make()
                        ->label('Hapus permanen yang dipilih'),

                    RestoreBulkAction::make()
                        ->label('Pulihkan yang dipilih'),

                    BulkAction::make('deactivate_selected')
                        ->label('Nonaktifkan yang dipilih')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->success()
                                ->title('Tahun Ajaran Dinonaktifkan')
                                ->body(count($records) . ' tahun ajaran berhasil dinonaktifkan.')
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('start_year', 'desc')
            ->poll('30s')
            ->striped();
    }
}
