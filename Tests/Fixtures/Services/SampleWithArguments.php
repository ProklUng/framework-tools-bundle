<?php

namespace Prokl\FrameworkExtensionBundle\Tests\Fixtures\Services;

/**
 * Class SampleWithArguments
 * @package Prokl\FrameworkExtensionBundle\Tests\Fixtures\Services
 */
class SampleWithArguments
{
    /**
     * @var string $foo
     */
    private $foo;

    /**
     * @var string $bar
     */
    private $bar;

    /**
     * SampleWithArguments constructor.
     *
     * @param string $foo
     * @param string $bar
     */
    public function __construct(string $foo, string $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public function getFoo()
    {
        return $this->foo;
    }

    public function getBar()
    {
        return $this->bar;
    }
}