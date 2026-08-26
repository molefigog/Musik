<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\TaskResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $existingFile = $this->record->file_path;
        $existingPreview = $this->record->preview_path;

        $hasNewFile = !empty($data['file_path']) && $data['file_path'] !== $existingFile;
        $hasNewPreview = !empty($data['preview_path']) && $data['preview_path'] !== $existingPreview;

        if ($hasNewFile || $hasNewPreview) {
            $data['status'] = true;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
