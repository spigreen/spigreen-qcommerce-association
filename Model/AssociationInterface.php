<?php

/**
 * This file is part of the QualityPress package.
 *
 * (c) QualityPress
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace QualityPress\QCommerce\Component\Association\Model;

/**
 * AssociationInterface
 *
 * @author QualityPress
 */
interface AssociationInterface
{

    /**
     * Localizar o modelo de associação.
     *
     * @return  string
     */
    public function getType();

}