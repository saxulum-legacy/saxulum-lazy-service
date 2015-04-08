<?php

namespace Saxulum\LazyService;

class Mapping
{
    /**
     * @var string
     */
    protected $originalClass;

    /**
     * @var string
     */
    protected $lazyClass;

    /**
     * @var ConstructArgument[]
     */
    protected $originalClassConstructArguments;

    /**
     * @param string $originalClass
     * @param string $lazyClass
     * @param array  $originalClassConstructArguments
     */
    public function __construct($originalClass, $lazyClass, array $originalClassConstructArguments)
    {
        $this->originalClass = $originalClass;
        $this->lazyClass = $lazyClass;
        $this->originalClassConstructArguments = array();
        foreach ($originalClassConstructArguments as $constructArgument) {
            if (!$constructArgument instanceof ConstructArgument) {
                throw new \InvalidArgumentException('Only instances of ConstructArgument are allowed as element!');
            }
            $this->originalClassConstructArguments[] = $constructArgument;
        }
    }

    /**
     * @return string
     */
    public function getOriginalClass()
    {
        return $this->originalClass;
    }

    /**
     * @return string
     */
    public function getLazyClass()
    {
        return $this->lazyClass;
    }

    /**
     * @return ConstructArgument[]
     */
    public function getOriginalClassConstructArguments()
    {
        return $this->originalClassConstructArguments;
    }
}
