<?php

namespace App\Filament\Resources\Expenses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                DatePicker::make('date')
                    ->required()
                    ->default(now()),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('owner_id')
                    ->relationship('owner', 'name')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->default(auth()->id()),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }
}
