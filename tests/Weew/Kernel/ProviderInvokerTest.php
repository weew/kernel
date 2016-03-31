<?php

namespace Tests\Weew\Kernel;

use PHPUnit_Framework_TestCase;
use Tests\Weew\Kernel\mocks\FakeProvider;
use Tests\Weew\Kernel\Mocks\SharedFakeProvider;
use Weew\Collections\Dictionary;
use Weew\Kernel\ProviderInvoker;

class ProviderInvokerTest extends PHPUnit_Framework_TestCase {
    public function test_create() {
        $invoker = new ProviderInvoker();
        $provider = $invoker->create(FakeProvider::class, new Dictionary());
        $this->assertTrue($provider instanceof FakeProvider);
    }

    public function test_configure() {
        $invoker = new ProviderInvoker();
        $provider = new FakeProvider();
        $invoker->configure($provider, new Dictionary());

        $this->assertEquals('configured', $provider->status);
    }

    public function test_initialize() {
        $invoker = new ProviderInvoker();
        $provider = new FakeProvider();
        $invoker->initialize($provider, new Dictionary());

        $this->assertEquals('initialized', $provider->status);
    }

    public function test_boot() {
        $invoker = new ProviderInvoker();
        $provider = new FakeProvider();
        $invoker->boot($provider, new Dictionary());

        $this->assertEquals('booted', $provider->status);
    }

    public function test_shutdown() {
        $invoker = new ProviderInvoker();
        $provider = new FakeProvider();
        $invoker->shutdown($provider, new Dictionary());

        $this->assertEquals('shutdown', $provider->status);
    }

    public function test_shared_arguments_are_passed() {
        $shared = new Dictionary();
        $provider = new SharedFakeProvider();
        $invoker = new ProviderInvoker();

        $shared->set('status', 'initialized');
        $invoker->initialize($provider, $shared);
        $this->assertEquals('initialized', $provider->status);

        $shared->set('status', 'booted');
        $invoker->boot($provider, $shared);
        $this->assertEquals('booted', $provider->status);

        $shared->set('status', 'shutdown');
        $invoker->shutdown($provider, $shared);
        $this->assertEquals('shutdown', $provider->status);
    }
}
