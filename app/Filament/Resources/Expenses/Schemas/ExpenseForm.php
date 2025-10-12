<?php

namespace App\Filament\Resources\Expenses\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;

class ExpenseForm
{
    public static function configure(Schema $schema, bool $showAdvancedTab = false): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->live(),
                DatePicker::make('date')
                    ->required()
                    ->default(now()),
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
                Textarea::make('description')
                    ->columnSpanFull(),
                Tabs::make('participants')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Equal Split')
                            ->schema([
                                CheckboxList::make('participant_ids')
                                    ->label('Participants')
                                    ->minItems(1)
                                    ->options(fn() => User::all()->pluck('name', 'id'))
                                    ->columns(fn () => User::count() > 3)
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, $state, $set) {
                                        // Clear percentages when switching to equal split
                                        $set('participant_percentages', null);
                                    }),
                            ]),
                        Tabs\Tab::make('Advanced')
                            ->schema([
                                Repeater::make('participant_percentages')
                                    ->label('Participant Percentages')
                                    ->schema([
                                        Select::make('user_id')
                                            ->label('Participant')
                                            ->options(fn() => User::all()->pluck('name', 'id'))
                                            ->required()
                                            ->searchable()
                                            ->distinct()
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                        TextInput::make('percentage')
                                            ->label('Percentage')
                                            ->required()
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->suffix('%')
                                            ->live(),
                                    ])
                                    ->columns(2)
                                    ->minItems(1)
                                    ->addActionLabel('Add Participant')
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, $state, $set) {
                                        // Clear participant_ids when using advanced split
                                        $set('participant_ids', null);
                                    })
                                    ->rules([
                                        function (Get $get) {
                                            return function (string $attribute, $value, $fail) use ($get) {
                                                if (!is_array($value)) {
                                                    return;
                                                }

                                                $total = collect($value)->sum('percentage');

                                                if (abs($total - 100) > 0.01) {
                                                    $fail('The total percentage must equal 100%. Current total: ' . $total . '%');
                                                }
                                            };
                                        },
                                    ]),
                            ]),
                    ])
                    ->activeTab($showAdvancedTab ? 2 : 1),
            ]);
    }
}
