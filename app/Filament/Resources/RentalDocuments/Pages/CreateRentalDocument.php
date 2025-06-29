<?php

namespace App\Filament\Resources\RentalDocuments\Pages;

use App\Filament\Resources\RentalDocuments\RentalDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRentalDocument extends CreateRecord
{
    protected static string $resource = RentalDocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Konwertuj na float przed zapisem
        if (isset($data['products'])) {
            foreach ($data['products'] as &$product) {
                if (isset($product['price_per_day'])) {
                    $product['price_per_day'] = (float) str_replace(',', '.', $product['price_per_day']);
                }
                if (isset($product['total_price'])) {
                    $product['total_price'] = (float) str_replace(',', '.', $product['total_price']);
                }
            }
        }

        if (isset($data['delivery_cost'])) {
            $data['delivery_cost'] = (float) str_replace(',', '.', $data['delivery_cost']);
        }
        if (isset($data['pickup_cost'])) {
            $data['pickup_cost'] = (float) str_replace(',', '.', $data['pickup_cost']);
        }
        if (isset($data['deposit'])) {
            $data['deposit'] = (float) str_replace(',', '.', $data['deposit']);
        }

        return $data;
    }
}
