<?php

namespace App\Filament\Resources\RentalDocuments\Schemas;

use App\Models\Product;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Repeater\TableColumn;

class RentalDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Formularz umowy')
                    ->tabs([
                        // Tab 1: Podstawowe informacje
                        Tabs\Tab::make('Podstawowe')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('agreement_number')
                                            ->label('Numer umowy')
                                            ->placeholder('Wygeneruje się automatycznie')
                                            ->prefixIcon('heroicon-o-hashtag')
                                            ->maxLength(255)
                                            ->readOnly(),

                                        Select::make('status')
                                            ->label('Status')
                                            ->options([
                                                'draft' => 'Wersja robocza',
                                                'rented' => 'Wynajęta',
                                                'partially_returned' => 'Częściowo zwrócona',
                                                'scheduled_return' => 'Zaplanowany zwrot',
                                                'returned' => 'Zwrócona',
                                            ])
                                            ->default('draft')
                                            ->required()
                                            ->native(false)
                                            ->live(),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('contractor_full_name')
                                            ->label('Kontrahent')
                                            ->required()
                                            ->prefixIcon('heroicon-o-user')
                                            ->placeholder('Imię i nazwisko lub nazwa firmy')
                                            ->maxLength(255),

                                        TextInput::make('city')
                                            ->label('Miasto najmu')
                                            ->default('Wyry')
                                            ->required()
                                            ->prefixIcon('heroicon-o-map-pin')
                                            ->maxLength(255),
                                    ]),

                                TextInput::make('equipment_location')
                                    ->label('Lokalizacja sprzętu')
                                    ->prefixIcon('heroicon-o-map-pin')
                                    ->placeholder('np. ul. Kopaniny 2, 43-175 Wyry')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ]),

                        // Tab 2: Dane kontaktowe i dokumenty
                        Tabs\Tab::make('Kontakt i dokumenty')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Section::make('Dane kontaktowe')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('contact_phone')
                                                    ->label('Telefon')
                                                    ->prefixIcon('heroicon-o-phone')
                                                    ->placeholder('+48 000 000 000')
                                                    ->tel()
                                                    ->maxLength(255),

                                                TextInput::make('contact_email')
                                                    ->label('Email')
                                                    ->prefixIcon('heroicon-o-envelope')
                                                    ->placeholder('email@example.com')
                                                    ->email()
                                                    ->maxLength(255),
                                            ]),
                                    ])
                                    ->compact(),

                                Section::make('Dokumenty tożsamości')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('document_type')
                                                    ->label('Typ dokumentu')
                                                    ->options([
                                                        'identity_card' => 'Dowód osobisty',
                                                        'passport' => 'Paszport',
                                                        'driving_license' => 'Prawo jazdy',
                                                        'other' => 'Inny dokument',
                                                    ])
                                                    ->default('identity_card')
                                                    ->required()
                                                    ->native(false)
                                                    ->live(),

                                                TextInput::make('document_number')
                                                    ->label('Numer dokumentu')
                                                    ->prefixIcon('heroicon-o-hashtag')
                                                    ->placeholder('np. ABC123456')
                                                    ->maxLength(255),
                                            ]),

                                        TextInput::make('other_document')
                                            ->label('Opis innego dokumentu')
                                            ->placeholder('Opisz jaki dokument')
                                            ->maxLength(255)
                                            ->visible(fn (Get $get) => $get('document_type') === 'other'),

                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('pesel')
                                                    ->label('PESEL')
                                                    ->placeholder('00000000000')
                                                    ->mask('99999999999')
                                                    ->length(11),

                                                TextInput::make('nip')
                                                    ->label('NIP')
                                                    ->placeholder('0000000000')
                                                    ->mask('9999999999')
                                                    ->length(10),
                                            ]),
                                    ])
                                    ->compact(),
                            ]),

                        // Tab 3: Adres
                        Tabs\Tab::make('Adres')
                            ->icon('heroicon-o-home')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('address_street')
                                            ->label('Ulica')
                                            ->placeholder('np. ul. Główna')
                                            ->maxLength(255),

                                        TextInput::make('address_building_number')
                                            ->label('Nr budynku')
                                            ->placeholder('15')
                                            ->maxLength(255),

                                        TextInput::make('address_apartment_number')
                                            ->label('Nr lokalu')
                                            ->placeholder('5')
                                            ->maxLength(255),

                                        TextInput::make('address_postal_code')
                                            ->label('Kod pocztowy')
                                            ->placeholder('00-000')
                                            ->mask('99-999')
                                            ->maxLength(6),
                                    ]),

                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('address_city')
                                            ->label('Miasto')
                                            ->placeholder('Warszawa')
                                            ->maxLength(255),

                                        Select::make('address_voivodeship')
                                            ->label('Województwo')
                                            ->options([
                                                'dolnośląskie' => 'Dolnośląskie',
                                                'kujawsko-pomorskie' => 'Kujawsko-pomorskie',
                                                'lubelskie' => 'Lubelskie',
                                                'lubuskie' => 'Lubuskie',
                                                'łódzkie' => 'Łódzkie',
                                                'małopolskie' => 'Małopolskie',
                                                'mazowieckie' => 'Mazowieckie',
                                                'opolskie' => 'Opolskie',
                                                'podkarpackie' => 'Podkarpackie',
                                                'podlaskie' => 'Podlaskie',
                                                'pomorskie' => 'Pomorskie',
                                                'śląskie' => 'Śląskie',
                                                'świętokrzyskie' => 'Świętokrzyskie',
                                                'warmińsko-mazurskie' => 'Warmińsko-mazurskie',
                                                'wielkopolskie' => 'Wielkopolskie',
                                                'zachodniopomorskie' => 'Zachodniopomorskie',
                                            ])
                                            ->searchable()
                                            ->native(false)
                                            ->visible(fn (Get $get) => $get('address_country') === 'Polska' || !$get('address_country')),

                                        TextInput::make('address_voivodeship_text')
                                            ->label('Województwo/Stan')
                                            ->placeholder('np. California')
                                            ->maxLength(255)
                                            ->visible(fn (Get $get) => $get('address_country') !== 'Polska' && $get('address_country')),

                                        Select::make('address_country')
                                            ->label('Kraj')
                                            ->options([
                                                'Polska' => 'Polska',
                                                'Niemcy' => 'Niemcy',
                                                'Czechy' => 'Czechy',
                                                'Słowacja' => 'Słowacja',
                                                'Ukraina' => 'Ukraina',
                                                'Litwa' => 'Litwa',
                                                'Białoruś' => 'Białoruś',
                                                'Rosja' => 'Rosja',
                                                'Inne' => 'Inne',
                                            ])
                                            ->default('Polska')
                                            ->searchable()
                                            ->native(false)
                                            ->live(),
                                    ]),
                            ]),

                        // Tab 4: Wynajem i produkty
                        Tabs\Tab::make('Wynajem')
                            ->icon('heroicon-o-calendar-days')
                            ->schema([
                                Section::make('Okres wynajmu')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                DatePicker::make('rental_date')
                                                    ->label('Data wynajmu')
                                                    ->native(false)
                                                    ->displayFormat('d.m.Y')
                                                    ->format('Y-m-d')
                                                    ->default(now())
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDays($get, $set)),

                                                DatePicker::make('expected_return_date')
                                                    ->label('Przewidywany zwrot')
                                                    ->native(false)
                                                    ->displayFormat('d.m.Y')
                                                    ->format('Y-m-d')
                                                    ->default(now()->addDay())
                                                    ->required()
                                                    ->minDate(fn (Get $get) => $get('rental_date') ? Carbon::parse($get('rental_date'))->addDay() : now()->addDay())
                                                    ->live()
                                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDays($get, $set)),

                                                TextInput::make('rental_days')
                                                    ->label('Liczba dni')
                                                    ->numeric()
                                                    ->suffix('dni')
                                                    ->readOnly()
                                                    ->default(1)
                                                    ->live(),
                                            ]),
                                    ])
                                    ->compact(),

                                Section::make('Dostawa')
                                    ->schema([
                                        Select::make('delivery_method')
                                            ->label('Sposób dostawy')
                                            ->options([
                                                'self_pickup' => 'Odbiór osobisty',
                                                'delivery_to_customer' => 'Dostawa do klienta',
                                            ])
                                            ->default('self_pickup')
                                            ->required()
                                            ->native(false)
                                            ->live()
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateSummaryFields($get, $set)),

                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('delivery_cost')
                                                    ->label('Koszt dostawy')
                                                    ->numeric()
                                                    ->suffix('zł')
                                                    ->default(0)
                                                    ->step(0.01)
                                                    ->live(onBlur: true)
                                                    ->visible(fn (Get $get) => $get('delivery_method') === 'delivery_to_customer')
                                                    ->formatStateUsing(fn ($state) => $state ? number_format((float)$state, 2, '.', '') : '0.00')
                                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                                        if ($state !== null && $state !== '') {
                                                            $formattedCost = number_format((float)$state, 2, '.', '');
                                                            $set('delivery_cost', $formattedCost);
                                                        } else {
                                                            $set('delivery_cost', '0.00');
                                                        }
                                                        self::updateSummaryFields($get, $set);
                                                    }),

                                                TextInput::make('pickup_cost')
                                                    ->label('Koszt odbioru')
                                                    ->numeric()
                                                    ->suffix('zł')
                                                    ->default(0)
                                                    ->step(0.01)
                                                    ->live(onBlur: true)
                                                    ->visible(fn (Get $get) => $get('delivery_method') === 'delivery_to_customer')
                                                    ->formatStateUsing(fn ($state) => $state ? number_format((float)$state, 2, '.', '') : '0.00')
                                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                                        if ($state !== null && $state !== '') {
                                                            $formattedCost = number_format((float)$state, 2, '.', '');
                                                            $set('pickup_cost', $formattedCost);
                                                        } else {
                                                            $set('pickup_cost', '0.00');
                                                        }
                                                        self::updateSummaryFields($get, $set);
                                                    }),

                                                TextInput::make('deposit')
                                                    ->label('Kaucja')
                                                    ->numeric()
                                                    ->suffix('zł')
                                                    ->default(0)
                                                    ->step(0.01)
                                                    ->live(onBlur: true)
                                                    ->formatStateUsing(fn ($state) => $state ? number_format((float)$state, 2, '.', '') : '0.00')
                                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                                        if ($state !== null && $state !== '') {
                                                            $formattedCost = number_format((float)$state, 2, '.', '');
                                                            $set('deposit', $formattedCost);
                                                        } else {
                                                            $set('deposit', '0.00');
                                                        }
                                                        self::updateSummaryFields($get, $set);
                                                    }),
                                            ]),
                                    ])
                                    ->compact(),

                                Section::make('Produkty w wynajmie')
                                    ->schema([
                                        Repeater::make('products')
                                            ->relationship('products')
                                            ->schema([
                                                Select::make('product_id')
                                                    ->label('Produkt')
                                                    ->options(Product::pluck('name', 'id'))
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                        if ($product = Product::find($state)) {
                                                            $pricePerDay = number_format((float) $product->price_per_day, 2, '.', '');
                                                            $set('price_per_day', $pricePerDay);
                                                            
                                                            $qty = (float) ($get('quantity') ?? 1);
                                                            $totalPrice = number_format($qty * (float) $product->price_per_day, 2, '.', '');
                                                            $set('total_price', $totalPrice);
                                                        }
                                                        self::updateSummaryFields($get, $set);
                                                    }),

                                                TextInput::make('quantity')
                                                    ->label('Ilość')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->minValue(1)
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->formatStateUsing(function ($state) {
                                                        if (!$state) return '1';
                                                        $value = (float) $state;
                                                        if ($value == (int) $value) {
                                                            return (string) (int) $value;
                                                        }
                                                        return rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
                                                    })
                                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                                        $qty = (float) $state;
                                                        $price = (float) str_replace(',', '.', $get('price_per_day') ?? 0);
                                                        $totalPrice = number_format($qty * $price, 2, '.', '');
                                                        $set('total_price', $totalPrice);
                                                        self::updateSummaryFields($get, $set);
                                                    }),

                                                TextInput::make('price_per_day')
                                                    ->label('Cena za dobę/szt.')
                                                    ->numeric()
                                                    ->suffix('zł')
                                                    ->required()
                                                    ->readOnly()
                                                    ->step(0.01),

                                                TextInput::make('total_price')
                                                    ->label('Wartość razem')
                                                    ->numeric()
                                                    ->suffix('zł')
                                                    ->readOnly()
                                                    ->step(0.01),
                                            ])
                                            ->addActionLabel('Dodaj produkt')
                                            ->live()
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateSummaryFields($get, $set))
                                            ->columnSpanFull()
                                            ->columns(4)
                                            ->itemLabel(function (array $state): ?string {
                                                $productName = '';
                                                if (!empty($state['product_id'])) {
                                                    $product = Product::find($state['product_id']);
                                                    $productName = $product?->name ?? 'Nieznany produkt';
                                                }
                                                $quantity = $state['quantity'] ?? 1;
                                                $total = isset($state['total_price']) ? number_format((float)$state['total_price'], 2, ',', '') : '0,00';
                                                
                                                return $productName ? "{$productName} (x{$quantity}) - {$total} zł" : 'Nowy produkt';
                                            }),
                                    ]),
                            ]),

                        // Tab 5: Podsumowanie
                        Tabs\Tab::make('Podsumowanie')
                            ->icon('heroicon-o-calculator')
                            ->schema([
                                Section::make('Kalkulacja kosztów')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('summary_products_per_day')
                                                    ->label('Produkty za dobę')
                                                    ->readOnly()
                                                    ->suffix('zł')
                                                    ->live()
                                                    ->formatStateUsing(function ($state, Get $get) {
                                                        if (empty($state)) {
                                                            $total = self::calculateProductsPerDay($get);
                                                            return number_format($total, 2, '.', '');
                                                        }
                                                        return number_format((float)$state, 2, '.', '');
                                                    }),

                                                TextInput::make('summary_products_total')
                                                    ->label('Produkty za okres')
                                                    ->readOnly()
                                                    ->suffix('zł')
                                                    ->live()
                                                    ->formatStateUsing(function ($state, Get $get) {
                                                        if (empty($state)) {
                                                            $perDay = self::calculateProductsPerDay($get);
                                                            $days = (int) ($get('rental_days') ?? 1);
                                                            $total = $perDay * $days;
                                                            return number_format($total, 2, '.', '');
                                                        }
                                                        return number_format((float)$state, 2, '.', '');
                                                    }),

                                                TextInput::make('summary_delivery')
                                                    ->label('Dostawa i odbiór')
                                                    ->readOnly()
                                                    ->suffix('zł')
                                                    ->live()
                                                    ->formatStateUsing(function ($state, Get $get) {
                                                        if (empty($state)) {
                                                            $delivery = (float) str_replace(',', '.', $get('delivery_cost') ?? 0);
                                                            $pickup = (float) str_replace(',', '.', $get('pickup_cost') ?? 0);
                                                            $total = $delivery + $pickup;
                                                            return number_format($total, 2, '.', '');
                                                        }
                                                        return number_format((float)$state, 2, '.', '');
                                                    }),

                                                TextInput::make('summary_deposit')
                                                    ->label('Kaucja')
                                                    ->readOnly()
                                                    ->suffix('zł')
                                                    ->live()
                                                    ->formatStateUsing(function ($state, Get $get) {
                                                        if (empty($state)) {
                                                            $deposit = (float) str_replace(',', '.', $get('deposit') ?? 0);
                                                            return number_format($deposit, 2, '.', '');
                                                        }
                                                        return number_format((float)$state, 2, '.', '');
                                                    }),
                                            ]),

                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('vat_rate')
                                                    ->label('Stawka VAT (%)')
                                                    ->numeric()
                                                    ->default(23)
                                                    ->minValue(0)
                                                    ->maxValue(99)
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::updateSummaryFields($get, $set)),

                                                TextInput::make('summary_total')
                                                    ->label('RAZEM DO ZAPŁATY')
                                                    ->readOnly()
                                                    ->suffix('zł')
                                                    ->extraInputAttributes(['class' => 'text-lg font-bold text-primary-600'])
                                                    ->live()
                                                    ->formatStateUsing(function ($state, Get $get) {
                                                        if (empty($state)) {
                                                            $total = self::calculateGrandTotal($get);
                                                            return number_format($total, 2, '.', '');
                                                        }
                                                        return number_format((float)$state, 2, '.', '');
                                                    }),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function calculateDays(Get $get, Set $set): void
    {
        $rentalDate = $get('rental_date');
        $returnDate = $get('expected_return_date');
        
        if ($rentalDate && $returnDate) {
            $days = Carbon::parse($rentalDate)->diffInDays(Carbon::parse($returnDate));
            $set('rental_days', max($days, 1));
            self::updateSummaryFields($get, $set);
        }
    }

    private static function calculateProductsPerDay(Get $get): float
    {
        $products = collect($get('products') ?? []);
        return $products->sum(fn ($item) => (float) str_replace(',', '.', $item['total_price'] ?? 0));
    }

    private static function calculateGrandTotal(Get $get): float
    {
        $productsPerDay = self::calculateProductsPerDay($get);
        $days = (int) ($get('rental_days') ?? 1);
        $products = $productsPerDay * $days;
        
        $delivery = (float) str_replace(',', '.', $get('delivery_cost') ?? 0);
        $pickup = (float) str_replace(',', '.', $get('pickup_cost') ?? 0);
        
        // Kaucja NIE jest dodawana do sumy - to zabezpieczenie, nie koszt!
        $net = $products + $delivery + $pickup;
        $vatRate = (float) ($get('vat_rate') ?? 23);
        $vat = $net * ($vatRate / 100);
        
        return $net + $vat;
    }

    private static function updateSummaryFields(Get $get, Set $set): void
    {
        // Produkty za dobę
        $productsPerDay = self::calculateProductsPerDay($get);
        $set('summary_products_per_day', number_format($productsPerDay, 2, '.', ''));
        
        // Produkty za okres
        $days = (int) ($get('rental_days') ?? 1);
        $productsTotal = $productsPerDay * $days;
        $set('summary_products_total', number_format($productsTotal, 2, '.', ''));
        
        // Dostawa i odbiór
        $delivery = (float) str_replace(',', '.', $get('delivery_cost') ?? 0);
        $pickup = (float) str_replace(',', '.', $get('pickup_cost') ?? 0);
        $deliveryTotal = $delivery + $pickup;
        $set('summary_delivery', number_format($deliveryTotal, 2, '.', ''));
        
        // Kaucja
        $deposit = (float) str_replace(',', '.', $get('deposit') ?? 0);
        $set('summary_deposit', number_format($deposit, 2, '.', ''));
        
        // Suma końcowa
        $total = self::calculateGrandTotal($get);
        $set('summary_total', number_format($total, 2, '.', ''));
    }

    public static function updateSummary(Get $get, Set $set): void
    {
        self::updateSummaryFields($get, $set);
    }
}
