<?php

namespace Saxulum\LazyService;

class ConstructArgument
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $containerKey;

    /**
     * @var string
     */
    protected $containerMethod;

    const CONTAINER_METHOD_GET = 'get';
    const CONTAINER_METHOD_GETPARAMETER = 'getParameter';

    /**
     * @param string $name
     * @param string $containerKey
     */
    public function __construct($name, $containerKey, $containerMethod)
    {
        $this->name = $name;
        $this->containerKey = $containerKey;
        $this->containerMethod = $containerMethod;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getContainerKey()
    {
        return $this->containerKey;
    }

    /**
     * @return string
     */
    public function getContainerMethod()
    {
        return $this->containerMethod;
    }
}
