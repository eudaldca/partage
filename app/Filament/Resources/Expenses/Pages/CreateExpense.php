<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Filament\Resources\Expenses\ExpenseResource;
use App\Services\ExpenseService;
use Filament\Resources\Pages\CreateRecord;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    public function __construct(private readonly ExpenseService $expenseService)
    {
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Check which tab was used
        if (!empty($data['participant_percentages'])) {
            // Advanced tab - split by percentages
            $percentagesByParticipant = collect($data['participant_percentages'])
                ->pluck('percentage', 'user_id')
                ->toArray();

            $data['split_with'] = $this->expenseService->splitAmountByPercentages(
                $data['amount'],
                $percentagesByParticipant
            );
        } else {
            // Equal split tab
            $data['split_with'] = $this->expenseService->splitAmountAmongParticipants(
                $data['amount'],
                $data['participant_ids']
            );
        }

        return $data;
    }
}
