<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\FacingProblem;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\FacingProblemResource\Pages;
use App\Filament\Resources\FacingProblemResource\RelationManagers;

class FacingProblemResource extends Resource
{
    protected static ?string $model = FacingProblem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                Forms\Components\DatePicker::make('date')
                    ->required(),
                Forms\Components\TextInput::make('location')
                    ->required()
                    ->maxLength(255),
                Forms\Components\RichEditor::make('description')
                    ->required()
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'solve' => 'Solved',
                        'cancel' => 'Canceled',
                    ])
                    ->required(),
                Textarea::make('feedback')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('user.name')->label('User'),
                TextColumn::make('date')->sortable(),
                TextColumn::make('location'),
                TextColumn::make('description')
                    ->limit(50)
                    ->formatStateUsing(function ($state) {
                        $plainText = strip_tags(Str::markdown($state));
                        return Str::words($plainText, 50, '...');
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'solve' => 'success',
                        'cancel' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('feedback')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('changeStatus')
                    ->form([
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'solve' => 'Solved',
                                'cancel' => 'Canceled',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state === 'pending') {
                                    $set('feedback', null);
                                }
                            }),
                        Textarea::make('feedback')
                            ->label('Feedback')
                            ->required(fn(Forms\Get $get): bool => in_array($get('status'), ['solve', 'cancel']))
                            ->hidden(fn(Forms\Get $get): bool => $get('status') === 'pending')
                            ->columnSpanFull(),
                    ])
                    ->action(function (FacingProblem $record, array $data) {
                        $record->update([
                            'status' => $data['status'],
                            'feedback' => $data['status'] === 'pending' ? null : $data['feedback']
                        ]);

                        Notification::make()
                            ->title('Status updated successfully')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Update Problem Status')
                    ->modalSubmitActionLabel('Update')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFacingProblems::route('/'),
            'create' => Pages\CreateFacingProblem::route('/create'),
            'edit' => Pages\EditFacingProblem::route('/{record}/edit'),
        ];
    }
}
