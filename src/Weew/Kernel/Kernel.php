<?php

namespace Weew\Kernel;

use Weew\Collections\Dictionary;
use Weew\Collections\IDictionary;
use Weew\Kernel\Exceptions\InvalidProviderException;
use Weew\Kernel\Exceptions\KernelException;

class Kernel implements IKernel {
    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @var array
     */
    protected $providerInstances = [];

    /**
     * @var IProviderInvoker
     */
    protected $providerInvoker;

    /**
     * @var string
     */
    protected $status = KernelStatus::SHUTDOWN;

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
     * @throws KernelException
     */
    public function initialize() {
        $this->tryToInitialize();

        for ($i = 0; array_has($this->providers, $i); $i++) {
            $provider = $this->providers[$i];

            $class = $this->getProviderClass($provider);
            $instance = $this->createProvider($provider);
            $this->providerInstances[$class] = $instance;

            if (method_exists($instance, 'initialize')) {
                $this->getProviderInvoker()
                    ->initialize($instance, $this->getSharedArguments());
            }
        }
    }

    /**
     * @throws KernelException
     */
    public function boot() {
        $this->tryToBoot();

        foreach ($this->providerInstances as $provider) {
            if (method_exists($provider, 'boot')) {
                $this->getProviderInvoker()->boot($provider, $this->getSharedArguments());
            }
        };
    }

    /**
     * @throws KernelException
     */
    public function shutdown() {
        $this->tryToShutdown();

        foreach ($this->providerInstances as $provider) {
            if (method_exists($provider, 'shutdown')) {
                $this->getProviderInvoker()->shutdown($provider, $this->getSharedArguments());
            }
        };
    }

    /**
     * @param string $provider
     *
     * @throws InvalidProviderException
     */
    public function addProvider($provider) {
        $this->validateProvider($provider);
        $class = $this->getProviderClass($provider);

        if ( ! in_array($class, $this->providers)) {
            $this->providers[] = $provider;
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
     * @param array $providers
     */
    public function setProviders(array $providers) {
        $this->providers = $providers;
    }

    /**
     * @return array
     */
    public function getProviderInstances() {
        return $this->providerInstances;
    }

    /**
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @param $status
     */
    protected function setStatus($status) {
        $this->status = $status;
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

    /**
     * @throws KernelException
     */
    protected function tryToInitialize() {
        if ($this->getStatus() !== KernelStatus::SHUTDOWN) {
            throw new KernelException(
                s('Can not initialize kernel. Kernel has to be %s, kernel is %s.',
                    KernelStatus::SHUTDOWN, $this->getStatus())
            );
        }

        $this->setStatus(KernelStatus::INITIALIZED);
    }

    /**
     * @throws KernelException
     */
    protected function tryToBoot() {
        if ($this->getStatus() !== KernelStatus::INITIALIZED) {
            throw new KernelException(
                s('Can not boot kernel. Kernel has to be %s, kernel is %s.',
                    KernelStatus::INITIALIZED, $this->getStatus())
            );
        }

        $this->setStatus(KernelStatus::BOOTED);
    }

    /**
     * @throws KernelException
     */
    protected function tryToShutdown() {
        if ($this->getStatus() !== KernelStatus::BOOTED) {
            throw new KernelException(
                s('Can not shutdown kernel. Kernel has to be %s, kernel is %s.',
                    KernelStatus::BOOTED, $this->getStatus())
            );
        }

        $this->setStatus(KernelStatus::SHUTDOWN);
    }

    /**
     * @param $provider
     *
     * @return string
     */
    protected function getProviderClass($provider) {
        if (is_object($provider)) {
            return get_class($provider);
        }

        return $provider;
    }

    /**
     * @param $provider
     *
     * @return object
     */
    protected function createProvider($provider) {
        if (is_string($provider)) {
            return $this->getProviderInvoker()
                ->create($provider, $this->getSharedArguments());
        }

        return $provider;
    }
}
