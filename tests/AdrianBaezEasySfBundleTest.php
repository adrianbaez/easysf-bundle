<?php

namespace AdrianBaez\Bundle\EasySfBundle\Tests;


use AdrianBaez\Bundle\EasySfBundle\AdrianBaezEasySfBundle;
use AdrianBaez\Bundle\EasySfBundle\DependencyInjection\AdrianBaezEasySfExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AdrianBaezEasySfBundleTest extends TestCase
{
    public function testLoadContainer()
    {
        $bundle = new AdrianBaezEasySfBundle;
        $containerExtension = $bundle->getContainerExtension();
        $this->assertTrue($containerExtension instanceof AdrianBaezEasySfExtension);
        $configs = [];
        $container = $this->createMock(ContainerBuilder::class);
        $containerExtension->load($configs, $container);
    }
}
