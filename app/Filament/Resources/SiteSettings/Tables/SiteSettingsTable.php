<?php

namespace App\Filament\Resources\SiteSettings\Tables;

use App\Models\SiteSetting;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('setting_group')
                    ->label('Kategori')
                    ->state(fn (SiteSetting $record): string => SiteSetting::groupFor($record->key))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Branding' => 'primary',
                        'Beranda' => 'success',
                        'Kontak' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('setting_label')
                    ->label('Parameter')
                    ->state(fn (SiteSetting $record): string => SiteSetting::labelFor($record->key))
                    ->description(fn (SiteSetting $record): string => $record->key)
                    ->searchable(['key'])
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('key', $direction)),
                TextColumn::make('value')
                    ->label('Nilai')
                    ->limit(90)
                    ->wrap()
                    ->placeholder('-'),
            ])
            ->defaultSort('key')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning'),
                DeleteAction::make()
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(fn (SiteSetting $record): bool => ! SiteSetting::isSystemKey($record->key)),
            ]);
    }
}
