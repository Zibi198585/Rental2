<?php

namespace App\Filament\Resources\RentalDocuments\Pages;

use App\Filament\Resources\RentalDocuments\RentalDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRentalDocument extends EditRecord
{
    protected static string $resource = RentalDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Formatuj kwoty w produktach dla wyświetlania
        if (isset($data['products'])) {
            foreach ($data['products'] as &$product) {
                if (isset($product['price_per_day'])) {
                    $product['price_per_day'] = number_format((float)$product['price_per_day'], 2, '.', '');
                }
                if (isset($product['total_price'])) {
                    $product['total_price'] = number_format((float)$product['total_price'], 2, '.', '');
                }
            }
        }

        // Formatuj inne kwoty
        if (isset($data['delivery_cost'])) {
            $data['delivery_cost'] = number_format((float)$data['delivery_cost'], 2, '.', '');
        }
        if (isset($data['pickup_cost'])) {
            $data['pickup_cost'] = number_format((float)$data['pickup_cost'], 2, '.', '');
        }
        if (isset($data['deposit'])) {
            $data['deposit'] = number_format((float)$data['deposit'], 2, '.', '');
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Konwertuj z powrotem na float przed zapisem
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

        // Konwertuj inne kwoty
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
