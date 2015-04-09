<?php

namespace Saxulum\Tests\LazyService\Fixtures;

class SampleHandler2
{
    /**
     * @var SampleHandler1
     */
    protected $sampleHandler1;

    /**
     * @param SampleHandler1 $sampleHandler1
     */
    public function __construct(SampleHandler1 $sampleHandler1)
    {
        $this->sampleHandler1 = $sampleHandler1;
    }

    /**
     * @param string $prefix
     *
     * @return string
     */
    public function handle($prefix)
    {
        return $this->sampleHandler1->handle($prefix);
    }
}
