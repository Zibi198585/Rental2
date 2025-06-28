<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Podstawowe informacje')
                    ->description('Wprowadź dane produktu dostępnego do wynajmu')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nazwa produktu')
                            ->placeholder('np. Agregat prądotwórczy Honda')
                            ->prefixIcon('heroicon-o-cube')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label('Opis produktu')
                            ->placeholder('Opis, parametry techniczne, uwagi...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Cennik')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        TextInput::make('price_per_day')
                            ->label('Cena za dzień')
                            ->prefixIcon('heroicon-o-banknotes')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->helperText('Podaj cenę netto za dobę w złotych (np. 120.00)')
                            ->placeholder('0,00'),
                    ])
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }
}
