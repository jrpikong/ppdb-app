<?php
declare(strict_types=1);
namespace App\Filament\School\Resources\Payments\Pages;

use App\Filament\School\Resources\Payments\PaymentResource;
use App\Models\Payment;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array { return []; }

    // ✅ Fix 1: Override getAllTableSummaryQuery
    public function getAllTableSummaryQuery(): Builder
    {
        return PaymentResource::getEloquentQuery();
    }

    public function getTabs(): array
    {
        return [
            'submitted' => Tab::make('Awaiting Verification')
                ->badge(fn (): int => Payment::query()
                    ->whereHas('application', fn (Builder $q) =>
                    $q->withoutGlobalScopes()
                        ->where('school_id', Filament::getTenant()?->id)
                    )
                    ->where('status', 'submitted')
                    ->count()
                )
                ->badgeColor('warning')
                // ✅ Fix 2: pakai $query bukan $q
                ->modifyQueryUsing(fn (Builder $query) => $query->where('payments.status', 'submitted')),

            'verified' => Tab::make('Verified')
                ->badge(fn (): int => Payment::query()
                    ->whereHas('application', fn (Builder $q) =>
                    $q->withoutGlobalScopes()
                        ->where('school_id', Filament::getTenant()?->id)
                    )
                    ->where('status', 'verified')
                    ->count()
                )
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('payments.status', 'verified')),

            'rejected' => Tab::make('Rejected')
                ->badge(fn (): int => Payment::query()
                    ->whereHas('application', fn (Builder $q) =>
                    $q->withoutGlobalScopes()
                        ->where('school_id', Filament::getTenant()?->id)
                    )
                    ->where('status', 'rejected')
                    ->count()
                )
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('payments.status', 'rejected')),

            'all' => Tab::make('All')
                ->badge(fn (): int => Payment::query()
                    ->whereHas('application', fn (Builder $q) =>
                    $q->withoutGlobalScopes()
                        ->where('school_id', Filament::getTenant()?->id)
                    )
                    ->count()
                ),
        ];
    }
}
