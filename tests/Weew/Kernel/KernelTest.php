<?php

namespace Tests\Weew\Kernel;

use PHPUnit_Framework_TestCase;
use stdClass;
use Tests\Weew\Kernel\Mocks\EmptyProvider;
use Tests\Weew\Kernel\Mocks\FakeProvider;
use Weew\Collections\Dictionary;
use Weew\Collections\IDictionary;
use Weew\Kernel\Exceptions\InvalidProviderException;
use Weew\Kernel\IProviderInvoker;
use Weew\Kernel\Kernel;
use Weew\Kernel\ProviderInvoker;
use Weew\Kernel\ProviderTag;

class KernelTest extends PHPUnit_Framework_TestCase {
    public function test_kernel_has_a_default_provider_invoker() {
        $kernel = new Kernel();
        $this->assertTrue($kernel->getProviderInvoker() instanceof IProviderInvoker);
    }

    public function test_set_custom_provider_invoker() {
        $invoker = new ProviderInvoker();
        $kernel = new Kernel();
        $kernel->setProviderInvoker($invoker);

        $this->assertTrue($kernel->getProviderInvoker() === $invoker);
    }

    public function test_create() {
        $kernel = new Kernel();
        $kernel->create();
    }

    public function test_configure() {
        $kernel = new Kernel();
        $kernel->initialize();
    }

    public function test_initialize() {
        $kernel = new Kernel();
        $kernel->initialize();
    }

    public function test_boot() {
        $kernel = new Kernel();
        $kernel->boot();
    }

    public function test_shutdown() {
        $kernel = new Kernel();
        $kernel->shutdown();
    }

    public function test_get_and_add_providers() {
        $kernel = new Kernel();
        $this->assertEquals([], $kernel->getProviders());
        $kernel->addProvider(FakeProvider::class);
        $this->assertEquals(1, count($kernel->getProviders()));
        $kernel->addProviders([self::class, self::class]);
        $this->assertEquals(2, count($kernel->getProviders()));
    }

    public function test_add_invalid_class_name_as_provider() {
        $kernel = new Kernel();
        $this->setExpectedException(
            InvalidProviderException::class, 'Provider class foo does not exist.'
        );
        $kernel->addProviders(['foo']);
    }

    public function test_add_invalid_provider_type() {
        $kernel = new Kernel();
        $this->setExpectedException(InvalidProviderException::class);
        $kernel->addProvider([]);
    }

    public function test_add_instantiated_provider() {
        $kernel = new Kernel();
        $kernel->addProvider(new FakeProvider());
    }

    public function test_providers_are_created() {
        $kernel = new Kernel();
        $kernel->addProvider(FakeProvider::class);
        $kernel->configure();

        $providers = $kernel->getProviders();
        $this->assertEquals(1, count($providers));

        $provider = array_pop($providers);
        $this->assertTrue(array_get($provider, 'instance') instanceof FakeProvider);
    }

    public function test_providers_are_configured() {
        $kernel = new Kernel();
        $kernel->addProvider(FakeProvider::class);
        $kernel->configure();

        $providers = $kernel->getProviders();
        $this->assertEquals(1, count($providers));

        $provider = array_pop($providers);
        $this->assertTrue(array_get($provider, 'instance') instanceof FakeProvider);
        $this->assertTrue(in_array(ProviderTag::CONFIGURED, array_get($provider, 'tags')));
    }

    public function test_providers_are_initialized() {
        $kernel = new Kernel();
        $kernel->addProvider(FakeProvider::class);
        $kernel->initialize();

        $providers = $kernel->getProviders();
        $this->assertEquals(1, count($providers));

        $provider = array_pop($providers);
        $this->assertTrue(array_get($provider, 'instance') instanceof FakeProvider);
        $this->assertTrue(in_array(ProviderTag::CONFIGURED, array_get($provider, 'tags')));
        $this->assertTrue(in_array(ProviderTag::INITIALIZED, array_get($provider, 'tags')));
    }

    public function test_providers_are_booted() {
        $kernel = new Kernel();
        $kernel->addProvider(FakeProvider::class);
        $kernel->boot();

        $providers = $kernel->getProviders();
        $this->assertEquals(1, count($providers));

        $provider = array_pop($providers);
        $this->assertTrue(array_get($provider, 'instance') instanceof FakeProvider);
        $this->assertTrue(in_array(ProviderTag::CONFIGURED, array_get($provider, 'tags')));
        $this->assertTrue(in_array(ProviderTag::INITIALIZED, array_get($provider, 'tags')));
        $this->assertTrue(in_array(ProviderTag::BOOTED, array_get($provider, 'tags')));
    }

    public function test_providers_are_shutdown() {
        $kernel = new Kernel();
        $kernel->addProvider(FakeProvider::class);
        $kernel->shutdown();

        $providers = $kernel->getProviders();
        $this->assertEquals(1, count($providers));

        $provider = array_pop($providers);
        $this->assertTrue(array_get($provider, 'instance') instanceof FakeProvider);
        $this->assertEquals([], array_get($provider, 'tags'));
    }

    public function test_providers_without_methods() {
        $kernel = new Kernel();
        $kernel->addProvider(EmptyProvider::class);
        $kernel->addProvider(new stdClass());
        $kernel->shutdown();
    }

    public function test_get_and_set_shared_arguments() {
        $kernel = new Kernel();
        $args = $kernel->getSharedArguments();
        $this->assertTrue($args instanceof IDictionary);
        $args = new Dictionary();
        $kernel->setSharedArguments($args);
        $this->assertTrue($kernel->getSharedArguments() === $args);
    }

    public function test_kernel_with_simple_providers() {
        $kernel = new Kernel();
        $kernel->addProvider(stdClass::class);

        $kernel->create();
        $kernel->configure();
        $kernel->initialize();
        $kernel->boot();
        $kernel->shutdown();
    }
}
