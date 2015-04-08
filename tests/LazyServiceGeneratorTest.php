<?php

namespace Saxulum\Tests\LazyService;

use PhpParser\PrettyPrinter\Standard as PhpGenerator;
use Pimple\Container;
use Saxulum\LazyService\ConstructArgument;
use Saxulum\LazyService\Container\PimpleAdapter;
use Saxulum\LazyService\Generator;
use Saxulum\LazyService\Mapping;
use Saxulum\Tests\LazyService\Fixtures\SampleHandler1;
use Saxulum\Generated\LazyService\SampleHandler2;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $phpGenerator = new PhpGenerator;

        $mapping = new Mapping(
            'Saxulum\Tests\LazyService\Fixtures\SampleHandler2',
            'Saxulum\Generated\LazyService\SampleHandler2',
            array(
                new ConstructArgument('sample1')
            )
        );

        $generatedPath = __DIR__ . '/../generated/';

        $generator = new Generator($phpGenerator);
        $generator->generate($mapping, $generatedPath);

        require $generatedPath . 'SampleHandler2.php';

        $container = new Container();
        $container['sample1'] = function() {
            return new SampleHandler1();
        };

        $container['sample2'] = function() use ($container) {
            return new SampleHandler2(new PimpleAdapter($container));
        };

        $object = new \stdClass();
        $date = new \DateTime();

        $this->assertTrue($container['sample2']->handle($object, $date));
    }
}