<?php

namespace App\Filament\Resources\SiteSettings\Schemas;

use App\Models\SiteSetting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class SiteSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pengaturan Website')
                    ->description('Kelola parameter yang dipakai halaman publik tanpa perlu mengubah kode.')
                    ->schema([
                        Select::make('key')
                            ->label('Parameter')
                            ->options(fn (): array => SiteSetting::optionLabels())
                            ->getSearchResultsUsing(function (string $search): array {
                                $systemOptions = collect(SiteSetting::optionLabels())
                                    ->filter(fn (string $label, string $key): bool => str_contains(strtolower($label . ' ' . $key), strtolower($search)));

                                return $systemOptions
                                    ->when(
                                        filled($search),
                                        fn ($options) => $options->put($search, 'Advanced - ' . $search),
                                    )
                                    ->all();
                            })
                            ->getOptionLabelUsing(fn (?string $value): ?string => filled($value)
                                ? (SiteSetting::isSystemKey($value) ? SiteSetting::optionLabels()[$value] : 'Advanced - ' . $value)
                                : null)
                            ->searchable()
                            ->live()
                            ->helperText(fn (?string $state): string => SiteSetting::helperFor($state))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->rules(['max:255']),
                        Textarea::make('value')
                            ->label(fn (Get $get): string => 'Nilai ' . SiteSetting::labelFor($get('key')))
                            ->helperText(fn (Get $get): string => SiteSetting::helperFor($get('key')))
                            ->placeholder(fn (Get $get): string => SiteSetting::definitionFor($get('key'))['placeholder'])
                            ->rows(fn (Get $get): int => SiteSetting::definitionFor($get('key'))['rows'])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
