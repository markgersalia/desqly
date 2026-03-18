<?php

namespace App\Models\Concerns;

use App\Models\Company;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

trait BelongsToCompany
{
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    protected static function bootBelongsToCompany(): void
    {
        static::creating(function ($model): void {
            if (! empty($model->company_id)) {
                return;
            }

            $tenant = null;

            try {
                $tenant = Filament::getTenant();
            } catch (\Throwable $e) {
                $tenant = null;
            }

            if ($tenant instanceof Company) {
                $model->company_id = $tenant->getKey();

                return;
            }

            $userCompanyId = auth('web')->user()?->company_id;

            if ($userCompanyId) {
                $model->company_id = $userCompanyId;

                return;
            }

            if (! Schema::hasTable('companies')) {
                return;
            }

            $companies = Company::query()->pluck('id');

            if ($companies->count() === 1) {
                $model->company_id = $companies->first();
            }
        });
    }
}

