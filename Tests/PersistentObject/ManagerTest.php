<?php

/**
 * @namespace
 */
namespace RedBullet\PersistentObjectsBundle\Tests;

use RedBullet\PersistentObjectsBundle\PersistentObject\Manager;

/**
 * ManagerTest class
 * 
 * @author Kevin Dew <kev@redbullet.co.uk>
 */
class ManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $manager = new Manager(
            __DIR__ . '/Fixtures',
            array(
                'test' => '\StdClass',
                'test2' => '\StdClass'
            )
        );

        $testObject = new \StdClass();
        $testObject->var = 'test';

        $this->assertEquals($testObject, $manager->get('test'));
        $this->assertNull($manager->get('test2'));
        $this->assertEquals($testObject, $manager->get('test2', $testObject));
    }

    public function testSet()
    {
        $manager = new Manager(
            __DIR__ . '/Fixtures',
            array(
                 'test' => '\StdClass',
                'iterator' => '\OuterIterator'
            )
        );

        $testObject = new \StdClass();

        $manager->set('test', $testObject);

        $this->assertEquals($manager->get('test'), $testObject);

        // testing interface
        $testObject = new \AppendIterator();

        $manager->set('iterator', $testObject);

        $this->assertEquals($testObject, $manager->get('iterator'));
    }

    /**
     * @expectedException    \InvalidArgumentException
     */
    public function testBadSet()
    {
        $manager = new Manager(
            __DIR__ . '/Fixtures', array('test' => '\StdClass')
        );

        $testObject = new \DateTime();

        $manager->set('test', $testObject);

        $this->assertEquals($testObject, $manager->get('test'));
    }

    public function testPersist()
    {
        $path = sys_get_temp_dir() . '/' . uniqid();

        $manager = new Manager(
            $path, array('test' => '\StdClass')
        );

        $object = new \StdClass();

        $manager->set('test', $object);

        $manager->persist('test');

        $filePath = $path . '/test.php.meta';

        $this->assertFileExists($filePath);

        $this->assertEquals(serialize($object), file_get_contents($filePath));
    }

    public function testReset()
    {
        $manager = new Manager(
            __DIR__ . '/Fixtures',
            array(
                'test' => '\StdClass',
            )
        );

        $testObject = new \StdClass();
        $testObject->var = 'test';

        $manager->get('test')->var = 'new value';

        $this->assertNotEquals($testObject, $manager->get('test'));

        $manager->reset('test');

        $this->assertEquals($testObject, $manager->get('test'));
    }

    public function testIsMapped()
    {
        $manager = new Manager(
            __DIR__ . '/Fixtures',
            array(
                'test' => '\StdClass',
            )
        );

        $this->assertTrue($manager->isMapped('test'));
        $this->assertFalse($manager->isMapped('test2'));
    }

    public function testGetClassName()
    {
        $manager = new Manager(
            __DIR__ . '/Fixtures',
            array(
                'test' => '\StdClass',
            )
        );

        $this->assertEquals('\StdClass', $manager->getClassName('test'));

        $this->assertNotEquals('\Iterator', $manager->getClassName('test'));
    }

    public function testExists()
    {
        $manager = new Manager(
            __DIR__ . '/Fixtures',
            array(
                'test' => '\StdClass',
                'test2' => '\StdClass'
            )
        );

        $this->assertTrue($manager->exists('test'));
        $this->assertFalse($manager->exists('test2'));
    }
}
