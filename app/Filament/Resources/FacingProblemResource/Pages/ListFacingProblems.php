<?php

namespace App\Filament\Resources\FacingProblemResource\Pages;

use App\Filament\Resources\FacingProblemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFacingProblems extends ListRecords
{
    protected static string $resource = FacingProblemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
