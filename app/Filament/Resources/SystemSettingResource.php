<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\SystemSetting;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SystemSettingResource\Pages;
use App\Filament\Resources\SystemSettingResource\RelationManagers;

class SystemSettingResource extends Resource
{
    protected static ?string $model = SystemSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Settings')
                    ->schema([
                        Forms\Components\FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->directory('settings')
                            ->nullable(),
                        Forms\Components\FileUpload::make('favicon')
                            ->label('Favicon')
                            ->image()
                            ->directory('settings')
                            ->nullable(),
                        Forms\Components\TextInput::make('copyright')
                            ->label('Copyright Text')
                            ->maxLength(255)
                            ->nullable(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\Textarea::make('address')
                            ->label('Address')
                            ->maxLength(65535)
                            ->nullable(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Localization')
                    ->schema([
                        Forms\Components\Select::make('timezone')
                            ->label('Timezone')
                            ->options(\DateTimeZone::listIdentifiers())
                            ->default('UTC')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('currency')
                            ->label('Currency')
                            ->options([
                                'USD' => 'US Dollar (USD)',
                                'EUR' => 'Euro (EUR)',
                                'GBP' => 'British Pound (GBP)',
                                // Add more currencies as needed
                            ])
                            ->default('USD')
                            ->searchable()
                            ->required(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('SEO Settings')
                    ->schema([
                        Forms\Components\Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->maxLength(65535)
                            ->nullable(),
                        Forms\Components\TextInput::make('meta_keywords')
                            ->label('Meta Keywords')
                            ->maxLength(255)
                            ->nullable(),
                    ])
                    ->columns(1),
                Forms\Components\Section::make('Social Media Links')
                    ->schema([
                        Forms\Components\TextInput::make('social_facebook')
                            ->label('Facebook')
                            ->url()
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\TextInput::make('social_twitter')
                            ->label('Twitter')
                            ->url()
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\TextInput::make('social_linkedin')
                            ->label('LinkedIn')
                            ->url()
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\TextInput::make('social_instagram')
                            ->label('Instagram')
                            ->url()
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\TextInput::make('social_youtube')
                            ->label('YouTube')
                            ->url()
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\TextInput::make('social_whatsapp')
                            ->label('WhatsApp')
                            ->url()
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\TextInput::make('social_telegram')
                            ->label('Telegram')
                            ->url()
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\TextInput::make('social_github')
                            ->label('GitHub')
                            ->url()
                            ->maxLength(255)
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png')),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Address')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->address),
                Tables\Columns\TextColumn::make('timezone')
                    ->label('Timezone')
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')
                    ->label('Currency')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('timezone')
                    ->label('Timezone')
                    ->options(\DateTimeZone::listIdentifiers())
                    ->searchable(),
                Tables\Filters\SelectFilter::make('currency')
                    ->label('Currency')
                    ->options([
                        'USD' => 'US Dollar (USD)',
                        'EUR' => 'Euro (EUR)',
                        'GBP' => 'British Pound (GBP)',
                        // Add more currencies as needed
                    ])
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Update'),
                Tables\Actions\ViewAction::make(),
                // DeleteAction omitted to prevent deletion
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Bulk delete omitted to prevent deletion
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Add relation managers if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystemSettings::route('/'),
            'create' => Pages\CreateSystemSetting::route('/create'),
            'edit' => Pages\EditSystemSetting::route('/{record}/edit'),
        ];
    }
    //delete false to prevent deletion of system settings
    public static function canDeleteAny(): bool
    {
        return false;
    }
    public static function canCreate(): bool
    {
        return false;
    }
}


