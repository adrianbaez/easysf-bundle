<?php

namespace AdrianBaez\Bundle\EasySfBundle\Tests\Routing;

use AdrianBaez\Bundle\EasySfBundle\Routing\ReusableControllerLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ReusableControllerLoaderTest extends TestCase
{
    /**
     * @param  string $resource
     * @param  string $acceptedType
     * @return LoaderInterface
     */
    protected function getMockLoader($resource, $acceptedType){
        $loader = $this->createMock(LoaderInterface::class);
        $loader->method('supports')->will($this->returnCallback(function($resource, $type) use ($acceptedType){
            return $type === $acceptedType;
        }));
        $loader->method('load')->will($this->returnCallback(function($resource, $type) use ($acceptedType){
            if ($type === $acceptedType) {
                $collection = new RouteCollection;
                $collection->add('route', new Route('/'));
                return $collection;
            }
        }));
        return $loader;
    }
    
    /**
     * @dataProvider providerLoadRoutes
     * @param  string $resource
     * @param  string $types 
     * @param  LoaderInterface $addLoader
     * @param  int $cantRoutes
     */
    public function testLoadRoutes($resource, $types, $addLoader, $cantRoutes)
    {
        $loader = new ReusableControllerLoader;
        $loader->addResource($resource, $types);
        $routes = $loader->loadRoutes();
        $this->assertTrue($routes instanceof RouteCollection);
        if($addLoader){
            $loader->addLoader($addLoader);
            $routes = $loader->loadRoutes();
        }
        $this->assertCount($cantRoutes, $routes);
    }
    
    /**
     * @return \Iterator
     */
    public function providerLoadRoutes()
    {
        yield ['.', ['foo'], null, 0];
        yield ['.', ['bar'], null, 0];
        yield ['.', ['bar'], $this->getMockLoader('.', 'foo'), 0];
        yield ['.', ['foo'], $this->getMockLoader('.', 'foo'), 1];
        yield ['.', ['foo', 'bar'], $this->getMockLoader('.', 'foo'), 1];
    }
}
