<?php
namespace Weew\Kernel;

use Weew\Collections\IDictionary;

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
