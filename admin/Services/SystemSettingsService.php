<?php
namespace App\Admin\Services;

use App\Repositories\SettingsRepository;

class SystemSettingsService
{
    private SettingsRepository $settings;

    public function __construct()
    {
        $this->settings = new SettingsRepository();
    }

    public function grouped(): array
    {
        return $this->settings->getAllGrouped();
    }

    public function updateMany(array $keyValues): void
    {
        $this->settings->updateMany($keyValues);
    }
}
