<?php

namespace Weew\Kernel;

use Weew\Collections\IDictionary;

interface IProviderInvoker {
    /**
     * @param $providerClass
     * @param IDictionary $shared
     *
     * @return object
     */
    function create($providerClass, IDictionary $shared);

    /**
     * @param object $provider
     * @param IDictionary $shared
     */
    function initialize($provider, IDictionary $shared);

    /**
     * @param object $provider
     * @param IDictionary $shared
     */
    function boot($provider, IDictionary $shared);

    /**
     * @param object $provider
     * @param IDictionary $shared
     */
    function shutdown($provider, IDictionary $shared);
}
