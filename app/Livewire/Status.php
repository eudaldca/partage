<?php

namespace App\Livewire;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class Status extends ChartWidget
{
    protected ?string $heading = 'Status';

    protected function getData(): array
    {
        $users = User::with(['expenses', 'paymentsIssued', 'paymentsReceived'])->get();
        return [
            'labels' => $users->pluck('name')->toArray(),
            'datasets' => [
                [
                    'label' => 'debts',
                    'data' => $users->map(fn ($user) => $user->paymentsIssued()->sum('amount')),
                    'fill' => false,
                    'borderWidth' => 1,
                ]
            ]
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
        ];
    }


    protected function getType(): string
    {
        return 'bar';
    }
}
