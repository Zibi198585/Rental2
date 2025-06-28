<?php

namespace App\Filament\Resources\RentalDocuments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RentalDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('agreement_number')
                    ->label('Numer umowy')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->toggleable()
                    ->formatStateUsing(fn ($state) => [
                        'draft' => 'Szkic',
                        'rented' => 'Wynajęty',
                        'partially_returned' => 'Częściowo zwrócony',
                        'scheduled_return' => 'Zaplanowany zwrot',
                        'returned' => 'Zwrócony',
                    ][$state] ?? $state),
                TextColumn::make('contractor_full_name')
                    ->label('Kontrahent')
                    ->searchable(),
                TextColumn::make('contact_phone')
                    ->label('Telefon kontaktowy')
                    ->searchable(),
                TextColumn::make('equipment_location')
                    ->label('Lokalizacja sprzętu')
                    ->searchable(),
                TextColumn::make('deposit')
                    ->label('Kaucja')
                    ->suffix(' zł')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Ostatnia aktualizacja')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rental_period_info')
                    ->label('Okres wynajmu')
                    ->searchable(false)
                    ->sortable(false),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ]),
                //->label('Akcje')
                //->icon('heroicon-o-ellipsis-horizontal'),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
