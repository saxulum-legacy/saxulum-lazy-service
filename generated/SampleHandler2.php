<?php

namespace Saxulum\Generated\LazyService;

class SampleHandler2 extends \Saxulum\Tests\LazyService\Fixtures\SampleHandler2
{
    protected $__container;
    protected $__original;
    public function __construct(\Saxulum\LazyService\Container\ReaderInterface $container)
    {
        $this->__container = $container;
    }
    protected function __original()
    {
        if (null === $this->__original) {
            $this->__original = new \Saxulum\Tests\LazyService\Fixtures\SampleHandler2($this->__container->get('sample1'));
        }
        return $this->__original;
    }
    public function handle(\stdClass &$object, \DateTime $date = null, array $attributes = array(0 => 'test'))
    {
        return $this->__original()->handle($object, $date, $attributes);
    }
}