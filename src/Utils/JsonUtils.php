<?php

namespace AdrianBaez\Bundle\EasySfBundle\Utils;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class JsonUtils
{

    /**
     * @var EntityUtilsInterface $entityUtils
     */
    public $entityUtils;

    /**
     * @var SerializerInterface $serializer
     */
    public $serializer;

    /**
     * @param EntityUtilsInterface $entityUtils
     * @param SerializerInterface $serializer
     */
    public function __construct(
        EntityUtilsInterface $entityUtils,
        SerializerInterface $serializer
        ) {
        $this->entityUtils = $entityUtils;
        $this->serializer = $serializer;
    }

    /**
     * @param  mixed $data
     * @param  array  $context
     * @return string
     */
    public function serialize($data, $context = [])
    {
        return $this->serializer->serialize($data, JsonEncoder::FORMAT, $context);
    }

    /**
     * @param string $json
     * @param string $class
     * @param array $context
     * @return object
     */
    public function deserialize($json, $class, $context = [])
    {
        return $this->serializer->deserialize(
            $json,
            $class,
            JsonEncoder::FORMAT,
            $context
        );
    }

    /**
     * @param string $json
     * @param string $class
     * @param array $context
     * @return object|null
     */
    public function create($json, $class, $context = [])
    {
        $entity = $this->deserialize($json, $class, $context);
        return $this->entityUtils->create($entity);
    }

    /**
     * @param string $json
     * @param string $class
     * @param object $entity
     * @param array $context
     * @return object|null
     */
    public function update($json, $class, $entity, $context = [])
    {
        $context = array_merge($context, ['object_to_populate' => $entity]);
        $entity = $this->deserialize($json, $class, $context);
        return $this->entityUtils->save($entity);
    }
}
