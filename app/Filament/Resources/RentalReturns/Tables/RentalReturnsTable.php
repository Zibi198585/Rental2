<?php

namespace App\Filament\Resources\RentalReturns\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RentalReturnsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('return_number')
                    ->searchable(),
                TextColumn::make('return_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->searchable(),
                TextColumn::make('customer_phone')
                    ->searchable(),
                TextColumn::make('customer_email')
                    ->searchable(),
                TextColumn::make('transport_cost')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('transport_included')
                    ->boolean(),
                TextColumn::make('pickup_contact_person')
                    ->searchable(),
                TextColumn::make('pickup_contact_phone')
                    ->searchable(),
                TextColumn::make('status'),
                TextColumn::make('returned_by')
                    ->searchable(),
                TextColumn::make('received_by')
                    ->searchable(),
                TextColumn::make('returned_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('equipment_condition'),
                TextColumn::make('damage_fee')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('late_fee')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('additional_fees')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_rental_days')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_rental_cost')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_additional_costs')
                    ->numeric()
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
                //
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
