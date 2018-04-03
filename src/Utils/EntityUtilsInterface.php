<?php

namespace AdrianBaez\Bundle\EasySfBundle\Utils;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

interface EntityUtilsInterface
{
    /**
     * @param string $class
     * @return EntityRepository
     */
    public function getRepository($class);

    /**
     * @param object $entity
     * @return object|null
     */
    public function save($entity);

    /**
     * @param object $entity
     * @return object|null
     */
    public function create($entity);

    /**
     * @param  string $class
     * @param  mixed $id
     * @param  int|null $lockMode
     * @param  int|null $lockVersion
     * @return object|null
     */
    public function find($class, $id, $lockMode = null, $lockVersion = null);

    /**
     * @param  string $class
     * @param  array  $criteria
     * @param  array|null $orderBy
     * @param  int|null $limit
     * @param  int|null $offset
     * @return array
     */
    public function findBy($class, array $criteria = [], array $orderBy = null, $limit = null, $offset = null);

    /**
     * @param  string $class
     * @param  array  $criteria
     * @param  array|null $orderBy
     * @return array
     */
    public function findOneBy($class, array $criteria = [], array $orderBy = null);

    /**
     * @param object $entity
     */
    public function delete($entity);

    /**
     * @param Query|QueryBuilder $dql
     * @param int $page
     * @param int $limit
     * @param bool $fetchJoinCollection
     * @return Paginator
     */
    public function paginate($dql, $page = 1, $limit = 0, $fetchJoinCollection = true);
}
