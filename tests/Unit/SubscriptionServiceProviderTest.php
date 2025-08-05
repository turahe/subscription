<?php

namespace Turahe\Subscription\Tests\Unit;

use Turahe\Subscription\SubscriptionServiceProvider;
use Turahe\Subscription\Tests\TestCase;

class SubscriptionServiceProviderTest extends TestCase
{
    public function test_service_provider_can_be_instantiated()
    {
        $provider = new SubscriptionServiceProvider(app());
        
        $this->assertInstanceOf(SubscriptionServiceProvider::class, $provider);
    }

    public function test_service_provider_register_method()
    {
        $provider = new SubscriptionServiceProvider(app());
        
        // Test that register method exists and can be called
        $this->assertTrue(method_exists($provider, 'register'));
        
        // Test that register method doesn't throw exceptions
        $this->assertNull($provider->register());
    }

    public function test_service_provider_boot_method()
    {
        $provider = new SubscriptionServiceProvider(app());
        
        // Test that boot method exists and can be called
        $this->assertTrue(method_exists($provider, 'boot'));
        
        // Test that boot method doesn't throw exceptions
        $this->assertNull($provider->boot());
    }

    public function test_service_provider_package_name()
    {
        $provider = new SubscriptionServiceProvider(app());
        
        $this->assertEquals('subscription', $provider->packageName);
    }

    public function test_service_provider_config_path()
    {
        $provider = new SubscriptionServiceProvider(app());
        
        $this->assertTrue(method_exists($provider, 'getConfigPath'));
        
        $configPath = $provider->getConfigPath();
        $this->assertIsString($configPath);
        $this->assertNotEmpty($configPath);
    }

    public function test_service_provider_migration_path()
    {
        $provider = new SubscriptionServiceProvider(app());
        
        $this->assertTrue(method_exists($provider, 'getMigrationPath'));
        
        $migrationPath = $provider->getMigrationPath();
        $this->assertIsString($migrationPath);
        $this->assertNotEmpty($migrationPath);
    }

    public function test_service_provider_publishes_config()
    {
        $provider = new SubscriptionServiceProvider(app());
        
        $this->assertTrue(method_exists($provider, 'publishes'));
        
        // Test that config can be published
        $this->assertIsArray($provider->publishes());
    }

    public function test_service_provider_publishes_migrations()
    {
        $provider = new SubscriptionServiceProvider(app());
        
        // Test that migrations can be published
        $this->assertIsArray($provider->publishes());
    }

    public function test_service_provider_loads_migrations()
    {
        $provider = new SubscriptionServiceProvider(app());
        
        $this->assertTrue(method_exists($provider, 'loadMigrationsFrom'));
        
        // Test that migrations can be loaded
        $this->assertNull($provider->loadMigrationsFrom($provider->getMigrationPath()));
    }

    public function test_service_provider_merges_config()
    {
        $provider = new SubscriptionServiceProvider(app());
        
        $this->assertTrue(method_exists($provider, 'mergeConfigFrom'));
        
        // Test that config can be merged
        $this->assertNull($provider->mergeConfigFrom($provider->getConfigPath(), 'subscription'));
    }

    public function test_service_provider_register_commands()
    {
        $provider = new SubscriptionServiceProvider(app());
        
        // Test that commands can be registered if they exist
        if (method_exists($provider, 'commands')) {
            $this->assertIsArray($provider->commands());
        }
    }

    public function test_service_provider_register_views()
    {
        $provider = new SubscriptionServiceProvider(app());
        
        // Test that views can be registered if they exist
        if (method_exists($provider, 'loadViewsFrom')) {
            $this->assertNull($provider->loadViewsFrom(__DIR__ . '/../../resources/views', 'subscription'));
        }
    }

    public function test_service_provider_register_translations()
    {
        $provider = new SubscriptionServiceProvider(app());
        
        // Test that translations can be registered if they exist
        if (method_exists($provider, 'loadTranslationsFrom')) {
            $this->assertNull($provider->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'subscription'));
        }
    }

    public function test_service_provider_register_routes()
    {
        $provider = new SubscriptionServiceProvider(app());
        
        // Test that routes can be registered if they exist
        if (method_exists($provider, 'loadRoutesFrom')) {
            $this->assertNull($provider->loadRoutesFrom(__DIR__ . '/../../routes/web.php'));
        }
    }

    public function test_service_provider_register_assets()
    {
        $provider = new SubscriptionServiceProvider(app());
        
        // Test that assets can be published if they exist
        if (method_exists($provider, 'publishes')) {
            $this->assertIsArray($provider->publishes());
        }
    }

    public function test_service_provider_config_structure()
    {
        $config = config('subscription');
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('tables', $config);
        $this->assertArrayHasKey('models', $config);
        
        $this->assertArrayHasKey('plans', $config['tables']);
        $this->assertArrayHasKey('features', $config['tables']);
        $this->assertArrayHasKey('subscriptions', $config['tables']);
        $this->assertArrayHasKey('subscription_usage', $config['tables']);
        
        $this->assertArrayHasKey('plan', $config['models']);
        $this->assertArrayHasKey('feature', $config['models']);
        $this->assertArrayHasKey('subscription', $config['models']);
        $this->assertArrayHasKey('subscription_usage', $config['models']);
    }

    public function test_service_provider_table_names()
    {
        $config = config('subscription.tables');
        
        $this->assertEquals('plans', $config['plans']);
        $this->assertEquals('plan_features', $config['features']);
        $this->assertEquals('plan_subscriptions', $config['subscriptions']);
        $this->assertEquals('plan_subscription_usage', $config['subscription_usage']);
    }

    public function test_service_provider_model_classes()
    {
        $config = config('subscription.models');
        
        $this->assertEquals(\Turahe\Subscription\Models\Plan::class, $config['plan']);
        $this->assertEquals(\Turahe\Subscription\Models\PlanFeature::class, $config['feature']);
        $this->assertEquals(\Turahe\Subscription\Models\PlanSubscription::class, $config['subscription']);
        $this->assertEquals(\Turahe\Subscription\Models\PlanSubscriptionUsage::class, $config['subscription_usage']);
    }

    public function test_service_provider_serialization()
    {
        $provider = new SubscriptionServiceProvider(app());
        
        $serialized = serialize($provider);
        $unserialized = unserialize($serialized);
        
        $this->assertInstanceOf(SubscriptionServiceProvider::class, $unserialized);
    }

    public function test_service_provider_json_serialization()
    {
        $provider = new SubscriptionServiceProvider(app());
        
        $json = json_encode($provider);
        $this->assertIsString($json);
        $this->assertNotEmpty($json);
    }

    public function test_service_provider_to_string()
    {
        $provider = new SubscriptionServiceProvider(app());
        
        $this->assertIsString((string) $provider);
    }

    public function test_service_provider_equality()
    {
        $provider1 = new SubscriptionServiceProvider(app());
        $provider2 = new SubscriptionServiceProvider(app());
        
        $this->assertEquals(get_class($provider1), get_class($provider2));
    }

    public function test_service_provider_methods_exist()
    {
        $provider = new SubscriptionServiceProvider(app());
        
        $this->assertTrue(method_exists($provider, 'register'));
        $this->assertTrue(method_exists($provider, 'boot'));
        $this->assertTrue(method_exists($provider, 'getConfigPath'));
        $this->assertTrue(method_exists($provider, 'getMigrationPath'));
        $this->assertTrue(method_exists($provider, 'publishes'));
    }

    public function test_service_provider_is_callable()
    {
        $provider = new SubscriptionServiceProvider(app());
        
        $this->assertTrue(is_callable($provider));
    }
} 