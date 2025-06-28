<?php

namespace App\Filament\Resources\RentalDocuments\Pages;

use App\Filament\Resources\RentalDocuments\RentalDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRentalDocuments extends ListRecords
{
    protected static string $resource = RentalDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
