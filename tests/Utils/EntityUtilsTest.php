<?php

namespace AdrianBaez\Bundle\EasySfBundle\Tests\Utils;

use AdrianBaez\Bundle\EasySfBundle\Event\EntityEvents;

use AdrianBaez\Bundle\EasySfBundle\Utils\EntityUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\AbstractQuery;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\Stopwatch\Stopwatch;

class EntityUtilsTest extends TestCase
{
    /**
     * @var TraceableEventDispatcher $dispatcher
     */
    protected $dispatcher;

    /**
     * @var EntityManagerInterface $em
     */
    protected $em;

    /**
     * @var EntityUtils
     */
    protected $utils;

    public function setUp()
    {
        parent::setUp();
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->dispatcher = new TraceableEventDispatcher(new EventDispatcher, new Stopwatch);
        $this->utils = new EntityUtils($this->em, $this->dispatcher);
    }

    public function testGetRepository()
    {
        $repository = $this->createMock(EntityRepository::class);
        $this->em->expects($this->once())->method('getRepository')->with('Foo')->will($this->returnValue($repository));
        $this->assertSame($repository, $this->utils->getRepository('Foo'));
    }

    public function testSaveOk()
    {
        $entity = new stdClass;

        $this->em->expects($this->once())->method('persist')->with($entity);
        $this->em->expects($this->once())->method('flush');

        $preSave = function () {
        };
        $postSave = function () {
        };

        $this->dispatcher->addListener(EntityEvents::PRE_SAVE, $preSave);
        $this->dispatcher->addListener(EntityEvents::POST_SAVE, $postSave);

        $this->assertSame($entity, $this->utils->create($entity));

        $calledListeners = $this->dispatcher->getCalledListeners();

        $this->assertCount(2, $calledListeners);
        $this->assertCount(0, $this->dispatcher->getNotCalledListeners());

        $this->assertArrayHasKey(EntityEvents::PRE_SAVE.'.closure', $calledListeners);
        $this->assertArrayHasKey(EntityEvents::POST_SAVE.'.closure', $calledListeners);
    }

    public function testSaveKo()
    {
        $entity = new stdClass;

        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->never())->method('flush');

        $preSave = function ($event) {
            $event->stopPropagation();
        };
        $postSave = function () {
        };

        $this->dispatcher->addListener(EntityEvents::PRE_SAVE, $preSave);
        $this->dispatcher->addListener(EntityEvents::POST_SAVE, $postSave);

        $this->assertSame(null, $this->utils->create($entity));

        $calledListeners = $this->dispatcher->getCalledListeners();
        $notCalledListeners = $this->dispatcher->getNotCalledListeners();

        $this->assertCount(1, $calledListeners);
        $this->assertCount(1, $notCalledListeners);

        $this->assertArrayHasKey(EntityEvents::PRE_SAVE.'.closure', $calledListeners);
        $this->assertArrayHasKey(EntityEvents::POST_SAVE.'.closure', $notCalledListeners);
    }

    public function testFindOk()
    {
        $entity = new stdClass;

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())->method('find')->with(1, 2, 3)->will($this->returnValue($entity));

        $this->em->expects($this->once())->method('getRepository')->with('Foo')->will($this->returnValue($repository));

        $test = $this;
        $preLoad = function ($event) use ($test) {
            $test->assertNull($event->getSubject());
            $this->assertEquals([
                'class' => 'Foo',
                'id' => 1,
                'lockMode' => 2,
                'lockVersion' => 3
            ], $event->getArguments());
        };
        $postLoad = function ($event) use ($test, $entity) {
            $test->assertSame($entity, $event->getSubject());
            $this->assertEquals([
                'class' => 'Foo',
                'id' => 1,
                'lockMode' => 2,
                'lockVersion' => 3
            ], $event->getArguments());
        };

        $this->dispatcher->addListener(EntityEvents::PRE_LOAD, $preLoad);
        $this->dispatcher->addListener(EntityEvents::POST_LOAD, $postLoad);

        $this->assertSame($entity, $this->utils->find('Foo', 1, 2, 3));

        $calledListeners = $this->dispatcher->getCalledListeners();

        $this->assertCount(2, $calledListeners);
        $this->assertCount(0, $this->dispatcher->getNotCalledListeners());

        $this->assertArrayHasKey(EntityEvents::PRE_LOAD.'.closure', $calledListeners);
        $this->assertArrayHasKey(EntityEvents::POST_LOAD.'.closure', $calledListeners);
    }

    public function testFindKo()
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->never())->method('find');
        $this->em->expects($this->never())->method('getRepository');

        $preLoad = function ($event) {
            $event->stopPropagation();
        };
        $postLoad = function () {
        };

        $this->dispatcher->addListener(EntityEvents::PRE_LOAD, $preLoad);
        $this->dispatcher->addListener(EntityEvents::POST_LOAD, $postLoad);


        $this->assertSame(null, $this->utils->find('Foo', 1, 2, 3));

        $calledListeners = $this->dispatcher->getCalledListeners();
        $notCalledListeners = $this->dispatcher->getNotCalledListeners();

        $this->assertCount(1, $calledListeners);
        $this->assertCount(1, $notCalledListeners);

        $this->assertArrayHasKey(EntityEvents::PRE_LOAD.'.closure', $calledListeners);
        $this->assertArrayHasKey(EntityEvents::POST_LOAD.'.closure', $notCalledListeners);
    }

    public function testFindByOk()
    {
        $result = [
            'FOO' => 'BAR',
            'BAZ' => 'QUX',
        ];

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())->method('findBy')->with(['bar' => 'baz'], ['qux' => 'quux'], 1, 2)->will($this->returnValue($result));

        $this->em->expects($this->once())->method('getRepository')->with('Foo')->will($this->returnValue($repository));

        $test = $this;
        $preLoadList = function ($event) use ($test) {
            $test->assertNull($event->getSubject());
            $this->assertEquals([
                'class' => 'Foo',
                'criteria' => ['bar' => 'baz'],
                'orderBy' => ['qux' => 'quux'],
                'limit' => 1,
                'offset' => 2
            ], $event->getArguments());
        };
        $postLoadList = function ($event) use ($test, $result) {
            $test->assertSame($result, $event->getSubject());
            $this->assertEquals([
                'class' => 'Foo',
                'criteria' => ['bar' => 'baz'],
                'orderBy' => ['qux' => 'quux'],
                'limit' => 1,
                'offset' => 2
            ], $event->getArguments());
        };

        $this->dispatcher->addListener(EntityEvents::PRE_LOAD_LIST, $preLoadList);
        $this->dispatcher->addListener(EntityEvents::POST_LOAD_LIST, $postLoadList);

        $this->assertSame($result, $this->utils->findBy('Foo', ['bar' => 'baz'], ['qux' => 'quux'], 1, 2));

        $calledListeners = $this->dispatcher->getCalledListeners();

        $this->assertCount(2, $calledListeners);
        $this->assertCount(0, $this->dispatcher->getNotCalledListeners());

        $this->assertArrayHasKey(EntityEvents::PRE_LOAD_LIST.'.closure', $calledListeners);
        $this->assertArrayHasKey(EntityEvents::POST_LOAD_LIST.'.closure', $calledListeners);
    }

    public function testFindByKo()
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->never())->method('findBy');
        $this->em->expects($this->never())->method('getRepository');

        $preLoadList = function ($event) {
            $event->stopPropagation();
        };
        $postLoadList = function () {
        };

        $this->dispatcher->addListener(EntityEvents::PRE_LOAD_LIST, $preLoadList);
        $this->dispatcher->addListener(EntityEvents::POST_LOAD_LIST, $postLoadList);


        $this->assertSame(null, $this->utils->findBy('Foo', ['bar' => 'baz'], ['qux' => 'quux'], 1, 2));

        $calledListeners = $this->dispatcher->getCalledListeners();
        $notCalledListeners = $this->dispatcher->getNotCalledListeners();

        $this->assertCount(1, $calledListeners);
        $this->assertCount(1, $notCalledListeners);

        $this->assertArrayHasKey(EntityEvents::PRE_LOAD_LIST.'.closure', $calledListeners);
        $this->assertArrayHasKey(EntityEvents::POST_LOAD_LIST.'.closure', $notCalledListeners);
    }

    public function testFindOneByOk()
    {
        $entity = new stdClass;

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())->method('findOneBy')->with(['bar' => 'baz'], ['qux' => 'quux'])->will($this->returnValue($entity));

        $this->em->expects($this->once())->method('getRepository')->with('Foo')->will($this->returnValue($repository));

        $test = $this;
        $preLoad = function ($event) use ($test) {
            $test->assertNull($event->getSubject());
            $this->assertEquals([
                'class' => 'Foo',
                'criteria' => ['bar' => 'baz'],
                'orderBy' => ['qux' => 'quux'],
            ], $event->getArguments());
        };
        $postLoad = function ($event) use ($test, $entity) {
            $test->assertSame($entity, $event->getSubject());
            $this->assertEquals([
                'class' => 'Foo',
                'criteria' => ['bar' => 'baz'],
                'orderBy' => ['qux' => 'quux'],
            ], $event->getArguments());
        };

        $this->dispatcher->addListener(EntityEvents::PRE_LOAD, $preLoad);
        $this->dispatcher->addListener(EntityEvents::POST_LOAD, $postLoad);

        $this->assertSame($entity, $this->utils->findOneBy('Foo', ['bar' => 'baz'], ['qux' => 'quux']));

        $calledListeners = $this->dispatcher->getCalledListeners();

        $this->assertCount(2, $calledListeners);
        $this->assertCount(0, $this->dispatcher->getNotCalledListeners());

        $this->assertArrayHasKey(EntityEvents::PRE_LOAD.'.closure', $calledListeners);
        $this->assertArrayHasKey(EntityEvents::POST_LOAD.'.closure', $calledListeners);
    }

    public function testFindOneByKo()
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->never())->method('findOneBy');
        $this->em->expects($this->never())->method('getRepository');

        $preLoad = function ($event) {
            $event->stopPropagation();
        };
        $postLoad = function () {
        };

        $this->dispatcher->addListener(EntityEvents::PRE_LOAD, $preLoad);
        $this->dispatcher->addListener(EntityEvents::POST_LOAD, $postLoad);


        $this->assertSame(null, $this->utils->findOneBy('Foo', ['bar' => 'baz'], ['qux' => 'quux']));

        $calledListeners = $this->dispatcher->getCalledListeners();
        $notCalledListeners = $this->dispatcher->getNotCalledListeners();

        $this->assertCount(1, $calledListeners);
        $this->assertCount(1, $notCalledListeners);

        $this->assertArrayHasKey(EntityEvents::PRE_LOAD.'.closure', $calledListeners);
        $this->assertArrayHasKey(EntityEvents::POST_LOAD.'.closure', $notCalledListeners);
    }

    public function testCreateOK()
    {
        $entity = new stdClass;
        $this->em->expects($this->once())->method('persist')->with($entity);
        $this->em->expects($this->once())->method('flush');

        $preCreate = function () {
        };
        $postCreate = function () {
        };

        $this->dispatcher->addListener(EntityEvents::PRE_CREATE, $preCreate);
        $this->dispatcher->addListener(EntityEvents::POST_CREATE, $postCreate);

        $this->assertSame($entity, $this->utils->create($entity));

        $calledListeners = $this->dispatcher->getCalledListeners();

        $this->assertCount(2, $calledListeners);
        $this->assertCount(0, $this->dispatcher->getNotCalledListeners());

        $this->assertArrayHasKey(EntityEvents::PRE_CREATE.'.closure', $calledListeners);
        $this->assertArrayHasKey(EntityEvents::POST_CREATE.'.closure', $calledListeners);
    }

    public function testCreateKo()
    {
        $entity = new stdClass;

        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->never())->method('flush');

        $preCreate = function ($event) {
            $event->stopPropagation();
        };
        $postCreate = function () {
        };

        $this->dispatcher->addListener(EntityEvents::PRE_CREATE, $preCreate);
        $this->dispatcher->addListener(EntityEvents::POST_CREATE, $postCreate);

        $this->assertSame(null, $this->utils->create($entity));

        $calledListeners = $this->dispatcher->getCalledListeners();
        $notCalledListeners = $this->dispatcher->getNotCalledListeners();

        $this->assertCount(1, $calledListeners);
        $this->assertCount(1, $notCalledListeners);

        $this->assertArrayHasKey(EntityEvents::PRE_CREATE.'.closure', $calledListeners);
        $this->assertArrayHasKey(EntityEvents::POST_CREATE.'.closure', $notCalledListeners);
    }

    public function testDeleteOK()
    {
        $entity = new stdClass;
        $this->em->expects($this->once())->method('remove')->with($entity);
        $this->em->expects($this->once())->method('flush');

        $preDelete = function () {
        };
        $postDelete = function () {
        };

        $this->dispatcher->addListener(EntityEvents::PRE_DELETE, $preDelete);
        $this->dispatcher->addListener(EntityEvents::POST_DELETE, $postDelete);

        $this->assertSame(null, $this->utils->delete($entity));

        $calledListeners = $this->dispatcher->getCalledListeners();

        $this->assertCount(2, $calledListeners);
        $this->assertCount(0, $this->dispatcher->getNotCalledListeners());

        $this->assertArrayHasKey(EntityEvents::PRE_DELETE.'.closure', $calledListeners);
        $this->assertArrayHasKey(EntityEvents::POST_DELETE.'.closure', $calledListeners);
    }

    public function testDeleteKo()
    {
        $entity = new stdClass;

        $this->em->expects($this->never())->method('remove');
        $this->em->expects($this->never())->method('flush');

        $preDelete = function ($event) {
            $event->stopPropagation();
        };
        $postDelete = function () {
        };

        $this->dispatcher->addListener(EntityEvents::PRE_DELETE, $preDelete);
        $this->dispatcher->addListener(EntityEvents::POST_DELETE, $postDelete);

        $this->assertSame(null, $this->utils->delete($entity));

        $calledListeners = $this->dispatcher->getCalledListeners();
        $notCalledListeners = $this->dispatcher->getNotCalledListeners();

        $this->assertCount(1, $calledListeners);
        $this->assertCount(1, $notCalledListeners);

        $this->assertArrayHasKey(EntityEvents::PRE_DELETE.'.closure', $calledListeners);
        $this->assertArrayHasKey(EntityEvents::POST_DELETE.'.closure', $notCalledListeners);
    }

    public function testPaginate()
    {
        $query = $this->getMockBuilder(AbstractQuery::class)
                         ->setMethods(['setFirstResult', 'setMaxResults'])
                         ->disableOriginalConstructor()
                         ->getMockForAbstractClass();
        $query->expects($this->once())->method('setFirstResult')->with(180)->will($this->returnValue($query));
        $query->expects($this->once())->method('setMaxResults')->with(20)->will($this->returnValue($query));
        $paginator = $this->utils->paginate($query, 10, 20, false);
        $this->assertTrue($paginator instanceof Paginator);
        $this->assertSame(false, $paginator->getFetchJoinCollection());
    }

    public function testPaginateDefault()
    {
        $query = $this->getMockBuilder(AbstractQuery::class)
                         ->setMethods(['setFirstResult', 'setMaxResults'])
                         ->disableOriginalConstructor()
                         ->getMockForAbstractClass();
        $query->expects($this->never())->method('setFirstResult');
        $query->expects($this->never())->method('setMaxResults');
        $paginator = $this->utils->paginate($query);
        $this->assertTrue($paginator instanceof Paginator);
        $this->assertSame(true, $paginator->getFetchJoinCollection());
    }
}
