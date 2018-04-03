<?php

namespace AdrianBaez\Bundle\EasySfBundle\Utils;

use AdrianBaez\Bundle\EasySfBundle\Event\EntityEvents;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EntityUtils implements EntityUtilsInterface
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    public $entityManager;

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    public $eventDispatcher;

    /**
     * @param EntityManagerInterface $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @inheritDoc
     */
    public function getRepository($class)
    {
        return $this->entityManager->getRepository($class);
    }

    /**
     * @inheritDoc
     */
    public function save($entity)
    {
        $event = new GenericEvent($entity);
        $this->eventDispatcher->dispatch(EntityEvents::PRE_SAVE, $event);
        if (!$event->isPropagationStopped()) {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            $this->eventDispatcher->dispatch(EntityEvents::POST_SAVE, $event);
            return $entity;
        }
    }

    /**
     * @inheritDoc
     */
    public function create($entity)
    {
        $event = new GenericEvent($entity);
        $this->eventDispatcher->dispatch(EntityEvents::PRE_CREATE, $event);
        if (!$event->isPropagationStopped()) {
            $entity = $this->save($entity);
            $this->eventDispatcher->dispatch(EntityEvents::POST_CREATE, $event);
            return $entity;
        }
    }

    /**
     * @inheritDoc
     */
    public function find($class, $id, $lockMode = null, $lockVersion = null)
    {
        $eventArguments = [
            'class' => $class,
            'id' => $id,
            'lockMode' => $lockMode,
            'lockVersion' => $lockVersion,
        ];
        $event = new GenericEvent(null, $eventArguments);
        $this->eventDispatcher->dispatch(EntityEvents::PRE_LOAD, $event);
        if (!$event->isPropagationStopped()) {
            $entity = $this->getRepository($event['class'])->find($event['id'], $event['lockMode'], $event['lockVersion']);
            $event = new GenericEvent($entity, $event->getArguments());
            $this->eventDispatcher->dispatch(EntityEvents::POST_LOAD, $event);
            return $entity;
        }
    }

    /**
     * @inheritDoc
     */
    public function findBy($class, array $criteria = [], array $orderBy = null, $limit = null, $offset = null)
    {
        $eventArguments = [
            'class' => $class,
            'criteria' => $criteria,
            'orderBy' => $orderBy,
            'limit' => $limit,
            'offset' => $offset,
        ];
        $event = new GenericEvent(null, $eventArguments);
        $this->eventDispatcher->dispatch(EntityEvents::PRE_LOAD_LIST, $event);
        if (!$event->isPropagationStopped()) {
            $result = $this->getRepository($event['class'])->findBy($event['criteria'], $event['orderBy'], $event['limit'], $event['offset']);
            $event = new GenericEvent($result, $event->getArguments());
            $this->eventDispatcher->dispatch(EntityEvents::POST_LOAD_LIST, $event);
            return $result;
        }
    }

    /**
     * @inheritDoc
     */
    public function findOneBy($class, array $criteria = [], array $orderBy = null)
    {
        $eventArguments = [
            'class' => $class,
            'criteria' => $criteria,
            'orderBy' => $orderBy,
        ];
        $event = new GenericEvent(null, $eventArguments);
        $this->eventDispatcher->dispatch(EntityEvents::PRE_LOAD, $event);
        if (!$event->isPropagationStopped()) {
            $entity = $this->getRepository($event['class'])->findOneBy($event['criteria'], $event['orderBy']);
            $event = new GenericEvent($entity, $event->getArguments());
            $this->eventDispatcher->dispatch(EntityEvents::POST_LOAD, $event);
            return $entity;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete($entity)
    {
        $event = new GenericEvent($entity);
        $this->eventDispatcher->dispatch(EntityEvents::PRE_DELETE, $event);
        if (!$event->isPropagationStopped()) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
            $this->eventDispatcher->dispatch(EntityEvents::POST_DELETE, $event);
        }
    }

    /**
     * @inheritDoc
     */
    public function paginate($dql, $page = 1, $limit = 0, $fetchJoinCollection = true)
    {
        $paginator = new Paginator($dql, $fetchJoinCollection);
        if ($limit > 0) {
            $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        }
        return $paginator;
    }
}
