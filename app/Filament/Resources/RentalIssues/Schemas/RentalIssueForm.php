<?php

namespace App\Filament\Resources\RentalIssues\Schemas;

use App\Models\Product;
use App\Models\RentalDocument;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;



class RentalIssueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Formularz wydania')
                    ->tabs([
                        // Tab 1: Podstawowe informacje
                        Tabs\Tab::make('Podstawowe')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('issue_number')
                                            ->label('Numer wydania')
                                            ->placeholder('Wygeneruje się automatycznie')
                                            ->prefixIcon('heroicon-o-hashtag')
                                            ->maxLength(255)
                                            ->readOnly(),

                                        Select::make('status')
                                            ->label('Status')
                                            ->options([
                                                'draft' => 'Szkic',
                                                'issued' => 'Wydane',
                                                'partially_returned' => 'Częściowo zwrócone',
                                                'fully_returned' => 'Całkowicie zwrócone',
                                                'cancelled' => 'Anulowane',
                                            ])
                                            ->default('draft')
                                            ->required()
                                            ->native(false)
                                            ->live(),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        DatePicker::make('issue_date')
                                            ->label('Data wydania')
                                            ->required()
                                            ->default(now())
                                            ->native(false),

                                        Select::make('rental_document_id')
                                            ->label('Powiązana umowa')
                                            ->relationship(
                                                'rentalDocument',
                                                'agreement_number',
                                                fn ($query) => $query->whereNotNull('agreement_number')
                                            )
                                            ->getOptionLabelFromRecordUsing(fn ($record) => 
                                                ($record->agreement_number ?? 'Umowa bez numeru') . 
                                                ' - ' . 
                                                ($record->contractor_full_name ?? 'Nieznany klient')
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->nullable()
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                if ($state) {
                                                    $document = RentalDocument::find($state);
                                                    if ($document) {
                                                        $set('customer_name', $document->contractor_full_name);
                                                        $set('customer_phone', $document->contact_phone);
                                                        $set('customer_email', $document->contact_email);
                                                        $set('delivery_address', $document->equipment_location);
                                                    }
                                                }
                                            }),
                                    ]),

                                Textarea::make('notes')
                                    ->label('Notatki')
                                    ->placeholder('Dodatkowe informacje o wydaniu...')
                                    ->columnSpanFull()
                                    ->rows(3),
                            ]),

                        // Tab 2: Dane klienta
                        Tabs\Tab::make('Klient')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make('Dane klienta')
                                    ->description('Informacje o kliencie odbierającym sprzęt')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('customer_name')
                                                    ->label('Nazwa klienta')
                                                    ->placeholder('Nazwa firmy lub imię nazwisko')
                                                    ->maxLength(255),

                                                TextInput::make('customer_phone')
                                                    ->label('Telefon')
                                                    ->placeholder('+48 000 000 000')
                                                    ->tel()
                                                    ->maxLength(255),
                                            ]),

                                        TextInput::make('customer_email')
                                            ->label('Email')
                                            ->placeholder('email@example.com')
                                            ->email()
                                            ->maxLength(255),

                                        Textarea::make('customer_address')
                                            ->label('Adres klienta')
                                            ->placeholder('Pełny adres klienta...')
                                            ->columnSpanFull()
                                            ->rows(3),
                                    ])
                                    ->compact(),
                            ]),

                        // Tab 3: Dostawa
                        Tabs\Tab::make('Dostawa')
                            ->icon('heroicon-o-truck')
                            ->schema([
                                Section::make('Informacje o dostawie')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('transport_cost')
                                                    ->label('Koszt transportu (PLN)')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->minValue(0)
                                                    ->step(0.01)
                                                    ->live(),

                                                Toggle::make('transport_included')
                                                    ->label('Transport uwzględniony')
                                                    ->default(false)
                                                    ->helperText('Czy koszt transportu jest wliczony w cenę'),

                                                Select::make('transport_type')
                                                    ->label('Typ transportu')
                                                    ->options([
                                                        'pickup' => 'Odbiór własny',
                                                        'delivery' => 'Dostawa',
                                                        'courier' => 'Kurier',
                                                    ])
                                                    ->default('delivery'),
                                            ]),

                                        Textarea::make('delivery_address')
                                            ->label('Adres dostawy')
                                            ->placeholder('Dokładny adres dostawy sprzętu...')
                                            ->columnSpanFull()
                                            ->rows(3),

                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('delivery_contact_person')
                                                    ->label('Osoba kontaktowa')
                                                    ->placeholder('Imię i nazwisko osoby odbierającej'),

                                                TextInput::make('delivery_contact_phone')
                                                    ->label('Telefon kontaktowy')
                                                    ->placeholder('+48 000 000 000')
                                                    ->tel(),
                                            ]),

                                        Textarea::make('transport_notes')
                                            ->label('Notatki transportowe')
                                            ->placeholder('Uwagi dotyczące transportu...')
                                            ->columnSpanFull()
                                            ->rows(3),
                                    ])
                                    ->compact(),
                            ]),

                        // Tab 4: Produkty
                        Tabs\Tab::make('Produkty')
                            ->icon('heroicon-o-rectangle-stack')
                            ->schema([
                                Section::make('Status wydań dla umowy')
                                    ->description('Przegląd tego co zostało już wydane i co pozostało do wydania')
                                    ->schema([
                                        \Filament\Forms\Components\Placeholder::make('rental_status_table')
                                            ->label('')
                                            ->content(function (Get $get) {
                                                $documentId = $get('rental_document_id');
                                                if (!$documentId) {
                                                    return 'Wybierz umowę wynajmu aby zobaczyć status wydań';
                                                }
                                                
                                                $document = RentalDocument::with(['products.product', 'rentalIssues.products'])->find($documentId);
                                                if (!$document) {
                                                    return 'Nie znaleziono wybranej umowy';
                                                }
                                                
                                                $service = new \App\Services\RentalDocumentStatusService();
                                                $status = $service->getDocumentStatus($document);
                                                
                                                // Bardzo prosty HTML - bez skomplikowanych stylów
                                                $html = '<style>
                                                .status-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                                                .status-table th, .status-table td { border: 1px solid #ddd; padding: 12px; text-align: center; }
                                                .status-table th { background-color: #f5f5f5; font-weight: bold; }
                                                .status-table td:first-child { text-align: left; }
                                                .bg-orange { background-color: #fef3cd; }
                                                .bg-blue { background-color: #d1ecf1; }
                                                .bg-green { background-color: #d4edda; }
                                                .bg-red { background-color: #f8d7da; }
                                                .text-blue { color: #0c5460; font-weight: bold; }
                                                .text-green { color: #155724; font-weight: bold; }
                                                .text-orange { color: #856404; font-weight: bold; }
                                                .badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; }
                                                .badge-orange { background-color: #ffeaa7; color: #6c5ce7; }
                                                .badge-blue { background-color: #74b9ff; color: white; }
                                                .badge-green { background-color: #00b894; color: white; }
                                                .badge-red { background-color: #e17055; color: white; }
                                                .summary-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; margin-top: 20px; }
                                                .summary-card { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #dee2e6; }
                                                .summary-number { font-size: 24px; font-weight: bold; margin: 5px 0; }
                                                </style>';
                                                
                                                $html .= '<table class="status-table">';
                                                $html .= '<thead>';
                                                $html .= '<tr>';
                                                $html .= '<th>Produkt</th>';
                                                $html .= '<th>Zaplanowano</th>';
                                                $html .= '<th>Wydano</th>';
                                                $html .= '<th>Zwrócono</th>';
                                                $html .= '<th>W obiegu</th>';
                                                $html .= '<th>Do wydania</th>';
                                                $html .= '<th>Status</th>';
                                                $html .= '</tr>';
                                                $html .= '</thead>';
                                                $html .= '<tbody>';

                                                foreach ($status['products'] as $product) {
                                                    $statusLabel = \App\Services\RentalDocumentStatusService::getStatusLabels()[$product['status']] ?? $product['status'];
                                                    
                                                    $rowClass = match($product['status']) {
                                                        'not_issued' => 'bg-orange',
                                                        'partially_issued' => 'bg-blue', 
                                                        'fully_issued' => 'bg-green',
                                                        'extra_issued' => 'bg-red',
                                                        'over_issued' => 'bg-red',
                                                        default => ''
                                                    };
                                                    
                                                    $html .= "<tr class=\"{$rowClass}\">";
                                                    $html .= '<td>' . htmlspecialchars($product['product_name']) . '</td>';
                                                    $html .= '<td>' . $product['planned_quantity'] . ' ' . $product['product_unit'] . '</td>';
                                                    $html .= '<td class="text-blue">' . $product['issued_quantity'] . ' ' . $product['product_unit'] . '</td>';
                                                    $html .= '<td>' . $product['returned_quantity'] . ' ' . $product['product_unit'] . '</td>';
                                                    $html .= '<td class="text-green">' . $product['in_circulation'] . ' ' . $product['product_unit'] . '</td>';
                                                    
                                                    if ($product['remaining_to_issue'] > 0) {
                                                        $html .= '<td class="text-orange">' . $product['remaining_to_issue'] . ' ' . $product['product_unit'] . '</td>';
                                                    } else {
                                                        $html .= '<td>—</td>';
                                                    }
                                                    
                                                    $badgeClass = match($product['status']) {
                                                        'not_issued' => 'badge badge-orange',
                                                        'partially_issued' => 'badge badge-blue', 
                                                        'fully_issued' => 'badge badge-green',
                                                        'extra_issued' => 'badge badge-red',
                                                        'over_issued' => 'badge badge-red',
                                                        default => 'badge'
                                                    };
                                                    
                                                    $html .= '<td><span class="' . $badgeClass . '">' . htmlspecialchars($statusLabel) . '</span></td>';
                                                    $html .= '</tr>';
                                                }
                                                $html .= '</tbody>';
                                                $html .= '</table>';
                                                
                                                // Podsumowanie
                                                $summary = $status['summary'];
                                                $html .= '<div class="summary-grid">';
                                                $html .= '<div class="summary-card">';
                                                $html .= '<div>Zaplanowano</div>';
                                                $html .= '<div class="summary-number">' . $summary['total_planned'] . '</div>';
                                                $html .= '</div>';
                                                $html .= '<div class="summary-card">';
                                                $html .= '<div class="text-blue">Wydano</div>';
                                                $html .= '<div class="summary-number text-blue">' . $summary['total_issued'] . '</div>';
                                                $html .= '</div>';
                                                $html .= '<div class="summary-card">';
                                                $html .= '<div>Zwrócono</div>';
                                                $html .= '<div class="summary-number">' . $summary['total_returned'] . '</div>';
                                                $html .= '</div>';
                                                $html .= '<div class="summary-card">';
                                                $html .= '<div class="text-green">W obiegu</div>';
                                                $html .= '<div class="summary-number text-green">' . $summary['total_in_circulation'] . '</div>';
                                                $html .= '</div>';
                                                $html .= '<div class="summary-card">';
                                                $html .= '<div class="text-orange">Do wydania</div>';
                                                $html .= '<div class="summary-number text-orange">' . $summary['total_remaining_to_issue'] . '</div>';
                                                $html .= '</div>';
                                                $html .= '</div>';
                                                
                                                return new \Illuminate\Support\HtmlString($html);
                                            })
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible()
                                    ->collapsed(fn (Get $get) => !$get('rental_document_id'))
                                    ->compact(),
                                    
                                Section::make('Lista wydawanych produktów')
                                    ->description('Dodaj produkty do wydania')
                                    ->schema([
                                        Repeater::make('products')
                                            ->relationship()
                                            ->schema([
                                                Select::make('product_id')
                                                    ->label('Produkt')
                                                    ->relationship('product', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(function (Set $set, $state) {
                                                        if ($state) {
                                                            $product = Product::find($state);
                                                            if ($product) {
                                                                $set('unit_price', $product->price_per_day);
                                                            }
                                                        }
                                                    })
                                                    ->columnSpan(['default' => 1, 'lg' => 3]),

                                                TextInput::make('quantity')
                                                    ->label('Ilość')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->minValue(1)
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                                        $quantity = (int) $get('quantity');
                                                        $unitPrice = (float) $get('unit_price');
                                                        $set('total_price', $quantity * $unitPrice);
                                                    })
                                                    ->columnSpan(['default' => 1, 'lg' => 1]),

                                                TextInput::make('unit_price')
                                                    ->label('Cena/dzień (PLN)')
                                                    ->numeric()
                                                    ->required()
                                                    ->step(0.01)
                                                    ->live()
                                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                                        $quantity = (int) $get('quantity');
                                                        $unitPrice = (float) $get('unit_price');
                                                        $set('total_price', $quantity * $unitPrice);
                                                    })
                                                    ->columnSpan(['default' => 1, 'lg' => 2]),

                                                TextInput::make('total_price')
                                                    ->label('Razem/dzień (PLN)')
                                                    ->numeric()
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->columnSpan(['default' => 1, 'lg' => 2]),

                                                DatePicker::make('planned_return_date')
                                                    ->label('Planowany zwrot')
                                                    ->native(false)
                                                    ->columnSpan(['default' => 1, 'lg' => 2]),

                                                TextInput::make('planned_rental_days')
                                                    ->label('Planowane dni')
                                                    ->numeric()
                                                    ->minValue(1)
                                                    ->columnSpan(['default' => 1, 'lg' => 2]),

                                                Textarea::make('technical_notes')
                                                    ->label('Notatki techniczne')
                                                    ->placeholder('Uwagi techniczne, stan sprzętu...')
                                                    ->columnSpanFull()
                                                    ->rows(2),

                                                TextInput::make('serial_numbers')
                                                    ->label('Numery seryjne')
                                                    ->placeholder('Wprowadź numery seryjne (oddzielone przecinkami)')
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(['default' => 1, 'lg' => 8])
                                            ->defaultItems(1)
                                            ->addActionLabel('Dodaj produkt')
                                            ->reorderable(false)
                                            ->collapsed(false)
                                            ->itemLabel(function (array $state): ?string {
                                                $productName = '';
                                                if (!empty($state['product_id'])) {
                                                    $product = Product::find($state['product_id']);
                                                    $productName = $product?->name ?? 'Nieznany produkt';
                                                }
                                                $quantity = $state['quantity'] ?? 1;
                                                $total = isset($state['total_price']) ? number_format((float)$state['total_price'], 2, ',', '') : '0,00';
                                                
                                                return $productName ? "{$productName} (x{$quantity}) - {$total} zł/dzień" : 'Nowy produkt';
                                            }),
                                    ]),
                            ]),

                        // Tab 5: Autoryzacja
                        Tabs\Tab::make('Autoryzacja')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Section::make('Podpisy i autoryzacja')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('issued_by')
                                                    ->label('Wydał')
                                                    ->placeholder('Imię i nazwisko wydającego'),

                                                TextInput::make('received_by')
                                                    ->label('Odebrał')
                                                    ->placeholder('Imię i nazwisko odbierającego'),
                                            ]),

                                        DateTimePicker::make('issued_at')
                                            ->label('Data i czas wydania')
                                            ->native(false),
                                    ])
                                    ->compact(),

                                Section::make('Podsumowanie finansowe')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('total_daily_cost')
                                                    ->label('Koszt dzienny (PLN)')
                                                    ->numeric()
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->step(0.01),

                                                TextInput::make('estimated_total_cost')
                                                    ->label('Szacowany koszt całkowity (PLN)')
                                                    ->numeric()
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->step(0.01),
                                            ]),
                                    ])
                                    ->compact(),
                            ]),
                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull(),
            ]);
    }
}
