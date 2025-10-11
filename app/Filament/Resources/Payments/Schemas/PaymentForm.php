<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('from_user_id')
                    ->relationship('fromUser', 'name')
                    ->native(false)
                    ->default((string) auth()->id())
                    ->live()
                    ->required(),
                Select::make('to_user_id')
                    ->relationship('toUser', 'name')
                    ->native(false)
                    ->live()
                    ->different('from_user_id')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                DatePicker::make('date')
                    ->default(now())
                    ->required(),
            ]);
    }
}
