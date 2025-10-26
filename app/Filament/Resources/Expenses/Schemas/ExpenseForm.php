<?php

namespace App\Filament\Resources\Expenses\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                        Tabs\Tab::make('Equal Split')->id('equal-split')
                            ->schema([
                                CheckboxList::make('participant_ids')
                                    ->label('Participants')
                                    ->options(fn() => User::all()->pluck('name', 'id'))
                                    ->columns(fn () => User::count() > 3)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set) {
                                        // Clear percentages when switching to equal split
                                        $set('participant_percentages', null);
                                    })
                                    ->rules([
                                        function (Get $get) {
                                        //check if tab is focused
                                            return function (string $attribute, $value, $fail) use ($get) {
                                                // Only validate if participant_percentages is empty
                                                $percentages = $get('participant_percentages');
                                                if (empty($percentages) && empty($value)) {
                                                    $fail('At least one participant must be selected.');
                                                }
                                            };
                                        },
                                    ])
                                    ->dehydrated(fn (Get $get) => empty($get('participant_percentages'))),
                            ]),
                        Tabs\Tab::make('Advanced')
                            ->schema([
                                Repeater::make('participant_percentages')
                                    ->label('Participant Percentages')
                                    ->table([
                                        TableColumn::make('user'),
                                        TableColumn::make('percentage'),
                                    ])

                                    ->schema([
                                        Select::make('user_id')
                                            ->label('Participant')
                                            ->options(function(Get $get) {
                                                return User::all()->except(collect($get('../'))->pluck('user_id'))->pluck('name', 'id');
                                            })
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
                                    ->reorderable(false)
                                    ->columns()
                                    ->minItems(1)
                                    ->addActionLabel('Add Participant')
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, $state, $set) {
                                        // Clear participant_ids when using advanced split
                                        $set('participant_ids', null);
                                    })
                                    ->dehydrated(fn (Get $get) => empty($get('participant_ids')))
                                    ->rules([
                                        function (Get $get) {
                                            return function (string $attribute, $value, $fail) use ($get) {
                                                // Only validate if participant_ids is empty
                                                $participantIds = $get('participant_ids');

                                                if (empty($participantIds)) {
                                                    if (!is_array($value) || empty($value)) {
                                                        $fail('At least one participant must be added.');
                                                        return;
                                                    }

                                                    $total = collect($value)->sum('percentage');

                                                    if (abs($total - 100) > 0.01) {
                                                        $fail('The total percentage must equal 100%. Current total: ' . $total . '%');
                                                    }
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
