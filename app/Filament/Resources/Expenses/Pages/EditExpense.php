<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Filament\Resources\Expenses\ExpenseResource;
use App\Filament\Resources\Expenses\Schemas\ExpenseForm;
use App\Services\ExpenseService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;

class EditExpense extends EditRecord
{
    protected static string $resource = ExpenseResource::class;

    private bool $showAdvancedTab = false;
    private readonly ExpenseService $expenseService;

    public function __construct()
    {
        $this->expenseService = app(ExpenseService::class);
    }

    public function form(Schema $schema): Schema
    {
        return ExpenseForm::configure($schema, $this->showAdvancedTab);
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $splitWith = $data['split_with'] ?? [];

        // Check if split is unequal (more than 1 cent difference)
        if ($this->expenseService->hasUnequalSplit($splitWith)) {
            $this->showAdvancedTab = true;

            // Use advanced tab - convert amounts to percentages
            $totalAmount = $data['amount'];
            $data['participant_percentages'] = collect($splitWith)
                ->map(function ($amount, $userId) use ($totalAmount) {
                    $percentage = $totalAmount > 0 ? ($amount / $totalAmount) * 100 : 0;
                    return [
                        'user_id' => $userId,
                        'percentage' => round($percentage, 2),
                    ];
                })
                ->values()
                ->toArray();
        } else {
            // Use equal split tab
            $data['participant_ids'] = array_keys($splitWith);
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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
