<?php

/**
 * This file is part of the QualityPress package.
 *
 * (c) QualityPress
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace QualityPress\QCommerce\Component\Association\Manager;

use QualityPress\QCommerce\Component\Association\Factory\FactoryInterface;
use QualityPress\QCommerce\Component\Association\Model\AssociableInterface;
use QualityPress\QCommerce\Component\Association\Model\AssociationInterface;
use QualityPress\QCommerce\Component\Association\Propel\AssociacaoProdutoPeer;
use QualityPress\QCommerce\Component\Association\Propel\AssociacaoProdutoProduto;
use QualityPress\QCommerce\Component\Association\Propel\AssociacaoProdutoProdutoPeer;
use QualityPress\QCommerce\Component\Association\Repository\Propel\AbstractRepository;
use QualityPress\QCommerce\Component\Association\Repository\Propel\AssociationRepository;

/**
 * AssociationManager
 *
 * @author QualityPress
 */
class AssociationManager implements AssociationManagerInterface
{

    /**
     * @var FactoryInterface
     */
    protected $associationFactory;

    /**
     * @var AbstractRepository
     */
    protected $associationRepository;

    /**
     * Constructor.
     *
     * @param FactoryInterface   $associationFactory
     * @param AbstractRepository $repository
     */
    public function __construct(FactoryInterface $associationFactory, AbstractRepository $repository)
    {
        $this->associationFactory       = $associationFactory;
        $this->associationRepository    = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function createNew($associationType = null)
    {
        $association = $this->associationFactory->createNew();

        if (null !== $associationType) {
            $association->setType($associationType);
        }

        return $association;
    }

    /**
     * {@inheritdoc}
     */
    public function addAssociatedObject(AssociationInterface $association, AssociableInterface $associatedObject)
    {
        $associatedObjectClass      = $this->associationRepository->getRelationClassNamespace(false);
        $intermediateAssociation    = new $associatedObjectClass();
        $associatedObjectTm         = $intermediateAssociation->getPeer()->getTableMap();

        /* @var $relation \RelationMap */
        $rightMethod = null;
        foreach ($associatedObjectTm->getRelations() as $relation) {
            try {
                $rightMethod = 'set' . $relation->getName();
                $intermediateAssociation->{$rightMethod}($associatedObject);
            }
            catch (\Exception $e) {}
            catch (\Throwable $e) {}
        }

        $method = 'add' . $this->associationRepository->getRelationClassName(false);
        $association->{$method}($intermediateAssociation);
    }

    /**
     * {@inheritdoc}
     */
    public function removeAssociatedObject(AssociationInterface $association, AssociableInterface $associatedObject)
    {
        $method = 'get' . $this->associationRepository->getRelationPluralName(false);
        $assoc  = $association->{$method}();

        foreach ($assoc as $objectAssoc) {
            $tableMap = $objectAssoc->getPeer()->getTableMap();

            /* @var $relation \RelationMap */
            foreach ($tableMap->getRelations() as $relation) {
                $rightMethod = 'get' . $relation->getName();

                try {
                    if ($associatedObject->getId() === $objectAssoc->{$rightMethod}()->getId()) {
                        $objectAssoc->delete();
                    }
                } catch (\Exception $e) {}
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasAssociatedObject(AssociationInterface $association, AssociableInterface $associatedObject)
    {
        $method = 'get' . $this->associationRepository->getRelationPluralName(false);
        $assoc  = $association->{$method}();

        foreach ($assoc as $objectAssoc) {
            $tableMap = $objectAssoc->getPeer()->getTableMap();

            /* @var $relation \RelationMap */
            foreach ($tableMap->getRelations() as $relation) {
                $rightMethod = 'get' . $relation->getName();

                try {
                    if ($associatedObject->getId() === $objectAssoc->{$rightMethod}()->getId()) {
                        return true;
                    }
                } catch (\Exception $e) {}
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociatedObjects(AssociableInterface $originAssociation = null, $type = null)
    {
        $collection = $this->associationRepository->findAssociatedObjectsByOriginObjectAndType($originAssociation, $type);

        return $this->transformAssociatedCollectionToArray($collection);
    }

    /**
     * Busca os objetos associados de segundo nível da associação a partir de uma associação tendo como opção o filtro por tipo.
     *
     * @param   AssociableInterface|null    $originAssociation
     * @param   null                        $type
     *
     * @return  \ArrayObject
     */
    public function getAssociatedObjectFromAssociatedObjects(AssociableInterface $originAssociation = null, $type = null)
    {
        $collection = $this->getAssociatedObjects($originAssociation, $type);
        $relationName = $this->associationRepository->getThirdRelationName($originAssociation);

        if (null === $relationName) {
            throw new \InvalidArgumentException();
        }

        $data = array();

        foreach ($collection as $associatedObject) {
            $data[] = $associatedObject->{'get' . $relationName}();
        }

        return new \ArrayObject($data);
    }

    /**
     * Percorre a coleção de objetos de associação, localica o relacionamento provàvel e armazena-o em um ArrayObject.
     * Desta forma, o retorno será somente os objetos relacionados ao invés de uma coleçãoo de associações.
     * Além disso, este método transformará as coleções dos objetos relacionados em objeto de coleção único.
     *
     * @param   $collection   \ArrayObject|array
     *
     * @return  \ArrayObject
     */
    protected function transformAssociatedCollectionToArray($collection)
    {
        $data   = array();
        $method = 'get' . $this->associationRepository->getRelationPluralName();

        foreach ($collection as $associatedObject) {
            $data = array_merge((array) $data, $associatedObject->{$method}()->getData());
        }

        return new \ArrayObject($data);
    }

}