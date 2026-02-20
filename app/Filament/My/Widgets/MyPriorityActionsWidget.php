<?php

declare(strict_types=1);

namespace App\Filament\My\Widgets;

use App\Filament\My\Resources\Applications\ApplicationResource;
use App\Filament\My\Resources\Payments\PaymentResource;
use App\Filament\My\Resources\Schedules\ScheduleResource;
use App\Models\Application;
use App\Models\Payment;
use App\Models\Schedule;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyPriorityActionsWidget extends BaseWidget
{
    protected ?string $heading = 'Priority Actions';

    protected function getStats(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $draftApplication = Application::query()
            ->where('user_id', $user->id)
            ->where('status', 'draft')
            ->latest('updated_at')
            ->first();

        $actionablePayment = Payment::query()
            ->whereHas('application', fn ($query) => $query->where('user_id', $user->id))
            ->whereIn('status', ['pending', 'rejected'])
            ->latest('updated_at')
            ->first();

        $actionableSchedule = Schedule::query()
            ->whereHas('application', fn ($query) => $query->where('user_id', $user->id))
            ->whereIn('status', ['scheduled', 'rescheduled'])
            ->whereDate('scheduled_date', '>=', today())
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->first();

        $unreadNotifications = $user->unreadNotifications()->count();

        $primaryAction = $this->buildPrimaryActionStat(
            draftApplication: $draftApplication,
            actionablePayment: $actionablePayment,
            actionableSchedule: $actionableSchedule,
        );

        $stats = [
            $primaryAction,
            Stat::make('Unread Notifications', (string) $unreadNotifications)
                ->description($unreadNotifications > 0
                    ? 'Check the bell icon to read updates.'
                    : 'No unread notifications.')
                ->descriptionIcon('heroicon-o-bell')
                ->color($unreadNotifications > 0 ? 'warning' : 'gray'),
        ];

        if ($actionablePayment) {
            $stats[] = Stat::make('Payment Pending Action', $actionablePayment->transaction_code)
                ->description('Open payment and submit/review payment proof.')
                ->descriptionIcon('heroicon-o-banknotes')
                ->url(PaymentResource::getUrl('view', ['record' => $actionablePayment]))
                ->color('danger');
        }

        if ($actionableSchedule) {
            $stats[] = Stat::make(
                'Schedule Needs Confirmation',
                $actionableSchedule->scheduled_date?->format('d M Y') . ' ' . substr((string) $actionableSchedule->scheduled_time, 0, 5)
            )
                ->description('Confirm attendance or request reschedule.')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->url(ScheduleResource::getUrl('view', ['record' => $actionableSchedule]))
                ->color('info');
        }

        return $stats;
    }

    private function buildPrimaryActionStat(
        ?Application $draftApplication,
        ?Payment $actionablePayment,
        ?Schedule $actionableSchedule
    ): Stat {
        if ($draftApplication) {
            return Stat::make('Primary Action', 'Complete Draft Application')
                ->description('Finish and submit application ' . $draftApplication->application_number)
                ->descriptionIcon('heroicon-o-document-text')
                ->url(ApplicationResource::getUrl('edit', ['record' => $draftApplication]))
                ->color('warning');
        }

        if ($actionablePayment) {
            return Stat::make('Primary Action', 'Upload Payment Proof')
                ->description('Payment ' . $actionablePayment->transaction_code . ' needs follow-up.')
                ->descriptionIcon('heroicon-o-banknotes')
                ->url(PaymentResource::getUrl('view', ['record' => $actionablePayment]))
                ->color('danger');
        }

        if ($actionableSchedule) {
            return Stat::make('Primary Action', 'Confirm Schedule')
                ->description('You have an upcoming schedule to confirm.')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->url(ScheduleResource::getUrl('view', ['record' => $actionableSchedule]))
                ->color('info');
        }

        return Stat::make('Primary Action', 'No Urgent Action')
            ->description('Semua proses utama sudah up to date.')
            ->descriptionIcon('heroicon-o-check-circle')
            ->url(ApplicationResource::getUrl('index'))
            ->color('primary');
    }
}
