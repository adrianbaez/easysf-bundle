<?php

namespace AdrianBaez\Bundle\EasySfBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Routing\RouteCollection;

class ReusableControllerLoader
{
    /**
     * @var LoaderResolver
     */
    protected $resolver;
    
    /**
     * @var string[]
     */
    protected $resources = [];
    
    public function loadRoutes()
    {
        $collection = new RouteCollection;
        foreach ($this->resources as $resource => $types) {
            foreach ($types as $type) {
                foreach ($this->getResolver()->getLoaders() as $loader) {
                    if ($loader->supports($resource, $type)) {
                        $collection->addCollection($loader->load($resource, $type));
                    }
                }
            }
        }
        return $collection;
    }
    
    /**
     * @param LoaderInterface $loader
     */
    public function addLoader(LoaderInterface $loader)
    {
        $this->getResolver()->addLoader($loader);
    }
    
    /**
     * @param string $resource
     * @param array $types
     */
    public function addResource(string $resource, array $types)
    {
        $this->resources[$resource] = $types;
        return $this;
    }
    
    /**
     * @return LoaderResolver
     */
    protected function getResolver() :LoaderResolver
    {
        if (!$this->resolver) {
            $this->resolver = new LoaderResolver;
        }
        return $this->resolver;
    }
}
