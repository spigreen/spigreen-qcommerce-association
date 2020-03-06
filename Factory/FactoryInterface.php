<?php

/**
 * This file is part of the QualityPress package.
 *
 * (c) QualityPress
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace QualityPress\QCommerce\Component\Association\Factory;

/**
 * FactoryInterface
 *
 * @author QualityPress
 */
interface FactoryInterface
{

    /**
     * Efetuar a criação de um novo objeto.
     *
     * @return  object
     */
    public function createNew();

    /**
     * Retornar a classe definida para criação do objeto.
     *
     * @return  string
     */
    public function getClass();

    /**
     * Definir a classe para criação de um novo objeto.
     *
     * @param   string  $class
     *
     * @return  self
     */
    public function setClass($class);

}