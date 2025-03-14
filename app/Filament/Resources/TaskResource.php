<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'title')
                    ->required()
                    ->searchable(),
                    
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                    
                Forms\Components\DatePicker::make('due_date')
                    ->required(),
                    
                Forms\Components\Select::make('priority')
                    ->options([
                        'Must be done' => 'Must be done',
                        'Important' => 'Important',
                        'Good to have' => 'Good to have',
                    ])
                    ->required(),
                    
                Forms\Components\Select::make('status')
                    ->options([
                        'To Do' => 'To Do',
                        'In Progress' => 'In Progress',
                        'Under Review' => 'Under Review',
                        'Completed' => 'Completed',
                    ])
                    ->required(),
                    
                Forms\Components\Select::make('assignee_id')
                    ->relationship('assignee', 'name')
                    ->nullable()
                    ->searchable(),
                    
                Forms\Components\FileUpload::make('attachments')
                    ->multiple()
                    ->directory('task-attachments')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => 
                        match ($state) {
                            'Must be done' => 'danger',
                            'Important' => 'warning',
                            'Good to have' => 'success',
                            default => 'gray',
                        }
                    ),
                    
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => 
                        match ($state) {
                            'To Do' => 'gray',
                            'In Progress' => 'warning',
                            'Under Review' => 'info',
                            'Completed' => 'success',
                            default => 'gray',
                        }
                    ),
                    
                Tables\Columns\TextColumn::make('assignee.name')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('priority'),
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('project')
                    ->relationship('project', 'title'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}