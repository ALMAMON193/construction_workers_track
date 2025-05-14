<?php

namespace App\Filament\Resources\FacingProblemResource\Pages;

use App\Filament\Resources\FacingProblemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFacingProblem extends EditRecord
{
    protected static string $resource = FacingProblemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
