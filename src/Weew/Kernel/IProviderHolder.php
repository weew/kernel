<?php

namespace Weew\Kernel;

interface IProviderHolder {
    /**
     * @param string $provider
     */
    function addProvider($provider);

    /**
     * @param array $providers
     */
    function addProviders(array $providers);

    /**
     * @return array
     */
    function getProviders();

    /**
     * @param array $providers
     */
    function setProviders(array $providers);

    /**
     * @return array
     */
    function getProviderInstances();
}
