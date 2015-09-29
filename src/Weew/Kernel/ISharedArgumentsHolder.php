<?php
namespace Weew\Kernel;

use Weew\Foundation\IDictionary;

interface ISharedArgumentsHolder {
    /**
     * @return IDictionary
     */
    function getSharedArguments();

    /**
     * @param IDictionary $shared
     */
    function setSharedArguments(IDictionary $shared);
}
