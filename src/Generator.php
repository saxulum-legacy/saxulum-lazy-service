<?php

namespace Saxulum\LazyService;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\Stmt\Return_;
use PhpParser\PrettyPrinter\Standard as PhpGenerator;

class Generator
{
    const PROP_CONTAINER = '__container';
    const PROP_ORIGINAL = '__original';

    /**
     * @var PhpGenerator
     */
    protected $phpGenerator;

    /**
     * @param PhpGenerator $phpGenerator
     */
    public function __construct(PhpGenerator $phpGenerator)
    {
        $this->phpGenerator = $phpGenerator;
    }

    /**
     * @param Mapping $mapping
     * @param string  $path
     *
     * @return string
     */
    public function generate(Mapping $mapping, $path)
    {
        $nodes = array_merge(
            $this->generatePropertyNodes(),
            $this->generateMethodNodes($mapping)
        );

        $lazyNamespaceParts = explode('\\', $mapping->getLazyClass());
        $lazyClass = array_pop($lazyNamespaceParts);

        $classNode = new Namespace_(
            new Name(implode('\\', $lazyNamespaceParts)), array(
                new Class_($lazyClass, array(
                    'extends' => new Name('\\'.$mapping->getOriginalClass()),
                    'stmts' => $nodes,
                )),
            )
        );

        $phpCode = '<?php'."\n\n".$this->phpGenerator->prettyPrint(array($classNode));

        file_put_contents($path.DIRECTORY_SEPARATOR.$lazyClass.'.php', $phpCode);
    }

    /**
     * @param string $class
     *
     * @return \ReflectionClass
     *
     * @throws \Exception
     */
    protected function getReflectionClass($class)
    {
        if (!class_exists($class)) {
            throw new \Exception(sprintf('Unknown class: %s', $class));
        }

        return new \ReflectionClass($class);
    }

    /**
     * @return Property[]
     */
    protected function generatePropertyNodes()
    {
        return array(
            new Property(2,
                array(
                    new PropertyProperty(self::PROP_CONTAINER),
                )
            ),
            new Property(2,
                array(
                    new PropertyProperty(self::PROP_ORIGINAL),
                )
            ),
        );
    }

    /**
     * @param Mapping $mapping
     *
     * @return ClassMethod[]
     */
    protected function generateMethodNodes(Mapping $mapping)
    {
        $reflectionClass = $this->getReflectionClass($mapping->getOriginalClass());

        $classMethodNodes = array();
        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            if ($reflectionMethod->getName() === '__construct') {
                $classMethodNodes[] = $this->generateMethodConstructNode();
                $classMethodNodes[] = $this->generateMethodOriginalNode($mapping, $reflectionMethod);
            } else {
                $classMethodNodes[] = $this->generateMethodNode($reflectionMethod);
            }
        }

        return $classMethodNodes;
    }

    /**
     * @return ClassMethod
     */
    protected function generateMethodConstructNode()
    {
        return new ClassMethod('__construct', array(
            'type' => 1,
            'params' => array(
                new Param(
                    'container',
                    null,
                    '\Saxulum\LazyService\Container\ReaderInterface'
                ),
            ),
            'stmts' => array(
                new Assign(
                    new PropertyFetch(new Variable('this'), self::PROP_CONTAINER),
                    new Variable('container')
                ),
            ),
        ));
    }

    /**
     * @param Mapping           $mapping
     * @param \ReflectionMethod $reflectionMethod
     *
     * @return ClassMethod
     */
    protected function generateMethodOriginalNode(Mapping $mapping, $reflectionMethod)
    {
        /** @var ConstructArgument[] $constructArgumentsByName */
        $constructArgumentsByName = array();
        foreach ($mapping->getOriginalClassConstructArguments() as $constructArgument) {
            $constructArgumentsByName[$constructArgument->getName()] = $constructArgument;
        }

        $args = array();
        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            if (isset($constructArgumentsByName[$reflectionParameter->getName()])) {
                $constructArgument = $constructArgumentsByName[$reflectionParameter->getName()];
                $args[] = new MethodCall(
                    new PropertyFetch(new Variable('this'), self::PROP_CONTAINER),
                    $constructArgument->getContainerMethod(),
                    array(
                        new String_($constructArgument->getContainerKey()),
                    )
                );
            } else {
                $args[] = new ConstFetch(new Name('null'));
            }
        }

        return new ClassMethod(self::PROP_ORIGINAL, array(
            'type' => 2,
            'stmts' => array(
                new If_(
                    new Expr\BinaryOp\Identical(
                        new ConstFetch(new Name('null')),
                        new PropertyFetch(new Variable('this'), self::PROP_ORIGINAL)
                    ),
                    array(
                        'stmts' => array(
                            new Assign(
                                new PropertyFetch(new Variable('this'), self::PROP_ORIGINAL),
                                new New_(new Name('\\'.$mapping->getOriginalClass()), $args)
                            ),
                        ),
                    )
                ),
                new Return_(
                    new PropertyFetch(new Variable('this'), self::PROP_ORIGINAL)
                ),
            ),
        ));
    }

    /**
     * @param \ReflectionMethod $reflectionMethod
     *
     * @return ClassMethod
     */
    protected function generateMethodNode(\ReflectionMethod $reflectionMethod)
    {
        $params = array();
        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $params[] = $this->generateParameterNode($reflectionParameter);
        }

        $args = array();
        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $args[] = new Variable($reflectionParameter->getName());
        }

        return new ClassMethod($reflectionMethod->getName(), array(
            'type' => 1,
            'params' => $params,
            'stmts' => array(
                new Return_(
                    new MethodCall(
                        new MethodCall(new Variable('this'), self::PROP_ORIGINAL),
                        $reflectionMethod->getName(),
                        $args
                    )
                ),
            ),
        ));
    }

    /**
     * @param \ReflectionParameter $reflectionParameter
     *
     * @return Param
     */
    protected function generateParameterNode(\ReflectionParameter $reflectionParameter)
    {
        return new Param(
            $reflectionParameter->getName(),
            $this->getParameterDefault($reflectionParameter),
            $this->getParameterTypeHint($reflectionParameter),
            $reflectionParameter->isPassedByReference(),
            $this->isParameterVariadic($reflectionParameter)
        );
    }

    /**
     * @param \ReflectionParameter $reflectionParameter
     *
     * @return null|Expr
     */
    protected function getParameterDefault(\ReflectionParameter $reflectionParameter)
    {
        if ($reflectionParameter->isDefaultValueAvailable()) {
            $defaultValue = $reflectionParameter->getDefaultValue();

            return $this->prepareStatement($defaultValue);
        }

        return;
    }

    /**
     * @param \ReflectionParameter $reflectionParameter
     *
     * @return null|string
     */
    protected function getParameterTypeHint(\ReflectionParameter $reflectionParameter)
    {
        if (null !== $class = $reflectionParameter->getClass()) {
            return '\\'.$class->getName();
        } elseif ($reflectionParameter->isArray()) {
            return 'array';
        }

        return;
    }

    /**
     * @param \ReflectionParameter $reflectionParameter
     *
     * @return bool
     */
    protected function isParameterVariadic(\ReflectionParameter $reflectionParameter)
    {
        if (is_callable(array($reflectionParameter, 'isVariadic')) &&
            $reflectionParameter->isVariadic()) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed $value
     *
     * @return Expr
     */
    protected function prepareStatement($value)
    {
        $method = 'prepareStatmentFor'.ucfirst(gettype($value));
        if (!is_callable(array($this, $method))) {
            throw new \InvalidArgumentException(sprintf('Can\'t prepare default statement for type: %s', gettype($value)));
        }

        return $this->$method($value);
    }

    /**
     * @param array $value
     *
     * @return Array_
     */
    protected function prepareStatmentForArray($value)
    {
        $items = array();
        foreach ($value as $subKey => $subValue) {
            $items[] = new ArrayItem($this->prepareStatement($subValue), $this->prepareStatement($subKey));
        }

        return new Array_($items);
    }

    /**
     * @return ConstFetch
     */
    protected function prepareStatmentForNull()
    {
        return new ConstFetch(new Name('null'));
    }

    /**
     * @param bool $value
     *
     * @return ConstFetch
     */
    protected function prepareStatmentForBool($value)
    {
        return new ConstFetch(new Name($value ? 'true' : 'false'));
    }

    /**
     * @param int $value
     *
     * @return LNumber
     */
    protected function prepareStatmentForInt($value)
    {
        return new LNumber($value);
    }

    /**
     * @param float $value
     *
     * @return DNumber
     */
    protected function prepareStatmentForFloat($value)
    {
        return new DNumber($value);
    }

    /**
     * @param string $value
     *
     * @return String_
     */
    protected function prepareStatmentForString($value)
    {
        return new String_($value);
    }
}
