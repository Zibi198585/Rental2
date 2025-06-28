<?php

namespace App\Filament\Resources\RentalDocuments\Pages;

use App\Filament\Resources\RentalDocuments\RentalDocumentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRentalDocument extends ViewRecord
{
    protected static string $resource = RentalDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
