<?php

/**
 * This file is part of the QualityPress package.
 *
 * (c) QualityPress
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace QualityPress\QCommerce\Component\Association\Bridge\Pimple;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * AssociationProvider
 *
 * @author QualityPress
 */
class AssociationProvider implements ServiceProviderInterface
{

    /**
     * {@inheritdoc}
     *
     * Config example:
     *
     * $pimple['quality.association.mapping'] = array(
     *      'product' => array(
     *          'class' =>
     *      )
     * );
     *
     * Usage example:
     * $pimple['quality.association.manager.product']->createNew();
     * $pimple['quality.association.manager.product']->getAssociatedObjects($associationObject, 'related_products');
     */
    public function register(Container $pimple)
    {
        if ( ! isset($pimple['quality.association.mapping'])) {
            return;
        }

        foreach ($pimple['quality.association.mapping'] as $key => $mapping) {
            $alias = $key;

            if ( ! isset($mapping['class'])) {
                throw new \InvalidArgumentException('Class key value must be defined!');
            }

            // Object class
            $pimple[sprintf('quality.association.%s.class', $alias)] = $mapping['class'];

            // Factory
            $pimple[sprintf('quality.association.factory.%s', $alias)] = function($pimple) use ($alias, $mapping) {
                $factoryClass = (isset($mapping['factory'])) ? $mapping['factory'] : 'QualityPress\\QCommerce\\Component\\Association\\Factory\\Factory';

                return new $factoryClass($pimple[sprintf('quality.association.%s.class', $alias)]);
            };

            // Repository
            $pimple[sprintf('quality.association.repository.%s', $alias)] = function($pimple) use ($alias, $mapping) {
                $repositoryClass = (isset($mapping['repository'])) ? $mapping['repository'] : 'QualityPress\\QCommerce\\Component\\Association\\Repository\\Propel\\AssociationRepository';

                return new $repositoryClass($pimple[sprintf('quality.association.%s.class', $alias)]);
            };

            // Manager
            $pimple[sprintf('quality.association.manager.%s', $alias)] = function($pimple) use ($alias, $mapping) {
                $managerClass = (isset($mapping['manager'])) ? $mapping['manager'] : 'QualityPress\\QCommerce\\Component\\Association\\Manager\\AssociationManager';

                return new $managerClass($pimple[sprintf('quality.association.factory.%s', $alias)], $pimple[sprintf('quality.association.repository.%s', $alias)]);
            };
        }
    }

}