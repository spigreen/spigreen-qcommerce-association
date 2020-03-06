<?php

/**
 * This file is part of the QualityPress package.
 *
 * (c) QualityPress
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace QualityPress\QCommerce\Component\Association\Repository\Propel;

/**
 * AbstractRepository
 *
 * @author QualityPress
 */
abstract class AbstractRepository
{

    protected $repositoryClass;

    public function __construct($class)
    {
        $this->repositoryClass = $class;
    }

    public function __call($method, $args)
    {
        $queryBuilder = $this->createQueryBuilder();

        return call_user_func_array(array($queryBuilder, $method), $args);
    }

    /**
     * @param mixed $id
     *
     * @return null|object
     */
    public function find($id)
    {
        return $this
            ->getQueryBuilder()
            ->add($this->getPropertyName('id'), intval($id), \Criteria::EQUAL)
            ->findOne()
            ;
    }

    /**
     * @return array
     */
    public function findAll()
    {
        return $this
            ->getCollectionQueryBuilder()
            ->find()
            ;
    }

    /**
     * @param array $criteria
     *
     * @return null|object
     */
    public function findOneBy(array $criteria)
    {
        $queryBuilder = $this->getQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);

        return $queryBuilder
            ->setLimit(1)
            ->findOne()
            ;
    }

    /**
     * @param array $criteria
     * @param array $sorting
     * @param int   $limit
     *
     * @return array
     */
    public function findBy(array $criteria, array $sorting = array(), $limit = null)
    {
        $queryBuilder = $this->getCollectionQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);
        $this->applySorting($queryBuilder, $sorting);

        if (null !== $limit) {
            $queryBuilder->setLimit($limit);
        }

        return $queryBuilder
            ->find()
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function add($resource)
    {
        if ( ! $resource instanceof \Persistent) {
            throw new \InvalidArgumentException('This user instance is not supported by the Propel Criteria implementation');
        }

        $resource->save();
    }

    /**
     * {@inheritdoc}
     */
    public function remove($resource)
    {
        if ( ! $resource instanceof \Persistent) {
            throw new \InvalidArgumentException('This user instance is not supported by the Propel Criteria implementation');
        }

        $resource->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function createPaginator(array $criteria = array(), array $sorting = array())
    {
        $queryBuilder = $this->getCollectionQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);
        $this->applySorting($queryBuilder, $sorting);

        return $this->getPaginator($queryBuilder);
    }

    /**
     * @return  Criteria
     */
    protected function getQueryBuilder()
    {
        return $this->createQueryBuilder($this->getAlias());
    }

    /**
     * @return  Criteria
     */
    protected function getCollectionQueryBuilder()
    {
        return $this->createQueryBuilder($this->getAlias());
    }

    /**
     * @param   \Criteria   $queryBuilder
     * @param   array       $criteria
     */
    protected function applyCriteria(\Criteria $queryBuilder, array $criteria = array())
    {
        foreach ($criteria as $property => $value) {
            $name = $this->getPropertyName($property);

            if (null === $value) {
                $queryBuilder->add($name, null, \Criteria::ISNULL);
            } elseif (is_array($value)) {
                $queryBuilder->add($name, null, $value, \Criteria::IN);
            } elseif ('' !== $value) {
                $queryBuilder
                    ->add($name, $value, \Criteria::EQUAL)
                ;
            }
        }
    }

    /**
     * Create a criteria builder.
     *
     * @param   null    $alias
     * @param   boolean $useAlias
     *
     * @return  Criteria
     * @throws  \PropelException
     */
    public function createQueryBuilder($alias = null, $useAlias = true)
    {
        if ( ! $useAlias) {
            return \PropelQuery::from($this->getObjectClass());
        }

        $alias = is_null($alias) ? $this->getAlias() : $alias;

        $queryBuilder = \PropelQuery::from(sprintf('%s %s', $this->getObjectClass(), $alias));
        $queryBuilder->setModelAlias($alias, true);

        return $queryBuilder;
    }

    /**
     * @param \Criteria     $queryBuilder
     * @param array         $sorting
     */
    protected function applySorting(\Criteria $queryBuilder, array $sorting = array())
    {
        foreach ($sorting as $property => $order) {
            if (!empty($order)) {
                if ($order == \Criteria::ASC) {
                    $queryBuilder->addAscendingOrderByColumn($this->getPropertyName($property), $order);
                }

                if ($order == \Criteria::DESC) {
                    $queryBuilder->addDescendingOrderByColumn($this->getPropertyName($property), $order);
                }
            }
        }
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getPropertyName($name)
    {
        if (false === strpos($name, '.')) {
            return $this->getAlias().'.'.$name;
        }

        return $name;
    }

    /**
     * Returns repository object class.
     *
     * @return  string
     */
    public function getObjectClass()
    {
        return $this->repositoryClass;
    }

    /**
     * Returns object peer class;
     *
     * @return  string
     */
    public function getPeerClass()
    {
        return $this->repositoryClass . 'Peer';
    }

    /**
     * @return string
     */
    protected function getAlias()
    {
        return 'o';
    }

}