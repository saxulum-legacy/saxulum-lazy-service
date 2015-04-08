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
     * @param \stdClass $object
     * @param \DateTime $date
     * @param array $attributes
     * @return bool
     */
    public function handle(\stdClass &$object, \DateTime $date = null, array $attributes = array('test'))
    {
        return $this->sampleHandler1->handle();
    }
}