<?php

namespace App\Filament\Resources\RentalIssues\Pages;

use App\Filament\Resources\RentalIssues\RentalIssueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRentalIssues extends ListRecords
{
    protected static string $resource = RentalIssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
