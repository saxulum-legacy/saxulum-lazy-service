<?php

namespace Saxulum\Tests\LazyService\Fixtures;

class SampleHandler1
{
    /**
     * @var string
     */
    protected $param1;

    /**
     * @var string
     */
    protected $param2;

    /**
     * @var string
     */
    protected $param3;

    /**
     * @param string $param1
     * @param string $param2
     * @param string $param3
     */
    public function __construct($param1, $param2, $param3)
    {
        $this->param1 = $param1;
        $this->param2 = $param2;
        $this->param3 = $param3;
    }

    /**
     * @param string $prefix
     *
     * @return string
     */
    public function handle($prefix)
    {
        return $prefix.'_'.$this->param1.'_'.$this->param2.'_'.$this->param3;
    }
}
