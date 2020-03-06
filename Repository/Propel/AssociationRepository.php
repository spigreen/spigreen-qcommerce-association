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

use QualityPress\QCommerce\Component\Association\Model\AssociableInterface;

/**
 * AssociationRepository
 *
 * @author QualityPress
 */
class AssociationRepository extends AbstractRepository
{

    /**
     * Verificar todas as associações.
     *
     * @return  \PropelCollection
     */
    public function findAssociatedObjects()
    {
        return $this->createQueryBuilder()
            ->joinWith($this->getRelationClassName())
            ->find()
        ;
    }

    /**
     * Criar o ObjectQuery para produtos associados.
     *
     * @param   AssociableInterface   $associableObject
     * @param   \Criteria|null        $queryBuilder
     *
     * @return  \PropelCollection
     */
    public function getAssociatedObjectsByOriginObjectQueryBuilder(AssociableInterface $associableObject, \Criteria $queryBuilder = null)
    {
        $relation = $this->getProbableRelation(true);
        $leftColumns = $relation->getLeftColumns();

        if (empty($leftColumns) && ! isset($leftColumns[0])) {
            throw new \InvalidArgumentException('Colunas à esquerda (origem) não localizadas');
        }

        $leftColumn = $leftColumns[0];
        $columnRelationName = $leftColumn->getPhpName();

        $queryBuilder = $queryBuilder ?: $this->createQueryBuilder();

        return $queryBuilder
            ->{'filterBy' . $columnRelationName}($associableObject->getId())
            ->joinWith($this->getRelationClassName())
            ->joinWith($this->getRelationClassName() .'.'. $this->getThirdRelationName($associableObject))
        ;
    }

    /**
     * Localizar objetos associados através de um objeto origem.
     *
     * @param   AssociableInterface     $associableObject
     *
     * @return  \PropelCollection
     */
    public function findAssociatedObjectsByOriginObject(AssociableInterface $associableObject)
    {
        return $this->getAssociatedObjectsByOriginObjectQueryBuilder($associableObject)->find();
    }

    /**
     * Localizar objetos associados através de um objeto origem, e por tipo.
     *
     * @param   AssociableInterface     $associableObject
     * @param   string                  $type
     *
     * @return  \PropelCollection       Lista de objetos associados
     */
    public function findAssociatedObjectsByOriginObjectAndType(AssociableInterface $associableObject = null, $type = null)
    {
        return $this
            ->getAssociatedObjectsByOriginObjectAndType($associableObject, $type)
            ->find()
        ;
    }

    /**
     * Retorna um 'Criteria' com os filtros de objetos associados através de um objeto origem e tipo.
     *
     * @param   AssociableInterface|null    $associableObject
     * @param   null                        $type
     *
     * @return  \Criteria
     */
    public function getAssociatedObjectsByOriginObjectAndType(AssociableInterface $associableObject = null, $type = null)
    {
        $queryBuilder = $this->createQueryBuilder();

        if (null !== $associableObject) {
            $this->getAssociatedObjectsByOriginObjectQueryBuilder($associableObject, $queryBuilder);
        }

        if (null !== $type) {
            $this->getAssociatedObjectsByTypeQueryBuilder($type, $queryBuilder);
        }

        return $queryBuilder;
    }

    /**
     * Retorna o nome da relação referente ao terceiro nível de associação.
     *
     * @param   AssociableInterface     $associableObject
     *
     * @return  null|string
     */
    public function getThirdRelationName(AssociableInterface $associableObject)
    {
        $relationClassName = $this->getRelationClassNamespace(false) . 'Peer';
        $tableMap = $relationClassName::getTableMap();

        foreach ($tableMap->getRelations() as $relation) {
            if ($relation->getRightTable()->getClassname() == get_class($associableObject)) {
                return $relation->getName();
            }
        }

        return null;
    }

    /**
     * Localizar criteria de objetos associados por tipo.
     *
     * @param String            $type
     * @param \Criteria|null    $queryBuilder
     *
     * @return \PropelCollection
     */
    public function getAssociatedObjectsByTypeQueryBuilder($type, \Criteria $queryBuilder = null)
    {
        $queryBuilder = $queryBuilder ?: $this->createQueryBuilder();

        return $queryBuilder
            ->filterByType($type)
            ->joinWith($this->getRelationClassName())
        ;
    }

    /**
     * Localizar objetos associados, pesquisando pelo tipo.
     *
     * @param   string  $type
     *
     * @return  \PropelCollection
     */
    public function findAssociatedObjectsByType($type)
    {
        return $this->getAssociatedObjectsByTypeQueryBuilder($type)
            ->find()
        ;
    }

    /**
     * @return  \TableMap
     */
    public function getTableMap()
    {
        $peer = $this->getPeerClass();

        return $peer::getTableMap();
    }

    /**
     * @param   boolean     $fromOriginObject
     *
     * @return null|string
     */
    public function getRelationClassNamespace($fromOriginObject = false)
    {
        return $this->getProbableRelation($fromOriginObject)->getLocalTable()->getClassname();
    }

    /**
     * @param   boolean     $fromOriginObject
     *
     * @return  string
     */
    public function getRelationPluralName($fromOriginObject = false)
    {
        return $this->getProbableRelation($fromOriginObject)->getPluralName();
    }

    /**
     * @param   boolean     $fromOriginObject
     *
     * @return  string
     */
    public function getRelationClassName($fromOriginObject = false)
    {
        return $this->getProbableRelation($fromOriginObject)->getName();
    }

    /**
     * Localizar as relações da tabela.
     *
     * @return  \RelationMap[]
     */
    public function getRelations()
    {
        return $this->getTableMap()->getRelations();
    }

    /**
     * Localizar o provável objeto de relação.
     *
     * @param   boolean             $fromOriginObject
     *
     * @return  \RelationMap|null
     */
    public function getProbableRelation($fromOriginObject = false)
    {
        $relationType = ($fromOriginObject) ? \RelationMap::MANY_TO_ONE : \RelationMap::ONE_TO_MANY;

        foreach ($this->getRelations() as $relation) {
            if ($relation->getType() === $relationType) {
                return $relation;
            }
        }

        return null;
    }

}