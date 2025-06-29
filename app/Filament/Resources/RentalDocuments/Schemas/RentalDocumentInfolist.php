<?php

namespace App\Filament\Resources\RentalDocuments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Illuminate\Support\Facades\Log;

class RentalDocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // HERO DASHBOARD HEADER
                Section::make()
                    ->schema([
                        Grid::make(['default' => 1, 'xl' => 3])
                            ->schema([
                                // GŁÓWNA KARTA UMOWY
                                Section::make()
                                    ->schema([
                                        TextEntry::make('agreement_number')
                                            ->label('')
                                            ->formatStateUsing(fn ($state) => $state ?: 'PROJEKT')
                                            ->extraAttributes([
                                                'class' => 'text-3xl font-black tracking-wider text-primary-600',
                                                'style' => 'font-family: "SF Pro Display", system-ui; letter-spacing: 0.1em;'
                                            ]),
                                        
                                        TextEntry::make('contractor_full_name')
                                            ->label('')
                                            ->extraAttributes([
                                                'class' => 'text-xl font-semibold text-gray-700 mt-2',
                                                'style' => 'font-family: "SF Pro Display", system-ui;'
                                            ]),
                                            
                                        TextEntry::make('status')
                                            ->label('')
                                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                                'draft' => 'PROJEKT',
                                                'rented' => 'AKTYWNA',
                                                'partially_returned' => 'CZĘŚCIOWY ZWROT',
                                                'scheduled_return' => 'PLANOWANY ZWROT',
                                                'returned' => 'ZAKOŃCZONA',
                                                default => 'NIEZNANY',
                                            })
                                            ->badge()
                                            ->color(fn (?string $state): string => match ($state) {
                                                'draft' => 'gray',
                                                'rented' => 'success',
                                                'partially_returned' => 'warning',
                                                'scheduled_return' => 'info',
                                                'returned' => 'success',
                                                default => 'gray',
                                            })
                                            ->extraAttributes(['class' => 'text-sm font-bold mt-3']),
                                    ])
                                    ->extraAttributes([
                                        'class' => 'bg-white border-2 border-primary-200 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300',
                                        'style' => 'background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); min-height: 200px; position: relative;'
                                    ]),

                                // METRYKI FINANSOWE
                                Section::make()
                                    ->schema([
                                        TextEntry::make('summary_financial')
                                            ->label('WARTOŚĆ UMOWY')
                                            ->formatStateUsing(function ($record) {
                                                $productsTotal = $record->products->sum(function ($product) {
                                                    return (float) $product->total_price * ($record->rental_days ?? 1);
                                                });
                                                $delivery = (float) ($record->delivery_cost ?? 0);
                                                $pickup = (float) ($record->pickup_cost ?? 0);
                                                // Kaucja NIE jest dodawana do sumy - to zabezpieczenie, nie koszt!
                                                $net = $productsTotal + $delivery + $pickup;
                                                $vat = $net * (($record->vat_rate ?? 23) / 100);
                                                $total = $net + $vat;
                                                
                                                return number_format($total, 2, ',', ' ') . ' zł';
                                            })
                                            ->extraAttributes([
                                                'class' => 'text-4xl font-black text-green-600',
                                                'style' => 'font-family: "SF Pro Display", system-ui; text-shadow: 0 2px 4px rgba(0,0,0,0.1);'
                                            ]),
                                            
                                        TextEntry::make('deposit')
                                            ->label('KAUCJA')
                                            ->formatStateUsing(fn ($state) => $state ? number_format((float)$state, 2, ',', ' ') . ' zł' : '0,00 zł')
                                            ->extraAttributes([
                                                'class' => 'text-2xl font-bold text-orange-600 mt-4',
                                                'style' => 'font-family: "SF Pro Display", system-ui;'
                                            ]),
                                    ])
                                    ->extraAttributes([
                                        'class' => 'bg-gradient-to-br from-green-50 to-emerald-100 border-2 border-green-200 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300',
                                        'style' => 'min-height: 200px; position: relative;'
                                    ]),

                                // TIMELINE UMOWY
                                Section::make()
                                    ->schema([
                                        TextEntry::make('rental_period')
                                            ->label('OKRES WYNAJMU')
                                            ->formatStateUsing(function ($record) {
                                                $start = $record->rental_date ? \Carbon\Carbon::parse($record->rental_date)->format('d.m.Y') : '—';
                                                $end = $record->expected_return_date ? \Carbon\Carbon::parse($record->expected_return_date)->format('d.m.Y') : '—';
                                                return "{$start} ➜ {$end}";
                                            })
                                            ->extraAttributes([
                                                'class' => 'text-xl font-bold text-blue-600',
                                                'style' => 'font-family: "SF Pro Display", system-ui;'
                                            ]),
                                            
                                        TextEntry::make('rental_days')
                                            ->label('')
                                            ->formatStateUsing(fn ($state) => $state ? "{$state} DNI" : '— DNI')
                                            ->extraAttributes([
                                                'class' => 'text-3xl font-black text-blue-800 mt-2',
                                                'style' => 'font-family: "SF Pro Display", system-ui;'
                                            ]),
                                            
                                        TextEntry::make('equipment_location')
                                            ->label('LOKALIZACJA')
                                            ->placeholder('Nie podano')
                                            ->extraAttributes([
                                                'class' => 'text-sm font-medium text-gray-600 mt-4',
                                                'style' => 'font-family: "SF Pro Display", system-ui;'
                                            ]),
                                    ])
                                    ->extraAttributes([
                                        'class' => 'bg-gradient-to-br from-blue-50 to-indigo-100 border-2 border-blue-200 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300',
                                        'style' => 'min-height: 200px; position: relative;'
                                    ]),
                            ]),
                    ])
                    ->extraAttributes([
                        'style' => 'margin-bottom: 2rem;'
                    ]),

                // ⭐ GŁÓWNA SEKCJA - LISTA WYNAJĘTYCH PRODUKTÓW ⭐
                Section::make('🛍️ WYNAJĘTE PRODUKTY')
                    ->icon('heroicon-o-cube-transparent')
                    ->schema([
                        // Licznik produktów
                        TextEntry::make('products_count')
                            ->label('')
                            ->formatStateUsing(function ($record) {
                                Log::info('RentalDocument ID: ' . $record->id);
                                Log::info('Products count: ' . $record->products->count());
                                Log::info('Products: ' . $record->products->toJson());
                                
                                $count = $record->products->count();
                                return "📦 Łącznie {$count} " . 
                                      ($count === 1 ? 'produkt' : 
                                      ($count < 5 ? 'produkty' : 'produktów'));
                            })
                            ->extraAttributes([
                                'class' => 'text-2xl font-bold text-blue-600 mb-4',
                                'style' => 'font-family: "SF Pro Display", system-ui;'
                            ]),

                        // Lista produktów z RepeatableEntry
                        RepeatableEntry::make('products')
                            ->schema([
                                Grid::make(['default' => 2, 'lg' => 5])
                                    ->schema([
                                        TextEntry::make('product.name')
                                            ->label('📦 Produkt')
                                            ->weight(FontWeight::SemiBold)
                                            ->extraAttributes(['class' => 'text-lg text-blue-700']),

                                        TextEntry::make('quantity')
                                            ->label('🔢 Ilość')
                                            ->formatStateUsing(function ($state) {
                                                $value = (float) $state;
                                                if ($value == (int) $value) {
                                                    return (string) (int) $value . ' szt.';
                                                }
                                                return rtrim(rtrim(number_format($value, 3, ',', ' '), '0'), ',') . ' szt.';
                                            })
                                            ->extraAttributes(['class' => 'text-orange-600 font-semibold']),

                                        TextEntry::make('price_per_day')
                                            ->label('💰 Cena/doba/szt.')
                                            ->formatStateUsing(fn ($state) => $state ? number_format((float)$state, 2, ',', ' ') . ' zł' : '0,00 zł')
                                            ->extraAttributes(['class' => 'text-green-600 font-semibold']),

                                        TextEntry::make('total_price')
                                            ->label('💵 Razem/doba')
                                            ->formatStateUsing(fn ($state) => $state ? number_format((float)$state, 2, ',', ' ') . ' zł' : '0,00 zł')
                                            ->extraAttributes(['class' => 'text-purple-600 font-bold']),

                                        TextEntry::make('total_period_calculated')
                                            ->label('🏆 Za cały okres')
                                            ->formatStateUsing(function ($state, $record) {
                                                // Znajdź rodzica (RentalDocument)
                                                $parent = $record;
                                                while ($parent && !isset($parent->rental_days)) {
                                                    $parent = $parent->rentalDocument ?? null;
                                                }
                                                
                                                $days = $parent?->rental_days ?? 1;
                                                $totalPrice = (float) $record->total_price;
                                                $totalForPeriod = $totalPrice * $days;
                                                
                                                return number_format($totalForPeriod, 2, ',', ' ') . ' zł (' . $days . ' ' . 
                                                       ($days === 1 ? 'dzień' : ($days < 5 ? 'dni' : 'dni')) . ')';
                                            })
                                            ->weight(FontWeight::Bold)
                                            ->extraAttributes(['class' => 'text-xl text-red-600 font-black']),
                                    ]),
                            ])
                            ->contained()
                            ->grid(['default' => 1, 'lg' => 1])
                            ->label('Wynajęte pozycje'),
                    ])
                    ->extraAttributes([
                        'class' => 'bg-gradient-to-r from-slate-50 to-gray-100 border-2 border-gray-200 rounded-2xl p-8 shadow-lg',
                        'style' => 'margin-bottom: 2rem;'
                    ]),

                // SZCZEGÓŁY FINANSOWE - OSOBNA SEKCJA
                Section::make('💰 PODSUMOWANIE FINANSOWE')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Grid::make(['default' => 2, 'lg' => 4])
                            ->schema([
                                TextEntry::make('products_value')
                                    ->label('💎 Produkty')
                                    ->formatStateUsing(function ($record) {
                                        $total = $record->products->sum(function ($product) use ($record) {
                                            return (float) $product->total_price * ($record->rental_days ?? 1);
                                        });
                                        return number_format($total, 2, ',', ' ') . ' zł';
                                    })
                                    ->extraAttributes(['class' => 'text-xl font-bold text-green-600']),

                                TextEntry::make('delivery_total')
                                    ->label('🚚 Transport')
                                    ->formatStateUsing(function ($record) {
                                        $delivery = (float) ($record->delivery_cost ?? 0);
                                        $pickup = (float) ($record->pickup_cost ?? 0);
                                        $total = $delivery + $pickup;
                                        return number_format($total, 2, ',', ' ') . ' zł';
                                    })
                                    ->extraAttributes(['class' => 'text-xl font-bold text-orange-600']),

                                TextEntry::make('deposit')
                                    ->label('🛡️ Kaucja')
                                    ->formatStateUsing(fn ($state) => $state ? number_format((float)$state, 2, ',', ' ') . ' zł' : '0,00 zł')
                                    ->extraAttributes(['class' => 'text-xl font-bold text-blue-600']),

                                TextEntry::make('grand_total')
                                    ->label('🏆 RAZEM Z VAT')
                                    ->formatStateUsing(function ($record) {
                                        $productsTotal = $record->products->sum(function ($product) {
                                            return (float) $product->total_price * ($record->rental_days ?? 1);
                                        });
                                        $delivery = (float) ($record->delivery_cost ?? 0);
                                        $pickup = (float) ($record->pickup_cost ?? 0);
                                        // Kaucja NIE jest dodawana do sumy - to zabezpieczenie, nie koszt!
                                        $net = $productsTotal + $delivery + $pickup;
                                        $vat = $net * (($record->vat_rate ?? 23) / 100);
                                        $total = $net + $vat;
                                        
                                        return number_format($total, 2, ',', ' ') . ' zł';
                                    })
                                    ->extraAttributes(['class' => 'text-2xl font-black text-green-700']),
                            ]),
                    ])
                    ->extraAttributes([
                        'class' => 'bg-gradient-to-br from-green-50 to-emerald-100 border-2 border-green-200 rounded-2xl p-6 shadow-lg'
                    ]),

                // SZCZEGÓŁY KLIENTA I DOSTAWY
                Grid::make(['default' => 1, 'lg' => 2])
                    ->schema([
                        // KARTA KLIENTA
                        Section::make('👤 PROFIL KLIENTA')
                            ->icon('heroicon-o-user-circle')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('document_type')
                                            ->label('Dokument')
                                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                                'identity_card' => '🆔 Dowód osobisty',
                                                'passport' => '📘 Paszport',
                                                'driving_license' => '🚗 Prawo jazdy',
                                                'other' => '📄 Inny',
                                                default => '—',
                                            })
                                            ->extraAttributes(['class' => 'text-sm font-semibold text-gray-700']),

                                        TextEntry::make('document_number')
                                            ->label('Numer')
                                            ->copyable()
                                            ->extraAttributes(['class' => 'text-sm font-mono text-gray-800 bg-gray-100 px-2 py-1 rounded']),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('pesel')
                                            ->label('PESEL')
                                            ->copyable()
                                            ->placeholder('—')
                                            ->extraAttributes(['class' => 'text-sm font-mono text-gray-800']),

                                        TextEntry::make('nip')
                                            ->label('NIP')
                                            ->copyable()
                                            ->placeholder('—')
                                            ->extraAttributes(['class' => 'text-sm font-mono text-gray-800']),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('contact_phone')
                                            ->label('📱 Telefon')
                                            ->copyable()
                                            ->url(fn (?string $state) => $state ? "tel:{$state}" : null)
                                            ->placeholder('—')
                                            ->extraAttributes(['class' => 'text-sm font-semibold text-blue-600']),

                                        TextEntry::make('contact_email')
                                            ->label('✉️ E-mail')
                                            ->copyable()
                                            ->url(fn (?string $state) => $state ? "mailto:{$state}" : null)
                                            ->placeholder('—')
                                            ->extraAttributes(['class' => 'text-sm font-semibold text-blue-600']),
                                    ]),
                            ])
                            ->extraAttributes([
                                'class' => 'bg-gradient-to-br from-blue-50 to-indigo-100 border-2 border-blue-200 rounded-2xl p-6 shadow-lg'
                            ]),

                        // KARTA DOSTAWY
                        Section::make('🚚 DOSTAWA I LOGISTYKA')
                            ->icon('heroicon-o-truck')
                            ->schema([
                                TextEntry::make('delivery_method')
                                    ->label('Sposób dostawy')
                                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                                        'self_pickup' => '🚶‍♂️ Odbiór własny',
                                        'delivery_to_customer' => '🚚 Dostawa do klienta',
                                        default => '—',
                                    })
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'self_pickup' => 'success',
                                        'delivery_to_customer' => 'warning',
                                        default => 'gray',
                                    })
                                    ->extraAttributes(['class' => 'text-lg font-bold']),

                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('delivery_cost')
                                            ->label('💰 Koszt dostawy')
                                            ->formatStateUsing(fn ($state) => $state ? number_format((float)$state, 2, ',', ' ') . ' zł' : '0,00 zł')
                                            ->extraAttributes(['class' => 'text-lg font-bold text-orange-600']),

                                        TextEntry::make('pickup_cost')
                                            ->label('💰 Koszt odbioru')
                                            ->formatStateUsing(fn ($state) => $state ? number_format((float)$state, 2, ',', ' ') . ' zł' : '0,00 zł')
                                            ->extraAttributes(['class' => 'text-lg font-bold text-orange-600']),
                                    ]),

                                TextEntry::make('city')
                                    ->label('🏙️ Miasto najmu')
                                    ->badge()
                                    ->color('primary')
                                    ->extraAttributes(['class' => 'text-base font-semibold']),
                            ])
                            ->extraAttributes([
                                'class' => 'bg-gradient-to-br from-orange-50 to-yellow-100 border-2 border-orange-200 rounded-2xl p-6 shadow-lg'
                            ]),
                    ]),

                // ADRES - ZWIJANY
                Section::make('🏠 ADRES ZAMIESZKANIA')
                    ->icon('heroicon-o-home-modern')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(['default' => 2, 'md' => 4, 'lg' => 6])
                            ->schema([
                                TextEntry::make('address_street')
                                    ->label('Ulica')
                                    ->columnSpan(['default' => 2, 'lg' => 2])
                                    ->placeholder('—'),

                                TextEntry::make('address_building_number')
                                    ->label('Nr budynku')
                                    ->badge()
                                    ->color('primary')
                                    ->placeholder('—'),

                                TextEntry::make('address_apartment_number')
                                    ->label('Nr mieszkania')
                                    ->badge()
                                    ->color('info')
                                    ->placeholder('—'),

                                TextEntry::make('address_postal_code')
                                    ->label('Kod pocztowy')
                                    ->fontFamily('mono')
                                    ->placeholder('—'),

                                TextEntry::make('address_city')
                                    ->label('Miasto')
                                    ->placeholder('—'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('address_voivodeship')
                                    ->label('Województwo')
                                    ->badge()
                                    ->color('success')
                                    ->placeholder('—'),

                                TextEntry::make('address_country')
                                    ->label('Kraj')
                                    ->badge()
                                    ->color('primary')
                                    ->placeholder('—'),
                            ]),
                    ])
                    ->extraAttributes([
                        'class' => 'bg-gradient-to-r from-gray-50 to-slate-100 border border-gray-200 rounded-xl p-6'
                    ]),

                // METADANE - ZWIJANE
                Section::make('⚙️ METADANE')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Utworzono')
                                    ->dateTime('d.m.Y H:i')
                                    ->color('success'),

                                TextEntry::make('updated_at')
                                    ->label('Aktualizacja')
                                    ->dateTime('d.m.Y H:i')
                                    ->color('info'),
                            ]),
                    ])
                    ->extraAttributes([
                        'class' => 'bg-gray-50 border border-gray-200 rounded-xl p-4'
                    ]),
            ]);
    }
}
