<?php

namespace Saxulum\Tests\LazyService\Fixtures;

class SampleHandler1
{
    /**
     * @var string
     */
    protected $value1;

    /**
     * @param string $value1
     */
    public function __construct($value1)
    {
        $this->value1 = $value1;
    }

    /**
     * @param string $prefix
     *
     * @return string
     */
    public function handle($prefix)
    {
        return $prefix.$this->value1;
    }
}
