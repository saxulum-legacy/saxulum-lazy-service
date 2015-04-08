<?php

namespace Saxulum\LazyService;

class ConstructArgument
{
    /**
     * @var string
     */
    protected $serviceName;

    /**
     * @param string $serviceName
     */
    public function __construct($serviceName)
    {
        $this->serviceName = $serviceName;
    }

    /**
     * @return string
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }
}
