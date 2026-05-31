<?php

namespace App\Filament\Resources\CarResource\Pages;

use App\Filament\Resources\CarResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCar extends EditRecord
{
    protected static string $resource = CarResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $types = ['thumbnail', 'front', 'back', 'left', 'right', 'interior_front', 'interior_back', 'trunk'];
        
        // Get existing image types
        $existingTypes = collect($data['images'] ?? [])->pluck('image_type')->toArray();

        foreach ($types as $type) {
            if (!in_array($type, $existingTypes)) {
                // Add a blank placeholder for missing types
                $data['images'][] = [
                    'image_type' => $type,
                    'path' => null,
                ];
            }
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
