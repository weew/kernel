<?php

namespace Weew\Kernel;

interface IKernel extends IProviderHolder, ISharedArgumentsHolder {
    /**
     * Instantiate all providers.
     *
     * @return void
     */
    function create();

    /**
     * Configure all providers.
     *
     * @return void
     */
    function configure();

    /**
     * Initialize all providers.
     *
     * @return void
     */
    function initialize();

    /**
     * Boot all providers.
     *
     * @return void
     */
    function boot();

    /**
     * Shutdown all providers.
     *
     * @return void
     */
    function shutdown();
}
