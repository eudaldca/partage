<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\User;
use Brick\Money\Money;
use Filament\Widgets\ChartWidget;

class Status extends ChartWidget
{
    protected ?string $heading = 'Status';

    protected float $maxAbs = 0.0;

    protected function getData(): array
    {
        $users = User::all()->mapWithKeys(fn ($user) => [$user->id => $user]);
        $expenses = Expense::all();
        $payments = Payment::all();
        foreach ($expenses as $expense) {
            $users[$expense->owner_id]->balance = $users[$expense->owner_id]->balance->plus($expense->amount);
            foreach ($expense->split_with as $userId => $splitAmount) {
                $splitAmount = Money::ofMinor($splitAmount, config('money.default_currency'));
                $users[$userId]->balance = $users[$userId]->balance->minus($splitAmount);
            }
        }

        foreach ($payments as $payment) {
            /** @var Payment $payment */
            $users[$payment->fromUser->id]->balance = $users[$payment->fromUser->id]->balance->plus($payment->amount);
            $users[$payment->toUser->id]->balance = $users[$payment->toUser->id]->balance->minus($payment->amount);
        }

        $names = $users->pluck('name')->toArray();
        $values = $users
            ->map(fn ($user) => $user->balance->getAmount()->toFloat())
            ->values()
            ->toArray();

        // Compute the maximum absolute value for symmetric x-axis bounds
        $this->maxAbs = empty($values) ? 0.0 : max(array_map('abs', $values));

        return [
            'labels' => $names,
            'datasets' => [
                [
                    'label' => 'balance',
                    'data' => $values,
                    'fill' => false,
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        // Use symmetric bounds around zero to center the 0 line
        $max = $this->maxAbs ?: 1; // avoid identical min/max when all zeros

        return [
            'indexAxis' => 'y',
            'scales' => [
                'x' => [
                    'min' => -$max,
                    'max' => $max,
                    'ticks' => [
                        'beginAtZero' => true,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
