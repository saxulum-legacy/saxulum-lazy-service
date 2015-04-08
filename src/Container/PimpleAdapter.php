<?php

namespace Saxulum\LazyService\Container;

use Pimple\Container;

class PimpleAdapter implements ReaderInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function get($name)
    {
        return $this->container[$name];
    }
}
