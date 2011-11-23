<?php

/**
 * @namespace
 */
namespace RedBullet\PersistentObjectsBundle\Tests;

use RedBullet\PersistentObjectsBundle\DependencyInjection\RedBulletPersistentObjectsExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * RedBulletPersistentObjectsExtensionTest class
 * 
 * @author Kevin Dew <kev@redbullet.co.uk>
 */
class RedBulletPersistentObjectsExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyMapping()
    {
        $loader = new RedBulletPersistentObjectsExtension();
        $containerBuilder = new ContainerBuilder();

        $loader->load(
            array(),
            $containerBuilder
        );

        $this->assertEquals(
            array(),
            $containerBuilder->getParameter('red_bullet_persistent_objects.mapping')
        );
    }

    public function testMapping()
    {
        $loader = new RedBulletPersistentObjectsExtension();
        $containerBuilder = new ContainerBuilder();

        $mapping = array(
            'aGoodKey' => 'MyNonExistantClass',
            'another_key' => 'MyNonExistantClass',
            'a.dotted.key' => 'MyNonExistantClass'
        );

        $loader->load(array(
                array(
                    'mapping' => $mapping
                )
            ),
            $containerBuilder
        );

        $this->assertEquals(
            $mapping,
            $containerBuilder->getParameter('red_bullet_persistent_objects.mapping')
        );
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testBadMapping()
    {
        $loader = new RedBulletPersistentObjectsExtension();
        $containerBuilder = new ContainerBuilder();

        $mapping = array(
            'aGoodKey' => 'MyNonExistantClass',
            'a bad key' => 'MythicalClass'
        );

        $loader->load(array(
                array(
                    'mapping' => $mapping
                )
            ),
            $containerBuilder
        );

        $this->assertEquals(
            $mapping,
            $containerBuilder->getParameter('red_bullet_persistent_objects.mapping')
        );
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testDuplicateMapping()
    {
        $loader = new RedBulletPersistentObjectsExtension();
        $containerBuilder = new ContainerBuilder();

        $mapping = array(
            'aGoodKey' => 'MyNonExistantClass',
            'agoodkey' => 'MythicalClass'
        );

        $loader->load(array(
                array(
                    'mapping' => $mapping
                )
            ),
            $containerBuilder
        );

        $this->assertEquals(
            $mapping,
            $containerBuilder->getParameter('red_bullet_persistent_objects.mapping')
        );
    }
}
