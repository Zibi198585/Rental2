<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Podstawowe informacje')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        ImageEntry::make('image')
                            ->label('Zdjęcie produktu')
                            ->defaultImageUrl(asset('images/no-image.svg'))
                            ->size(200)
                            ->columnSpanFull(),
                            
                        Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nazwa produktu')
                                    ->icon('heroicon-o-cube')
                                    ->badge()
                                    ->color('primary')
                                    ->copyable(),

                                TextEntry::make('price_per_day')
                                    ->label('Cena za dzień')
                                    ->icon('heroicon-o-banknotes')
                                    ->money('PLN')
                                    ->color('success'),
                            ]),
                        TextEntry::make('description')
                            ->label('Opis produktu')
                            ->icon('heroicon-o-document-text')
                            ->columnSpanFull()
                            ->placeholder('Brak opisu'),
                    ]),

                Section::make('System')
                    ->icon('heroicon-o-server-stack')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Utworzono')
                                    ->dateTime('d.m.Y H:i')
                                    ->icon('heroicon-o-plus-circle')
                                    ->color('success'),

                                TextEntry::make('updated_at')
                                    ->label('Zaktualizowano')
                                    ->dateTime('d.m.Y H:i')
                                    ->icon('heroicon-o-pencil-square')
                                    ->color('info'),
                            ]),
                    ]),
            ]);
    }
}
