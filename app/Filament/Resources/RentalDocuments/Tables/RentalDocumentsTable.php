<?php

namespace App\Filament\Resources\RentalDocuments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;

class RentalDocumentsTable
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
                        'rented' => 'success', 
                        'partially_returned' => 'warning',
                        'scheduled_return' => 'info',
                        'returned' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'draft' => 'Szkic',
                        'rented' => 'Aktywny',
                        'partially_returned' => 'Częściowy zwrot',
                        'scheduled_return' => 'Planowany zwrot',
                        'returned' => 'Zwrócony',
                        default => $state,
                    }),

                TextColumn::make('agreement_number')
                    ->label('Numer umowy')
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->sortable(),

                TextColumn::make('contractor_full_name')
                    ->label('Klient')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) > 30) {
                            return $state;
                        }
                        return null;
                    }),

                TextColumn::make('equipment_location')
                    ->label('Lokalizacja')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '—';
                        $parts = explode(',', $state);
                        return trim($parts[0]);
                    })
                    ->badge()
                    ->color('gray'),

                TextColumn::make('rental_period')
                    ->label('Okres wynajmu')
                    ->getStateUsing(function ($record) {
                        $start = $record->rental_date ? \Carbon\Carbon::parse($record->rental_date)->format('d.m.Y') : '—';
                        $end = $record->expected_return_date ? \Carbon\Carbon::parse($record->expected_return_date)->format('d.m.Y') : '—';
                        $days = $record->rental_days ?? 0;
                        
                        return $start . ' → ' . $end . '<br/><small class="text-gray-500">' . $days . ' dni</small>';
                    })
                    ->html()
                    ->color(function ($record) {
                        if (!$record->expected_return_date || $record->status === 'returned') {
                            return 'gray';
                        }
                        $today = now()->startOfDay();
                        $returnDate = \Carbon\Carbon::parse($record->expected_return_date)->startOfDay();
                        
                        if ($returnDate->isPast()) {
                            return 'danger';
                        } elseif ($returnDate->diffInDays($today) <= 2) {
                            return 'warning';
                        }
                        return 'success';
                    }),

                TextColumn::make('summary_products_per_day')
                    ->label('Kwota dobowa')
                    ->getStateUsing(function ($record) {
                        if (!in_array($record->status, ['rented', 'partially_returned'])) {
                            return '—';
                        }
                        return $record->summary_products_per_day ? number_format($record->summary_products_per_day, 2, ',', ' ') . ' zł' : '—';
                    })
                    ->sortable()
                    ->color('success')
                    ->weight(FontWeight::Medium),

                TextColumn::make('issue_status')
                    ->label('Status wydań')
                    ->getStateUsing(function ($record) {
                        if (!in_array($record->status, ['rented', 'partially_returned', 'scheduled_return'])) {
                            return '—';
                        }
                        
                        $service = new \App\Services\RentalDocumentStatusService();
                        $status = $service->getDocumentStatus($record);
                        $summary = $status['summary'];
                        
                        if ($summary['total_remaining_to_issue'] > 0) {
                            return 'Wydano: ' . $summary['total_issued'] . '/' . $summary['total_planned'] . ' (' . $summary['completion_percentage'] . '%)<br/>' .
                                   '<small class="text-orange-600">Pozostało do wydania: ' . $summary['total_remaining_to_issue'] . '</small>';
                        } elseif ($summary['total_in_circulation'] > 0) {
                            return 'Wydano: ' . $summary['total_issued'] . '/' . $summary['total_planned'] . ' (100%)<br/>' .
                                   '<small class="text-blue-600">W obiegu: ' . $summary['total_in_circulation'] . '</small>';
                        } else {
                            return 'Wydano: ' . $summary['total_issued'] . '/' . $summary['total_planned'] . ' (100%)<br/>' .
                                   '<small class="text-green-600">Wszystko zwrócone</small>';
                        }
                    })
                    ->html()
                    ->searchable(false)
                    ->sortable(false),

                TextColumn::make('deposit_balance_text')
                    ->label('Saldo kaucji')
                    ->getStateUsing(function ($record) {
                        $balance = $record->deposit_balance;
                        
                        if ($balance['status'] === 'inactive') {
                            return $record->deposit ? number_format($record->deposit, 2, ',', ' ') . ' zł<br/><small class="text-gray-500">nieaktywne</small>' : 'Brak kaucji';
                        }

                        // Dla zwróconych - pokaż ostateczną różnicę
                        if ($record->status === 'returned') {
                            $difference = $balance['balance_difference'];
                            if ($difference > 0) {
                                return '<span class="text-green-600">Zwrot: ' . number_format($difference, 2, ',', ' ') . ' zł</span>';
                            } elseif ($difference < 0) {
                                return '<span class="text-red-600">Dopłata: ' . number_format(abs($difference), 2, ',', ' ') . ' zł</span>';
                            } else {
                                return '<span class="text-gray-600">Rozliczone dokładnie</span>';
                            }
                        }

                        // Dla aktywnych wynajmów
                        $remaining = number_format($balance['remaining'], 2, ',', ' ') . ' zł';
                        $progress = $balance['days_passed'] . '/' . $balance['days_covered_by_deposit'] . ' dni';
                        
                        $finalDifference = $balance['balance_difference'];
                        if ($finalDifference > 0) {
                            $prognosis = '<small class="text-green-600">prognoza: zwrot ' . number_format($finalDifference, 2, ',', ' ') . ' zł</small>';
                        } elseif ($finalDifference < 0) {
                            $prognosis = '<small class="text-red-600">prognoza: dopłata ' . number_format(abs($finalDifference), 2, ',', ' ') . ' zł</small>';
                        } else {
                            $prognosis = '<small class="text-gray-600">prognoza: dokładnie</small>';
                        }
                        
                        return $remaining . ' (' . $progress . ')<br/>' . $prognosis;
                    })
                    ->html()
                    ->searchable(false)
                    ->sortable(false),

                // Ukryte kolumny
                    
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
                        'rented' => 'Aktywny',
                        'partially_returned' => 'Częściowy zwrot',
                        'scheduled_return' => 'Planowany zwrot',
                        'returned' => 'Zwrócony',
                        'draft' => 'Szkic',
                    ]),
                    
                SelectFilter::make('equipment_location')
                    ->label('Lokalizacja')
                    ->options(function () {
                        return \App\Models\RentalDocument::whereNotNull('equipment_location')
                            ->distinct()
                            ->pluck('equipment_location')
                            ->mapWithKeys(function ($location) {
                                $city = trim(explode(',', $location)[0]);
                                return [$city => $city];
                            })
                            ->unique()
                            ->sort()
                            ->toArray();
                    }),
            ])
            ->defaultSort('updated_at', 'desc')
            ->striped()
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
