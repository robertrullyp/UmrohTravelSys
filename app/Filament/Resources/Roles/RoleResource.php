<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Clusters\Settings;
use App\Filament\Resources\PermissionResource;
use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleResource extends PermissionResource
{
    protected static ?string $model = Role::class;

    protected static ?string $cluster = Settings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'Role / Hak Akses';

    protected static ?string $modelLabel = 'Role';

    protected static ?string $pluralModelLabel = 'Role / Hak Akses';

    protected static ?int $navigationSort = 2;

    protected static function permissionPrefix(): string
    {
        return 'roles';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Role / Hak Akses')
                ->description('Role adalah paket hak akses. Berikan role ke pengguna agar menu dan aksi admin sesuai tugasnya.')
                ->columns([
                    'default' => 1,
                    'md' => 2,
                    'xl' => 4,
                ])
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Role')
                        ->helperText('Contoh: admin, editor-konten, atau finance. Jangan ubah role super-admin.')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->columnSpan([
                            'default' => 'full',
                            'xl' => 1,
                        ]),
                    ...static::permissionChecklistSchema(),
                ])
                ->columnSpanFull(),
        ]);
    }

    /**
     * @return array<int, Fieldset>
     */
    private static function permissionChecklistSchema(): array
    {
        return collect(static::permissionGroups())
            ->filter(fn (array $permissions): bool => static::permissionRecords($permissions)->isNotEmpty())
            ->map(fn (array $permissions, string $group): Fieldset => Fieldset::make($group)
                ->schema([
                    CheckboxList::make('permissions_'.str($group)->slug('_'))
                        ->hiddenLabel()
                        ->helperText(static::permissionGroupHelper($group))
                        ->options(fn (): array => static::permissionOptions($permissions))
                        ->loadStateFromRelationshipsUsing(fn (CheckboxList $component): null => static::loadPermissionGroupState($component, $permissions))
                        ->saveRelationshipsUsing(fn (CheckboxList $component): null => static::savePermissionGroupState($component, $permissions))
                        ->dehydrated(false)
                        ->columns([
                            'default' => 1,
                            'xl' => 2,
                        ])
                        ->extraAttributes(['class' => 'role-permission-checklist '.static::permissionGroupCssClass($group)]),
                ])
                ->columnSpan(static::permissionGroupColumnSpan($group))
                ->extraAttributes(['class' => 'role-permission-group']))
            ->values()
            ->all();
    }

    /**
     * @return array<string, int|string>
     */
    private static function permissionGroupColumnSpan(string $group): array
    {
        return match ($group) {
            'Akses Panel' => [
                'default' => 'full',
                'md' => 1,
                'xl' => 1,
            ],
            'Booking' => [
                'default' => 'full',
                'md' => 2,
                'xl' => 2,
            ],
            default => [
                'default' => 'full',
                'md' => 1,
                'xl' => 1,
            ],
        };
    }

    private static function permissionGroupCssClass(string $group): string
    {
        return in_array($group, ['Akses Panel', 'Booking'], true)
            ? 'role-permission-checklist-standard'
            : 'role-permission-checklist-compact';
    }

    /**
     * @param  array<int, string>  $permissions
     * @return array<string, string>
     */
    private static function permissionOptions(array $permissions): array
    {
        return static::permissionRecords($permissions)
            ->mapWithKeys(fn (Permission $permission): array => [
                (string) $permission->getKey() => static::permissionLabel($permission->name),
            ])
            ->all();
    }

    /**
     * @param  array<int, string>  $permissions
     * @return Collection<int, Permission>
     */
    private static function permissionRecords(array $permissions): Collection
    {
        if ($permissions === []) {
            return Permission::query()->whereRaw('1 = 0')->get();
        }

        $orderSql = collect($permissions)
            ->map(fn (string $permission, int $index): string => 'WHEN ? THEN '.$index)
            ->implode(' ');

        return Permission::query()
            ->whereIn('name', $permissions)
            ->orderByRaw('CASE name '.$orderSql.' ELSE '.count($permissions).' END', $permissions)
            ->get(['id', 'name']);
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private static function loadPermissionGroupState(CheckboxList $component, array $permissions): null
    {
        $record = $component->getRecord();

        if (! $record instanceof Role || ! $record->exists) {
            $component->state([]);

            return null;
        }

        $component->state(
            $record->permissions()
                ->whereIn('name', $permissions)
                ->pluck('permissions.id')
                ->map(fn (int|string $id): string => (string) $id)
                ->all(),
        );

        return null;
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private static function savePermissionGroupState(CheckboxList $component, array $permissions): null
    {
        $record = $component->getRecord();

        if (! $record instanceof Role || ! $record->exists) {
            return null;
        }

        $allowedIds = static::permissionRecords($permissions)
            ->pluck('id')
            ->map(fn (int|string $id): string => (string) $id)
            ->all();

        $state = collect($component->getState() ?? [])
            ->map(fn (int|string $id): string => (string) $id)
            ->intersect($allowedIds)
            ->values()
            ->all();

        $currentIds = $record->permissions()
            ->whereIn('name', $permissions)
            ->pluck('permissions.id')
            ->map(fn (int|string $id): string => (string) $id)
            ->all();

        $idsToDetach = array_values(array_diff($currentIds, $state));

        if ($idsToDetach !== []) {
            $record->permissions()->detach($idsToDetach);
        }

        if ($state !== []) {
            $record->permissions()->sync($state, detaching: false);
        }

        $record->unsetRelation('permissions');

        return null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    private static function permissionGroups(): array
    {
        return [
            'Akses Panel' => [
                'panel.access',
            ],
            'Booking' => [
                'bookings.view',
                'bookings.update',
                'bookings.delete',
                'bookings.approve',
                'bookings.reject',
                'bookings.cancel',
            ],
            'Paket Umrah' => [
                'packages.view',
                'packages.create',
                'packages.update',
                'packages.delete',
            ],
            'Jadwal' => [
                'schedules.view',
                'schedules.create',
                'schedules.update',
                'schedules.delete',
            ],
            'Galeri' => [
                'galleries.view',
                'galleries.create',
                'galleries.update',
                'galleries.delete',
            ],
            'Profil Perusahaan' => [
                'profiles.view',
                'profiles.update',
            ],
            'Kontak' => [
                'contacts.view',
                'contacts.create',
                'contacts.update',
                'contacts.delete',
            ],
            'Pengaturan Website' => [
                'settings.view',
                'settings.update',
            ],
            'Pengguna Admin' => [
                'users.view',
                'users.create',
                'users.update',
                'users.delete',
            ],
            'Role / Hak Akses' => [
                'roles.view',
                'roles.create',
                'roles.update',
                'roles.delete',
            ],
            'Pembaruan Sistem' => [
                'updates.view',
                'updates.run',
            ],
            'Log' => [
                'logs.view',
                'logs.delete',
            ],
        ];
    }

    private static function permissionGroupHelper(string $group): ?string
    {
        return match ($group) {
            'Akses Panel' => 'Wajib agar pengguna bisa masuk panel admin.',
            'Booking' => 'Mengatur data booking dan keputusan review admin.',
            'Pengaturan Website' => 'Mengatur logo, hero beranda, CTA, dan SEO website.',
            'Pengguna Admin', 'Role / Hak Akses' => 'Gunakan hanya untuk pengelola akun admin.',
            'Pembaruan Sistem' => 'Gunakan hanya untuk super-admin atau pengelola teknis.',
            'Log' => 'Melihat riwayat akses publik yang tercatat sistem.',
            default => null,
        };
    }

    private static function permissionLabel(string $permission): string
    {
        [, $action] = str_contains($permission, '.')
            ? explode('.', $permission, 2)
            : [$permission, $permission];

        return match ($permission) {
            'panel.access' => 'Masuk panel admin',
            'bookings.approve' => 'Setujui booking',
            'bookings.reject' => 'Tolak booking',
            'bookings.cancel' => 'Batalkan booking',
            'logs.delete' => 'Hapus log lama',
            'updates.run' => 'Jalankan pembaruan',
            default => match ($action) {
                'view' => 'Lihat',
                'create' => 'Tambah',
                'update' => 'Edit',
                'delete' => 'Hapus',
                'run' => 'Jalankan',
                default => str($action)->headline()->toString(),
            },
        };
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Role')->searchable()->sortable(),
                TextColumn::make('permissions_count')->label('Jumlah Izin')->counts('permissions')->sortable(),
                TextColumn::make('users_count')->label('Jumlah Pengguna')->counts('users')->sortable(),
                TextColumn::make('updated_at')->label('Diperbarui')->dateTime('d M Y H:i')->sortable()->visibleFrom('lg')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->visible(fn (Role $record): bool => static::canEdit($record)),
                DeleteAction::make()
                    ->label('Hapus')
                    ->visible(fn (Role $record): bool => static::canDelete($record)),
            ])
            ->stackedOnMobile()
            ->defaultSort('name');
    }

    public static function canEdit(Model $record): bool
    {
        return parent::canEdit($record) && $record instanceof Role && $record->name !== 'super-admin';
    }

    public static function canDelete(Model $record): bool
    {
        return parent::canDelete($record) && $record instanceof Role && $record->name !== 'super-admin';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
