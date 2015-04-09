<?php

namespace Saxulum\LazyService\Container;

interface ReaderInterface
{
    /**
     * @param string $name
     *
     * @return mixed
     */
    public function get($name);

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getParameter($name);
}
