<?php

namespace App\Services;

use Brick\Money\Money;

class ExpenseService
{
    public function splitAmountAmongParticipants(int $totalAmount, array $participantIds): array
    {
        $amount = Money::of($totalAmount, config('money.default_currency'));
        $splitAmounts = collect($amount->split(count($participantIds)))
            ->map(fn(Money $money) => $money->getMinorAmount())
            ->toArray();

        return array_combine($participantIds, $splitAmounts);
    }

    public function splitAmountByPercentages(int $totalAmount, array $percentagesByParticipant): array
    {
        $amount = Money::of($totalAmount, config('money.default_currency'));

        return collect($percentagesByParticipant)
            ->mapWithKeys(function ($percentage, $participantId) use ($amount) {
                $participantAmount = $amount->multipliedBy($percentage / 100, roundingMode: \Brick\Math\RoundingMode::HALF_UP);
                return [$participantId => $participantAmount->getMinorAmount()->toInt()];
            })
            ->toArray();
    }

    public function hasUnequalSplit(array $splitWith): bool
    {
        if (count($splitWith) <= 1) {
            return false;
        }

        $amounts = array_values($splitWith);
        $min = min($amounts);
        $max = max($amounts);

        // Check if difference is more than 1 cent (rounding tolerance)
        return ($max - $min) > 1;
    }
}
