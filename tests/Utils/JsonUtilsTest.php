<?php

namespace AdrianBaez\Bundle\EasySfBundle\Tests\Utils;

use stdClass;

use AdrianBaez\Bundle\EasySfBundle\Utils\EntityUtilsInterface;
use AdrianBaez\Bundle\EasySfBundle\Utils\JsonUtils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

class JsonUtilsTest extends TestCase
{
    /**
     * @var EntityUtilsInterface $entityUtils
     */
    protected $entityUtils;

    /**
     * @var JsonUtils $jsonUtils
     */
    protected $jsonUtils;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function setUp()
    {
        parent::setUp();
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->entityUtils = $this->createMock(EntityUtilsInterface::class);
        $this->jsonUtils = new JsonUtils($this->entityUtils, $this->serializer);
    }

    public function testSerialize()
    {
        $object = new stdClass;
        $object->foo = 'bar';
        $json = '{"foo":"bar"}';
        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($object, 'json', ['baz' => 'qux'])
            ->will($this->returnValue($json));
        $this->assertEquals($json, $this->jsonUtils->serialize($object, ['baz' => 'qux']));
    }

    public function testDeserialize()
    {
        $expected = new stdClass;
        $expected->foo = 'bar';
        $json = '{"foo":"bar"}';
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($json, stdClass::class, 'json', ['baz' => 'qux'])
            ->will($this->returnValue($expected));
        $this->assertEquals($expected, $this->jsonUtils->deserialize($json, stdClass::class, ['baz' => 'qux']));
    }

    public function testCreate()
    {
        $expected = new stdClass;
        $expected->foo = 'bar';
        $json = '{"foo":"bar"}';
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($json, stdClass::class, 'json', ['baz' => 'qux'])
            ->will($this->returnValue($expected));
        $this->entityUtils
            ->expects($this->once())
            ->method('create')
            ->with($expected)
            ->will($this->returnValue($expected));
        $this->assertEquals($expected, $this->jsonUtils->create($json, stdClass::class, ['baz' => 'qux']));
    }

    public function testUpdate()
    {
        $expected = new stdClass;
        $expected->foo = 'bar';
        $json = '{"foo":"bar"}';
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($json, stdClass::class, 'json', [
                'baz' => 'qux',
                'object_to_populate' => $expected,
            ])
            ->will($this->returnValue($expected));
        $this->entityUtils
            ->expects($this->once())
            ->method('save')
            ->with($expected)
            ->will($this->returnValue($expected));
        $this->assertEquals($expected, $this->jsonUtils->update($json, stdClass::class, $expected, ['baz' => 'qux']));
    }
}
