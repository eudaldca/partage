<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Guava\IconPicker\Forms\Components\IconPicker;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                IconPicker::make('icon')
                    ->sets(['fontawesome-solid'])
                    ->columns(4)
                    ->iconsSearchResults(false)
                    ->required(),
                ColorPicker::make('color')
                    ->required(),
            ]);
    }
}
