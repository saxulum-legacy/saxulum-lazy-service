<?php

namespace Saxulum\Tests\LazyService;

use PhpParser\PrettyPrinter\Standard as PhpGenerator;
use Pimple\Container;
use Saxulum\LazyService\ConstructArgument;
use Saxulum\LazyService\Container\PimpleAdapter;
use Saxulum\LazyService\Generator;
use Saxulum\LazyService\Mapping;
use Saxulum\Generated\LazyService\SampleHandler1;
use Saxulum\Tests\LazyService\Fixtures\SampleHandler2;

class LazyServiceGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $phpGenerator = new PhpGenerator();

        $mapping = new Mapping(
            'Saxulum\Tests\LazyService\Fixtures\SampleHandler1',
            'Saxulum\Generated\LazyService\SampleHandler1',
            array(
                new ConstructArgument('param1', 'value1', ConstructArgument::CONTAINER_METHOD_GETPARAMETER),
                new ConstructArgument('param3', 'value3', ConstructArgument::CONTAINER_METHOD_GETPARAMETER),
            )
        );

        $generatedPath = __DIR__.'/../generated/';

        $generator = new Generator($phpGenerator);
        $generator->generate($mapping, $generatedPath);

        require $generatedPath.'SampleHandler1.php';

        $container = new Container();

        $container['value1'] = 'value1';
        $container['value3'] = 'value3';

        $container['sample1'] = function () use ($container) {
            return new SampleHandler1(new PimpleAdapter($container));
        };

        $container['sample2'] = function () use ($container) {
            return new SampleHandler2($container['sample1']);
        };

        $this->assertEquals('prefix_value1__value3', $container['sample2']->handle('prefix'));
    }
}
