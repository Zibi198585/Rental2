<?php

namespace App\Filament\Resources\RentalReturns\Pages;

use App\Filament\Resources\RentalReturns\RentalReturnResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRentalReturns extends ListRecords
{
    protected static string $resource = RentalReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
