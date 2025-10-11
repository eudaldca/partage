<?php

namespace App\Filament\Resources\Payments\Tables;

use Brick\Money\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fromUser.name')
                    ->searchable(),
                TextColumn::make('toUser.name')
                    ->searchable(),
                TextColumn::make('amount')
                    ->formatStateUsing(fn (Money $state) => $state->formatTo(config('app.locale')))
                    ->sortable(),
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('fromUser')
                    ->relationship('fromUser', 'name')
                    ->multiple(),
                SelectFilter::make('toUser')
                    ->relationship('toUser', 'name')
                    ->multiple(),
                DateRangeFilter::make('date'),

            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
