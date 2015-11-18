<?php

namespace Weew\Kernel;

use InvalidArgumentException;
use Weew\Collections\Dictionary;
use Weew\Collections\IDictionary;
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
        if ($this->getStatus() !== KernelStatus::SHUTDOWN) {
            throw new KernelException(
                s('Can not initialize kernel. Kernel has to be %s, kernel is %s.',
                    KernelStatus::SHUTDOWN, $this->getStatus())
            );
        }

        foreach ($this->providers as $class) {
            $provider = $this->getProviderInvoker()->create($class, $this->getSharedArguments());
            $this->providerInstances[$class] = $provider;

            if (method_exists($provider, 'initialize')) {
                $this->getProviderInvoker()->initialize($provider, $this->getSharedArguments());
            }
        }

        $this->setStatus(KernelStatus::INITIALIZED);
    }

    /**
     * @throws KernelException
     */
    public function boot() {
        if ($this->getStatus() !== KernelStatus::INITIALIZED) {
            throw new KernelException(
                s('Can not boot kernel. Kernel has to be %s, kernel is %s.',
                    KernelStatus::INITIALIZED, $this->getStatus())
            );
        }

        foreach ($this->providerInstances as $provider) {
            if (method_exists($provider, 'boot')) {
                $this->getProviderInvoker()->boot($provider, $this->getSharedArguments());
            }
        }

        $this->setStatus(KernelStatus::BOOTED);
    }

    /**
     * @throws KernelException
     */
    public function shutdown() {
        if ($this->getStatus() !== KernelStatus::BOOTED) {
            throw new KernelException(
                s('Can not shutdown kernel. Kernel has to be %s, kernel is %s.',
                    KernelStatus::BOOTED, $this->getStatus())
            );
        }

        foreach ($this->providerInstances as $provider) {
            if (method_exists($provider, 'shutdown')) {
                $this->getProviderInvoker()->shutdown($provider, $this->getSharedArguments());
            }
        }

        $this->setStatus(KernelStatus::SHUTDOWN);
    }

    /**
     * @param string $providerClass
     */
    public function addProvider($providerClass) {
        if ( ! class_exists($providerClass)) {
            throw new InvalidArgumentException(
                s('Provider class %s does not exist.', $providerClass)
            );
        }

        $this->providers[] = $providerClass;
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
