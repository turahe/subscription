<?php

namespace Turahe\Subscription\Tests\Unit;

use Turahe\Subscription\Events\UserSubscribed;
use Turahe\Subscription\Events\SubscriptionUpdated;
use Turahe\Subscription\Listeners\UpdateActiveSubscription;
use Turahe\Subscription\Listeners\UpdateTrialEndingDate;
use Turahe\Subscription\Models\PlanSubscription;
use Turahe\Subscription\Tests\Factories\PlanFactory;
use Turahe\Subscription\Tests\Models\User;
use Turahe\Subscription\Tests\TestCase;

class ListenersTest extends TestCase
{
    protected User $user;
    protected PlanSubscription $subscription;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        
        $plan = PlanFactory::new()->create();
        $this->subscription = $this->user->newPlanSubscription('main', $plan);
    }

    public function test_update_active_subscription_listener()
    {
        $listener = new UpdateActiveSubscription();
        
        $this->assertInstanceOf(UpdateActiveSubscription::class, $listener);
    }

    public function test_update_active_subscription_listener_handle()
    {
        $listener = new UpdateActiveSubscription();
        $event = new UserSubscribed($this->user, $this->subscription->plan, false);
        
        // Test that the listener can handle the event without throwing exceptions
        $this->assertNull($listener->handle($event));
    }

    public function test_update_active_subscription_listener_with_updated_event()
    {
        $listener = new UpdateActiveSubscription();
        $event = new SubscriptionUpdated($this->subscription);
        
        // Test that the listener can handle the updated event
        $this->assertNull($listener->handle($event));
    }

    public function test_update_trial_ending_date_listener()
    {
        $listener = new UpdateTrialEndingDate();
        
        $this->assertInstanceOf(UpdateTrialEndingDate::class, $listener);
    }

    public function test_update_trial_ending_date_listener_handle()
    {
        $listener = new UpdateTrialEndingDate();
        $event = new UserSubscribed($this->user, $this->subscription->plan, false);
        
        // Test that the listener can handle the event without throwing exceptions
        $this->assertNull($listener->handle($event));
    }

    public function test_update_trial_ending_date_listener_with_updated_event()
    {
        $listener = new UpdateTrialEndingDate();
        $event = new SubscriptionUpdated($this->subscription);
        
        // Test that the listener can handle the updated event
        $this->assertNull($listener->handle($event));
    }

    public function test_listeners_with_null_event()
    {
        $updateActiveListener = new UpdateActiveSubscription();
        $updateTrialListener = new UpdateTrialEndingDate();
        
        $this->expectException(\TypeError::class);
        $updateActiveListener->handle(null);
    }

    public function test_listeners_with_invalid_event_type()
    {
        $updateActiveListener = new UpdateActiveSubscription();
        $updateTrialListener = new UpdateTrialEndingDate();
        
        $this->expectException(\TypeError::class);
        $updateActiveListener->handle('invalid');
    }

    public function test_listeners_serialization()
    {
        $updateActiveListener = new UpdateActiveSubscription();
        $updateTrialListener = new UpdateTrialEndingDate();
        
        $serializedActive = serialize($updateActiveListener);
        $serializedTrial = serialize($updateTrialListener);
        
        $unserializedActive = unserialize($serializedActive);
        $unserializedTrial = unserialize($serializedTrial);
        
        $this->assertInstanceOf(UpdateActiveSubscription::class, $unserializedActive);
        $this->assertInstanceOf(UpdateTrialEndingDate::class, $unserializedTrial);
    }

    public function test_listeners_json_serialization()
    {
        $updateActiveListener = new UpdateActiveSubscription();
        
        $json = json_encode($updateActiveListener);
        $this->assertIsString($json);
        $this->assertNotEmpty($json);
    }

    public function test_listeners_equality()
    {
        $listener1 = new UpdateActiveSubscription();
        $listener2 = new UpdateActiveSubscription();
        
        $this->assertEquals(get_class($listener1), get_class($listener2));
    }

    public function test_listeners_with_canceled_subscription()
    {
        $this->subscription->cancel();
        
        $updateActiveListener = new UpdateActiveSubscription();
        $updateTrialListener = new UpdateTrialEndingDate();
        
        $event = new SubscriptionUpdated($this->subscription);
        
        // Test that listeners can handle canceled subscriptions
        $this->assertNull($updateActiveListener->handle($event));
        $this->assertNull($updateTrialListener->handle($event));
    }

    public function test_listeners_with_ended_subscription()
    {
        $this->subscription->cancel(true);
        
        $updateActiveListener = new UpdateActiveSubscription();
        $updateTrialListener = new UpdateTrialEndingDate();
        
        $event = new SubscriptionUpdated($this->subscription);
        
        // Test that listeners can handle ended subscriptions
        $this->assertNull($updateActiveListener->handle($event));
        $this->assertNull($updateTrialListener->handle($event));
    }

    public function test_listeners_with_trial_subscription()
    {
        $updateActiveListener = new UpdateActiveSubscription();
        $updateTrialListener = new UpdateTrialEndingDate();
        
        $event = new UserSubscribed($this->user, $this->subscription->plan, false);
        
        // Test that listeners can handle trial subscriptions
        $this->assertNull($updateActiveListener->handle($event));
        $this->assertNull($updateTrialListener->handle($event));
    }

    public function test_listeners_methods_exist()
    {
        $updateActiveListener = new UpdateActiveSubscription();
        $updateTrialListener = new UpdateTrialEndingDate();
        
        $this->assertTrue(method_exists($updateActiveListener, 'handle'));
        $this->assertTrue(method_exists($updateTrialListener, 'handle'));
    }

    public function test_listeners_are_callable()
    {
        $updateActiveListener = new UpdateActiveSubscription();
        $updateTrialListener = new UpdateTrialEndingDate();
        
        $this->assertTrue(is_callable($updateActiveListener));
        $this->assertTrue(is_callable($updateTrialListener));
    }
} 