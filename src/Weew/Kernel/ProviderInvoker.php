<?php

namespace Weew\Kernel;

use Weew\Foundation\IDictionary;

class ProviderInvoker implements IProviderInvoker {
    /**
     * @param $providerClass
     * @param IDictionary $shared
     *
     * @return object
     */
    public function create($providerClass, IDictionary $shared) {
        return new $providerClass($shared);
    }

    /**
     * @param object $provider
     * @param IDictionary $shared
     */
    public function initialize($provider, IDictionary $shared) {
        $provider->initialize($shared);
    }

    /**
     * @param object $provider
     * @param IDictionary $shared
     */
    public function boot($provider, IDictionary $shared) {
        $provider->boot($shared);
    }

    /**
     * @param object $provider
     * @param IDictionary $shared
     */
    public function shutdown($provider, IDictionary $shared) {
        $provider->shutdown($shared);
    }
}
