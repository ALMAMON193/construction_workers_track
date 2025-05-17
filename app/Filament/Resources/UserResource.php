<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('hourly_working_rate')
                    ->numeric()
                    ->required()
                    ->prefix('$')
                    ->placeholder('0.00')
                    ->label('Hourly Rate'),

                Forms\Components\TextInput::make('hourly_working_rate_vat')
                    ->numeric()
                    ->required()
                    ->suffix('%')
                    ->placeholder('0')
                    ->label('VAT Percentage'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_use_storage'),
                TextColumn::make('working_days')->default('0'),
                TextColumn::make('hourly_working_rate')
                    ->money('USD')
                    ->searchable()
                    ->sortable()
                    ->label('Hourly Rate ($)'),
                TextColumn::make('hourly_working_rate_vat')
                    ->formatStateUsing(fn ($state) => $state.'%')
                    ->searchable()
                    ->sortable()
                    ->label('VAT %'),
                TextColumn::make('employee_id'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('updateSelectedEmployeeRates')
                        ->label('Set Rates for Selected Employees')
                        ->icon('heroicon-o-pencil')
                        ->form([
                            Forms\Components\TextInput::make('hourly_working_rate')
                                ->numeric()
                                ->required()
                                ->prefix('$')
                                ->placeholder('0.00')
                                ->label('Hourly Rate'),

                            Forms\Components\TextInput::make('hourly_working_rate_vat')
                                ->numeric()
                                ->required()
                                ->suffix('%')
                                ->placeholder('0')
                                ->label('VAT Percentage'),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                if ($record->role === 'employee') {
                                    $record->update([
                                        'hourly_working_rate' => $data['hourly_working_rate'],
                                        'hourly_working_rate_vat' => $data['hourly_working_rate_vat']
                                    ]);
                                }
                            }
                        }),
                ]),
            ])
            ->headerActions([
                Action::make('updateAllEmployeeRates')
                    ->label('Set Rates for All Employees')
                    ->icon('heroicon-o-users')
                    ->form([
                        Forms\Components\TextInput::make('hourly_working_rate')
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->placeholder('0.00')
                            ->label('Hourly Rate'),

                        Forms\Components\TextInput::make('hourly_working_rate_vat')
                            ->numeric()
                            ->required()
                            ->suffix('%')
                            ->placeholder('0')
                            ->label('VAT Percentage'),
                    ])
                    ->action(function (array $data) {
                        User::where('role', 'employee')->update([
                            'hourly_working_rate' => $data['hourly_working_rate'],
                            'hourly_working_rate_vat' => $data['hourly_working_rate_vat']
                        ]);
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
