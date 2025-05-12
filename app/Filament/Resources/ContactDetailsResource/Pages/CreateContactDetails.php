<?php

namespace App\Filament\Resources\ContactDetailsResource\Pages;

use App\Filament\Resources\ContactDetailsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateContactDetails extends CreateRecord
{
    protected static string $resource = ContactDetailsResource::class;
}
