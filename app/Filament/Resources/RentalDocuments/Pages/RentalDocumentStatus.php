<?php

namespace App\Filament\Resources\RentalDocuments\Pages;

use App\Filament\Resources\RentalDocuments\RentalDocumentResource;
use App\Services\RentalDocumentStatusService;
use App\Models\RentalDocument;
use Filament\Resources\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;

class RentalDocumentStatus extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = RentalDocumentResource::class;
    
    protected static ?string $navigationLabel = 'Status Wydań';
    
    protected static ?string $title = 'Status Wydań i Zwrotów';

    protected string $view = 'filament.resources.rental-documents.pages.rental-document-status';

    private RentalDocumentStatusService $statusService;

    public function __construct()
    {
        $this->statusService = new RentalDocumentStatusService();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                RentalDocument::query()
                    ->whereIn('status', ['rented', 'partially_returned', 'scheduled_return'])
                    ->with(['products.product', 'rentalIssues.products'])
            )
            ->columns([
                TextColumn::make('agreement_number')
                    ->label('Numer umowy')
                    ->weight(FontWeight::Bold)
                    ->searchable(),

                TextColumn::make('contractor_full_name')
                    ->label('Klient')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('rental_period_info')
                    ->label('Okres wynajmu')
                    ->html(),

                TextColumn::make('products_status')
                    ->label('Status produktów')
                    ->formatStateUsing(function ($record) {
                        $status = $this->statusService->getDocumentStatus($record);
                        $summary = $status['summary'];
                        
                        return sprintf(
                            "Zaplanowano: %d<br/>Wydano: %d (%s%%)<br/>Zwrócono: %d (%s%%)<br/>W obiegu: %d<br/>Pozostało do wydania: %d",
                            $summary['total_planned'],
                            $summary['total_issued'],
                            $summary['completion_percentage'],
                            $summary['total_returned'],
                            $summary['return_percentage'],
                            $summary['total_in_circulation'],
                            $summary['total_remaining_to_issue']
                        );
                    })
                    ->html(),

                TextColumn::make('status_badge')
                    ->label('Status ogólny')
                    ->badge()
                    ->formatStateUsing(function ($record) {
                        $status = $this->statusService->getDocumentStatus($record);
                        $summary = $status['summary'];
                        
                        if ($summary['total_remaining_to_issue'] > 0) {
                            return 'Nieukończone wydania';
                        } elseif ($summary['total_in_circulation'] > 0) {
                            return 'W obiegu';
                        } else {
                            return 'Zakończone';
                        }
                    })
                    ->color(function ($record) {
                        $status = $this->statusService->getDocumentStatus($record);
                        $summary = $status['summary'];
                        
                        if ($summary['total_remaining_to_issue'] > 0) {
                            return 'warning';
                        } elseif ($summary['total_in_circulation'] > 0) {
                            return 'info';
                        } else {
                            return 'success';
                        }
                    }),

                TextColumn::make('issues_count')
                    ->label('Liczba wydań')
                    ->formatStateUsing(function ($record) {
                        return $record->rentalIssues()->count();
                    }),

                TextColumn::make('returns_count')
                    ->label('Liczba zwrotów')
                    ->formatStateUsing(function ($record) {
                        $status = $this->statusService->getDocumentStatus($record);
                        return $status['returns']->count();
                    }),
            ])
            ->defaultSort('rental_date', 'desc')
            ->striped();
    }

    public function render(): View
    {
        return view($this->view, [
            'statusService' => $this->statusService
        ]);
    }
}
