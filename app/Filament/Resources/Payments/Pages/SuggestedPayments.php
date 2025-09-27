<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\User;
use Brick\Money\Money;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class SuggestedPayments extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = PaymentResource::class;

    protected string $view = 'filament.resources.payments.pages.suggested-payments';
    protected static ?string $slug = 'payments';

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return $table
            ->records(fn(): array => $this->obtainTableRecords())
            ->columns([
                TextColumn::make('from_user')
                    ->label('From User')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state->name)
                    ->color('danger')
                    ->icon('heroicon-o-arrow-right'),

                TextColumn::make('to_user')
                    ->label('To User')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state->name)
                    ->color('success')
                    ->icon('heroicon-o-arrow-left'),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn(Money $state) => $state->formatTo(app()->getLocale()))
                    ->money()
                    ->sortable()
                    ->weight('bold'),
            ])
            ->recordActions([
                Action::make('create_payment')
                    ->button()
                    ->label('Create Payment')
                    ->icon('heroicon-o-banknotes')
                    ->requiresConfirmation()
                    ->modalHeading('Create Payment')
                    ->modalDescription(fn($record) => sprintf(
                        "Create a payment of %s from %s to %s?",
                        $record['amount']->formatTo(app()->getLocale()),
                        $record['from_user']->name,
                        $record['to_user']->name)
                    )
                    ->modalSubmitActionLabel('Create Payment')
                    ->action(function (array $record) {
                        $this->createPayment($record);
                        $this->resetTable();
                    }),
            ])
            ->emptyStateHeading('🎉 All balances are settled!')
            ->emptyStateDescription('No payments are needed at this time. All user balances are settled.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->striped()
            ->paginated(false);
    }


    protected function obtainTableRecords(): array
    {
        $suggestedPayments = $this->calculateSuggestedPayments();

        return $suggestedPayments->map(fn($payment) => [
            'from_user' => $payment['from_user'],
            'to_user' => $payment['to_user'],
            'amount' => $payment['amount'],
        ])->toArray();
    }

    protected function calculateSuggestedPayments(): Collection
    {
        // Calculate user balances
        $users = User::all()->mapWithKeys(fn($user) => [$user->id => $user]);
        $expenses = Expense::all();
        $payments = Payment::all();

        // Apply expenses
        foreach ($expenses as $expense) {
            $users[$expense->owner_id]->balance = $users[$expense->owner_id]->balance->plus($expense->amount);
            foreach ($expense->split_with as $userId => $splitAmount) {
                $splitAmount = Money::ofMinor($splitAmount, config('money.default_currency'));
                $users[$userId]->balance = $users[$userId]->balance->minus($splitAmount);
            }
        }


        // Apply existing payments
        foreach ($payments as $payment) {
            $users[$payment->from_user_id]->balance = $users[$payment->from_user_id]->balance->plus($payment->amount);
            $users[$payment->to_user_id]->balance = $users[$payment->to_user_id]->balance->minus($payment->amount);
        }

        // Separate creditors (positive balance) and debtors (negative balance)
        $creditors = [];
        $debtors = [];

        foreach ($users as $user) {
            $amount = $user->balance->getAmount()->toFloat();
            if ($amount > 0) {
                $creditors[] = [
                    'user' => $user,
                    'amount' => $user->balance
                ];
            } elseif ($amount < 0) {
                $debtors[] = [
                    'user' => $user,
                    'amount' => $user->balance->abs()
                ];
            }
        }

        // Sort by amount (largest first) for better optimization
        usort($creditors, fn($a, $b) => $b['amount']->compareTo($a['amount']));
        usort($debtors, fn($a, $b) => $b['amount']->compareTo($a['amount']));

        // Calculate optimal payments
        $suggestedPayments = [];
        $creditorIndex = 0;
        $debtorIndex = 0;

        while ($creditorIndex < count($creditors) && $debtorIndex < count($debtors)) {
            $creditor = &$creditors[$creditorIndex];
            $debtor = &$debtors[$debtorIndex];

            $paymentAmount = $creditor['amount']->isLessThan($debtor['amount'])
                ? $creditor['amount']
                : $debtor['amount'];

            if ($paymentAmount->isPositive()) {
                $suggestedPayments[] = [
                    'from_user' => $debtor['user'],
                    'to_user' => $creditor['user'],
                    'amount' => $paymentAmount
                ];

                $creditor['amount'] = $creditor['amount']->minus($paymentAmount);
                $debtor['amount'] = $debtor['amount']->minus($paymentAmount);
            }

            if ($creditor['amount']->isZero()) {
                $creditorIndex++;
            }
            if ($debtor['amount']->isZero()) {
                $debtorIndex++;
            }
        }

        return collect($suggestedPayments);
    }

    public function createPayment(array $record): void
    {
        try {
            $record = Payment::create([
                'from_user_id' => $record['from_user']->id,
                'to_user_id' => $record['to_user']->id,
                'amount' => $record['amount'],
                'date' => now(),
            ]);

            Notification::make()
                ->title('Payment created successfully')
                ->body("Payment of {$record->amount->formatTo(app()->getLocale())} from {$record->fromUser->name} to {$record->toUser->name} has been created.")
                ->success()
                ->send();

            // Refresh the table to reflect changes
            $this->dispatch('$refresh');

        } catch (Exception $e) {
            Notification::make()
                ->title('Error creating payment')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh Suggestions')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn() => $this->dispatch('$refresh')),
        ];
    }
}
