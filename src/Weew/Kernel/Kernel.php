<?php

namespace Weew\Kernel;

use Weew\Collections\Dictionary;
use Weew\Collections\IDictionary;
use Weew\Kernel\Exceptions\InvalidProviderException;

class Kernel implements IKernel {
    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @var IProviderInvoker
     */
    protected $providerInvoker;

    /**
     * @var IDictionary
     */
    protected $sharedArguments;

    /**
     * @param IProviderInvoker|null $invoker
     */
    public function __construct(IProviderInvoker $invoker = null) {
        if ( ! $invoker instanceof IProviderInvoker) {
            $invoker = $this->createProviderInvoker();
        }

        $this->setSharedArguments($this->createSharedArguments());
        $this->setProviderInvoker($invoker);
    }

    /**
     * Instantiate all providers.
     *
     * @return void
     */
    public function create() {
        foreach ($this->providers as $class => &$data) {
            if ( ! array_get($data, 'instance')) {
                $instance = $this->getProviderInvoker()
                    ->create($class, $this->getSharedArguments());
                array_set($data, 'instance', $instance);

                $this->create();
                break;
            }
        }
    }

    /**
     * Configure all providers.
     *
     * @return void
     */
    public function configure() {
        foreach ($this->providers as $class => &$data) {
            if ( ! in_array(ProviderTag::CONFIGURED, array_get($data, 'tags'))) {
                $this->create();

                array_add($data, 'tags', ProviderTag::CONFIGURED);
                $instance = array_get($data, 'instance');

                if (method_exists($instance, 'configure')) {
                    $this->getProviderInvoker()
                        ->configure($instance, $this->getSharedArguments());
                }

                $this->configure();
                break;
            }
        }
    }

    /**
     * Initialize all providers.
     *
     * @return void
     */
    public function initialize() {
        foreach ($this->providers as $class => &$data) {
            if ( ! in_array(ProviderTag::INITIALIZED, array_get($data, 'tags'))) {
                $this->configure();

                array_add($data, 'tags', ProviderTag::INITIALIZED);
                $instance = array_get($data, 'instance');

                if (method_exists($instance, 'initialize')) {
                    $this->getProviderInvoker()
                        ->initialize($instance, $this->getSharedArguments());
                }

                $this->initialize();
                break;
            }
        }
    }

    /**
     * Boot all providers.
     *
     * @return void
     */
    public function boot() {
        foreach ($this->providers as $class => &$data) {
            if ( ! in_array(ProviderTag::BOOTED, array_get($data, 'tags'))) {
                $this->initialize();

                array_add($data, 'tags', ProviderTag::BOOTED);
                $instance = array_get($data, 'instance');

                if (method_exists($instance, 'boot')) {
                    $this->getProviderInvoker()
                        ->boot($instance, $this->getSharedArguments());
                }

                $this->boot();
                break;
            }
        }
    }

    /**
     * Shutdown all providers.
     *
     * @return void
     */
    public function shutdown() {
        foreach ($this->providers as $class => &$data) {
            if ( ! in_array(ProviderTag::SHUTDOWN, array_get($data, 'tags'))) {
                $this->boot();

                array_add($data, 'tags', ProviderTag::SHUTDOWN);
                $instance = array_get($data, 'instance');

                if (method_exists($instance, 'shutdown')) {
                    $this->getProviderInvoker()
                        ->boot($instance, $this->getSharedArguments());
                }

                $this->shutdown();
                break;
            }
        }
    }

    /**
     * @param string $provider
     *
     * @throws InvalidProviderException
     */
    public function addProvider($provider) {
        $this->validateProvider($provider);

        if (is_object($provider)) {
            $class = get_class($provider);
            $instance = $provider;
        } else {
            $class = $provider;
            $instance = null;
        }

        if ( ! array_has($this->providers, $class)) {
            $this->providers[$class] = [
                'instance' => $instance,
                'tags' => [],
            ];
        }
    }

    /**
     * @param array $providers
     */
    public function addProviders(array $providers) {
        foreach ($providers as $provider) {
            $this->addProvider($provider);
        }
    }

    /**
     * @return array
     */
    public function getProviders() {
        return $this->providers;
    }

    /**
     * @return IDictionary
     */
    public function getSharedArguments() {
        return $this->sharedArguments;
    }

    /**
     * @param IDictionary $shared
     */
    public function setSharedArguments(IDictionary $shared) {
        $this->sharedArguments = $shared;
    }

    /**
     * @return IProviderInvoker
     */
    public function getProviderInvoker() {
        return $this->providerInvoker;
    }

    /**
     * @param IProviderInvoker $invoker
     */
    public function setProviderInvoker(IProviderInvoker $invoker) {
        $this->providerInvoker = $invoker;
    }

    /**
     * @param $provider
     *
     * @throws InvalidProviderException
     */
    protected function validateProvider($provider) {
        if (is_string($provider)) {
            if ( ! class_exists($provider)) {
                $message = s('Provider class %s does not exist.', $provider);

                throw new InvalidProviderException($message);
            }
        } else if ( ! is_object($provider)) {
            $message = s(
                'Provider must be either a valid class name, or an instance, received: "%s".',
                get_type($provider)
            );

            throw new InvalidProviderException($message);
        }
    }

    /**
     * @return IProviderInvoker
     */
    protected function createProviderInvoker() {
        return new ProviderInvoker();
    }

    /**
     * @return Dictionary
     */
    protected function createSharedArguments() {
        return new Dictionary();
    }
}
