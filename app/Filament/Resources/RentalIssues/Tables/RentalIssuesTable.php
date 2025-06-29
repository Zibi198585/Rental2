<?php

namespace App\Filament\Resources\RentalIssues\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;

class RentalIssuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'issued' => 'success', 
                        'partially_returned' => 'warning',
                        'fully_returned' => 'primary',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'draft' => 'Szkic',
                        'issued' => 'Wydane',
                        'partially_returned' => 'Częściowy zwrot',
                        'fully_returned' => 'Zwrócone',
                        'cancelled' => 'Anulowane',
                        default => $state,
                    }),

                TextColumn::make('issue_number')
                    ->label('Numer wydania')
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->sortable(),

                TextColumn::make('customer_display_name')
                    ->label('Klient')
                    ->searchable(['customer_name', 'rentalDocument.contractor_full_name'])
                    ->description(fn ($record) => $record->rentalDocument ? 
                        'Umowa: ' . $record->rentalDocument->agreement_number : 
                        'Wydanie bezumowne'
                    ),

                TextColumn::make('issue_date')
                    ->label('Data wydania')
                    ->date('d.m.Y')
                    ->sortable()
                    ->color(fn ($record) => $record->issue_date->isPast() ? 'success' : 'warning'),

                TextColumn::make('products_summary')
                    ->label('Produkty')
                    ->formatStateUsing(function ($record) {
                        $productsCount = $record->products->count();
                        $totalQuantity = $record->products->sum('quantity');
                        $returnedQuantity = $record->products->sum('returned_quantity');
                        
                        if ($productsCount === 0) {
                            return 'Brak produktów';
                        }
                        
                        $text = $productsCount . ' ' . ($productsCount === 1 ? 'pozycja' : 
                               ($productsCount < 5 ? 'pozycje' : 'pozycji'));
                        $text .= " ({$totalQuantity} szt.)";
                        
                        if ($returnedQuantity > 0) {
                            $text .= "\nZwrócono: {$returnedQuantity} szt.";
                        }
                        
                        return $text;
                    })
                    ->html()
                    ->wrap(),

                TextColumn::make('total_daily_cost')
                    ->label('Koszt dzienny')
                    ->money('PLN')
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->color('primary'),

                TextColumn::make('transport_info')
                    ->label('Transport')
                    ->formatStateUsing(function ($record) {
                        if ($record->transport_cost > 0) {
                            return number_format($record->transport_cost, 2, ',', ' ') . ' zł';
                        }
                        return $record->transport_included ? 'Wliczony' : 'Odbiór własny';
                    })
                    ->icon(fn ($record) => $record->transport_cost > 0 ? 'heroicon-o-truck' : 'heroicon-o-home'),

                TextColumn::make('delivery_address')
                    ->label('Adres dostawy')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    })
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('delivery_contact_person')
                    ->label('Osoba kontaktowa')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('issued_by')
                    ->label('Wydał')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('received_by')
                    ->label('Odebrał')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('issued_at')
                    ->label('Czas wydania')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('estimated_total_cost')
                    ->label('Szacowany koszt')
                    ->money('PLN')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Szkic',
                        'issued' => 'Wydane',
                        'partially_returned' => 'Częściowy zwrot',
                        'fully_returned' => 'Zwrócone',
                        'cancelled' => 'Anulowane',
                    ]),

                Filter::make('issue_date')
                    ->form([
                        DatePicker::make('issued_from')
                            ->label('Data wydania od'),
                        DatePicker::make('issued_until')
                            ->label('Data wydania do'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['issued_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('issue_date', '>=', $date),
                            )
                            ->when(
                                $data['issued_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('issue_date', '<=', $date),
                            );
                    }),

                SelectFilter::make('has_rental_document')
                    ->label('Typ wydania')
                    ->options([
                        'with_document' => 'Z umową',
                        'without_document' => 'Bezumowne',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'with_document' => $query->whereNotNull('rental_document_id'),
                            'without_document' => $query->whereNull('rental_document_id'),
                            default => $query,
                        };
                    }),

                Filter::make('transport_cost')
                    ->label('Z transportem')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('transport_cost', '>', 0)),
            ])
            ->defaultSort('issue_date', 'desc')
            ->striped()
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ])
                ->label('Akcje')
                ->icon('heroicon-o-ellipsis-horizontal'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
