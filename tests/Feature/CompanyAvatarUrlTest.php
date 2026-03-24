<?php

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('company filament avatar url returns null when avatar is blank', function () {
    $company = Company::factory()->make(['avatar' => null]);

    expect($company->getFilamentAvatarUrl())->toBeNull()
        ->and($company->avatar_url)->toBeNull();
});

test('company filament avatar url returns full urls as is', function () {
    $avatarUrl = 'https://cdn.example.com/company/avatar.png';
    $company = Company::factory()->make(['avatar' => $avatarUrl]);

    expect($company->getFilamentAvatarUrl())->toBe($avatarUrl);
});

test('company filament avatar url normalizes filename-only values', function () {
    $company = Company::factory()->make(['avatar' => 'avatar-file.png']);

    $expected = Storage::disk('public')->url('companies/avatars/avatar-file.png');

    expect($company->getFilamentAvatarUrl())->toBe($expected)
        ->and($company->avatar_url)->toBe($expected);
});

test('company filament avatar url normalizes prefixed storage paths', function () {
    $companyWithStoragePrefix = Company::factory()->make([
        'avatar' => 'storage/companies/avatars/avatar-file.png',
    ]);

    $companyWithPublicPrefix = Company::factory()->make([
        'avatar' => 'public/companies/avatars/avatar-file.png',
    ]);

    $companyWithDirectoryPath = Company::factory()->make([
        'avatar' => 'companies/avatars/avatar-file.png',
    ]);

    $expected = Storage::disk('public')->url('companies/avatars/avatar-file.png');

    expect($companyWithStoragePrefix->getFilamentAvatarUrl())->toBe($expected);
    expect($companyWithPublicPrefix->getFilamentAvatarUrl())->toBe($expected);
    expect($companyWithDirectoryPath->getFilamentAvatarUrl())->toBe($expected);
});
