<?php

namespace App\Filament\Resources\Contacts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContactsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->description('Kontak utama tampil di footer dan CTA. Semua kontak aktif tampil berurutan di halaman kontak publik.')
            ->columns([
                TextColumn::make('address')->label('Alamat')->limit(60),
                TextColumn::make('whatsapp')->label('WhatsApp')->searchable(),
                TextColumn::make('email')->label('Email')->searchable()->visibleFrom('md'),
                TextColumn::make('instagram')->label('Instagram')->visibleFrom('lg')->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_primary')->label('Utama')->boolean(),
                IconColumn::make('is_active')->label('Tampil')->boolean(),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->orderByDesc('is_primary')
                ->latest('updated_at')
                ->latest('id'))
            ->filters([
                //
            ])
            ->emptyStateHeading('Belum ada kontak')
            ->emptyStateDescription('Tambahkan kontak lalu aktifkan. Tandai satu kontak sebagai utama untuk footer dan CTA.')
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning'),
                DeleteAction::make()
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->color('danger'),
            ], position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus terpilih'),
                ]),
            ])
            ->stackedOnMobile();
    }
}
