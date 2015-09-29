<?php

namespace Weew\Kernel;

interface IKernel extends IProviderHolder, IStatusHolder, ISharedArgumentsHolder {
    /**
     * @return void
     */
    function initialize();

    /**
     * @return void
     */
    function boot();

    /**
     * @return void
     */
    function shutdown();
}
