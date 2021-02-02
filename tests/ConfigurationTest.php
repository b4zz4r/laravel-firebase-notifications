<?php
declare(strict_types=1);

namespace Besanek\LaravelFirebaseNotifications\Tests;

use Besanek\LaravelFirebaseNotifications\Exceptions\ConfigurationException;
use Illuminate\Contracts\Config\Repository;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class ConfigurationTest extends TestCase
{
    public function testCredentialsFromFile(): void
    {
        /** @var Repository $configRepository */
        $configRepository = $this->app->make(Repository::class);

        $path = storage_path('firebase.credentials.json');
        file_put_contents($path, $configRepository->get('firebase.credentials'));
        $configRepository->set('firebase.credentials', $path);

        $this->app->make(ServiceAccount::class);

        $this->expectNotToPerformAssertions();
    }

    public function testCredentialsFromEnv(): void
    {
        $this->app->make(ServiceAccount::class);

        $this->expectNotToPerformAssertions();
    }

    public function testMissingFile(): void
    {
        $this->expectException(ConfigurationException::class);

        /** @var Repository $configRepository */
        $configRepository = $this->app->make(Repository::class);

        $path = storage_path('firebase.credentials.json');
        @unlink($path);
        $configRepository->set('firebase.credentials', $path);

        $this->app->make(Factory::class);
    }

    public function testInvalidJSON(): void
    {
        $this->expectException(ConfigurationException::class);

        /** @var Repository $configRepository */
        $configRepository = $this->app->make(Repository::class);

        $configRepository->set('firebase.credentials', '{"this": "is", "not": "valid", "JSON": "",}');

        $this->app->make(Factory::class);
    }
}
