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

use QualityPress\QCommerce\Component\Association\Model\AssociableInterface;
use QualityPress\QCommerce\Component\Association\Model\AssociationInterface;

/**
 * AssociationManagerInterface
 *
 * @author QualityPress
 */
interface AssociationManagerInterface
{

    /**
     * Criar um objeto de associação.
     *
     * @param   string|null         $associationType
     *
     * @return  AssociationInterface
     */
    public function createNew($associationType = null);

    /**
     * Localizar os objetos associados.
     *
     * @param   AssociableInterface     $originAssociation
     * @param   string                  $type
     *
     * @return  \PropelCollection
     */
    public function getAssociatedObjects(AssociableInterface $originAssociation = null, $type = null);

    /**
     * Associar um novo objeto.
     *
     * @param   AssociationInterface    $association
     * @param   AssociableInterface     $associatedObject
     */
    public function addAssociatedObject(AssociationInterface $association, AssociableInterface $associatedObject);

    /**
     * Remover um objeto associado.
     *
     * @param   AssociationInterface    $association
     * @param   AssociableInterface     $associatedObject
     */
    public function removeAssociatedObject(AssociationInterface $association, AssociableInterface $associatedObject);

    /**
     * Verificar se o objeto está associado.
     *
     * @param   AssociationInterface    $association
     * @param   AssociableInterface     $associatedObject
     *
     * @return  bool
     */
    public function hasAssociatedObject(AssociationInterface $association, AssociableInterface $associatedObject);

}