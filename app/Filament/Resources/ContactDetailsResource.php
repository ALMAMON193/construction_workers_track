<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactDetailsResource\Pages;
use App\Filament\Resources\ContactDetailsResource\RelationManagers;
use App\Models\ContactDetails;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContactDetailsResource extends Resource
{
    protected static ?string $model = ContactDetails::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('whatsapp')->required()->maxLength(255)->label('WhatsApp'),
                Forms\Components\TextInput::make('facebook')->required()->maxLength(255)->label('Facebook'),
                Forms\Components\TextInput::make('instagram')->required()->maxLength(255)->label('Instagram'),
                Forms\Components\TextInput::make('twitter')->required()->maxLength(255)->label('Twitter'),
                Forms\Components\TextInput::make('service_center')->label('Service Center Contact')->required()->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('whatsapp'),
                Tables\Columns\TextColumn::make('facebook'),
                Tables\Columns\TextColumn::make('instagram'),
                Tables\Columns\TextColumn::make('twitter'),
                Tables\Columns\TextColumn::make('service_center')->label('Service Center Contact'),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactDetails::route('/'),
        ];
    }
}
