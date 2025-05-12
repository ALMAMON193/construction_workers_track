<?php

namespace App\Filament\Resources\ContactDetailsResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ContactDetailsResource;

class ListContactDetails extends ListRecords
{
    protected static string $resource = ContactDetailsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
