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

    /**
     * Force loading relacji przed wyświetleniem
     */
    protected function resolveRecord($key): \Illuminate\Database\Eloquent\Model
    {
        $record = static::getResource()::resolveRecordRouteBinding($key);
        
        // Force reload relacji
        $record->load([
            'products',
            'products.product'
        ]);
        
        return $record;
    }
}
