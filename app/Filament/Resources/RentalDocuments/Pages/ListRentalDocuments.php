<?php

namespace App\Filament\Resources\RentalDocuments\Pages;

use App\Filament\Resources\RentalDocuments\RentalDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListRentalDocuments extends ListRecords
{
    protected static string $resource = RentalDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Wszystkie')
                ->badge(fn () => $this->getResource()::getEloquentQuery()->count()),

            'rented' => Tab::make('Aktywne')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rented'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('status', 'rented')->count())
                ->badgeColor('success'),

            'partially_returned' => Tab::make('Częściowy zwrot')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'partially_returned'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('status', 'partially_returned')->count())
                ->badgeColor('warning'),

            'scheduled_return' => Tab::make('Planowany zwrot')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'scheduled_return'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('status', 'scheduled_return')->count())
                ->badgeColor('info'),

            'returned' => Tab::make('Zwrócone')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'returned'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('status', 'returned')->count())
                ->badgeColor('primary'),

            'draft' => Tab::make('Szkice')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('status', 'draft')->count())
                ->badgeColor('gray'),
        ];
    }
}
