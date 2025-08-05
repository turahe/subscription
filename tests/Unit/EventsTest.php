<?php

namespace Turahe\Subscription\Tests\Unit;

use Turahe\Subscription\Events\UserSubscribed;
use Turahe\Subscription\Events\SubscriptionUpdated;
use Turahe\Subscription\Events\SubscriptionCancelled;
use Turahe\Subscription\Events\Saving;
use Turahe\Subscription\Models\Plan;
use Turahe\Subscription\Models\PlanSubscription;
use Turahe\Subscription\Tests\Factories\PlanFactory;
use Turahe\Subscription\Tests\Models\User;
use Turahe\Subscription\Tests\TestCase;

class EventsTest extends TestCase
{
    protected User $user;
    protected Plan $plan;
    protected PlanSubscription $subscription;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        
        $this->plan = PlanFactory::new()->create();
        $this->subscription = $this->user->newPlanSubscription('main', $this->plan);
    }

    public function test_user_subscribed_event()
    {
        $event = new UserSubscribed($this->user, $this->plan, false);
        
        $this->assertInstanceOf(UserSubscribed::class, $event);
        $this->assertEquals($this->user, $event->user);
        $this->assertEquals($this->plan, $event->plan);
        $this->assertFalse($event->fromRegistration);
    }

    public function test_user_subscribed_event_serialization()
    {
        $event = new UserSubscribed($this->user, $this->plan, true);
        
        $serialized = serialize($event);
        $unserialized = unserialize($serialized);
        
        $this->assertInstanceOf(UserSubscribed::class, $unserialized);
        $this->assertEquals($this->user->id, $unserialized->user->id);
        $this->assertEquals($this->plan->id, $unserialized->plan->id);
    }

    public function test_subscription_updated_event()
    {
        $event = new SubscriptionUpdated($this->subscription);
        
        $this->assertInstanceOf(SubscriptionUpdated::class, $event);
        $this->assertEquals($this->subscription, $event->subscription);
    }

    public function test_subscription_updated_event_serialization()
    {
        $event = new SubscriptionUpdated($this->subscription);
        
        $serialized = serialize($event);
        $unserialized = unserialize($serialized);
        
        $this->assertInstanceOf(SubscriptionUpdated::class, $unserialized);
        $this->assertEquals($this->subscription->id, $unserialized->subscription->id);
    }

    public function test_subscription_cancelled_event()
    {
        $event = new SubscriptionCancelled($this->subscription);
        
        $this->assertInstanceOf(SubscriptionCancelled::class, $event);
        $this->assertEquals($this->subscription, $event->subscription);
    }

    public function test_subscription_cancelled_event_serialization()
    {
        $event = new SubscriptionCancelled($this->subscription);
        
        $serialized = serialize($event);
        $unserialized = unserialize($serialized);
        
        $this->assertInstanceOf(SubscriptionCancelled::class, $unserialized);
        $this->assertEquals($this->subscription->id, $unserialized->subscription->id);
    }

    public function test_saving_event()
    {
        $event = new Saving($this->plan);
        
        $this->assertInstanceOf(Saving::class, $event);
        $this->assertEquals($this->plan, $event->subscriptionPlan);
    }

    public function test_saving_event_serialization()
    {
        $event = new Saving($this->plan);
        
        $serialized = serialize($event);
        $unserialized = unserialize($serialized);
        
        $this->assertInstanceOf(Saving::class, $unserialized);
        $this->assertEquals($this->plan->id, $unserialized->subscriptionPlan->id);
    }

    public function test_events_with_null_user()
    {
        $this->expectException(\TypeError::class);
        new UserSubscribed(null, $this->plan, false);
    }

    public function test_events_with_invalid_user_type()
    {
        $this->expectException(\TypeError::class);
        new UserSubscribed('invalid', $this->plan, false);
    }

    public function test_user_subscribed_event_with_different_user()
    {
        $newUser = User::create([
            'name' => 'Another User',
            'email' => 'another@example.com',
            'password' => 'password',
        ]);
        
        $event = new UserSubscribed($newUser, $this->plan, false);
        
        $this->assertEquals($newUser, $event->user);
        $this->assertEquals($this->plan, $event->plan);
    }

    public function test_events_broadcastable()
    {
        $userSubscribedEvent = new UserSubscribed($this->user, $this->plan, false);
        $subscriptionUpdatedEvent = new SubscriptionUpdated($this->subscription);
        $subscriptionCancelledEvent = new SubscriptionCancelled($this->subscription);
        $savingEvent = new Saving($this->plan);
        
        // Test that events can be broadcasted (they should implement ShouldBroadcast or similar)
        $this->assertIsObject($userSubscribedEvent);
        $this->assertIsObject($subscriptionUpdatedEvent);
        $this->assertIsObject($subscriptionCancelledEvent);
        $this->assertIsObject($savingEvent);
    }

    public function test_events_json_serialization()
    {
        $userSubscribedEvent = new UserSubscribed($this->user, $this->plan, false);
        
        $json = json_encode($userSubscribedEvent);
        $this->assertIsString($json);
        $this->assertNotEmpty($json);
    }

    public function test_events_to_string()
    {
        $userSubscribedEvent = new UserSubscribed($this->user, $this->plan, false);
        
        $this->assertIsString((string) $userSubscribedEvent);
    }

    public function test_events_equality()
    {
        $event1 = new UserSubscribed($this->user, $this->plan, false);
        $event2 = new UserSubscribed($this->user, $this->plan, false);
        
        $this->assertEquals($event1->user->id, $event2->user->id);
        $this->assertEquals($event1->plan->id, $event2->plan->id);
    }

    public function test_events_with_canceled_subscription()
    {
        $this->subscription->cancel();
        
        $event = new SubscriptionCancelled($this->subscription);
        
        $this->assertInstanceOf(SubscriptionCancelled::class, $event);
        $this->assertTrue($event->subscription->canceled());
    }

    public function test_events_with_updated_subscription()
    {
        $this->subscription->update(['name' => 'updated']);
        
        $event = new SubscriptionUpdated($this->subscription);
        
        $this->assertInstanceOf(SubscriptionUpdated::class, $event);
        $this->assertEquals('updated', $event->subscription->name);
    }
} 