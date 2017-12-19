<?php

/**
 * * This file is part of MetaModels/filter_text.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage FilterTextBundle
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/filter_text/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MenAtWork\CtoCommunicationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * This is the Bundle extension.
 */
class CtoCommunicationBundle extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
//        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
//        $loader->load('services.yml');
    }
}
