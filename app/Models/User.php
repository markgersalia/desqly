<?php

namespace App\Models;

use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasDefaultTenant, HasTenants
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getTenants(Panel $panel): array | Collection
    {
        return $this->company ? collect([$this->company]) : collect();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return (int) ($this->company_id ?? 0) === (int) $tenant->getKey();
    }

    public function getDefaultTenant(Panel $panel): ?Model
    {
        return $this->company;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'super-admin') {
            return $this->hasRole('SystemOwner');
        }

        if ($panel->getId() === 'admin') {
            if ($this->hasRole('SystemOwner')) {
                return false;
            }

            // Allow users with no company to reach tenant registration.
            if (blank($this->company_id)) {
                return true;
            }

            return $this->hasAnyRole(['Admin', 'Staff']);
        }

        return false;
    }

    public static function getAdminUsers()
    {
        try {
            $companyId = Filament::getTenant()?->getKey() ?? auth('web')->user()?->company_id;

            return self::query()
                ->role('Admin')
                ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
                ->get();
        } catch (\Throwable $e) {
            return self::query()->whereRaw('1 = 0')->get();
        }
    }
}
