<?php

/**
 * This file is part of the AppleApnPushBundle package
 *
 * (c) Vitaliy Zhuk <zhuk2205@gmail.com>
 *
 * For the full copyring and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Apple\ApnPushBundle\Tests\ApnPush;

use Apple\ApnPushBundle\ApnPush\ApnPushManager;
use Symfony\Component\DependencyInjection\Container;

/**
 * Apn push manager tests
 */
class ApnPushManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->container = $this->getMock(
            'Symfony\Component\DependencyInjection\Container',
            array('get', 'getParameter', 'has')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
        unset ($this->container);
    }

    /**
     * Get apn push manager
     *
     * @param Container $container
     */
    public function getApnManager(Container $container)
    {
        return new ApnPushManager($container);
    }

    /**
     * Test get default manager key
     */
    public function testGetDefaultManagerKey()
    {
        $container = $this->container;
        $container->expects($this->once())
            ->method('getParameter')
            ->with('apple.apn_push.default_manager');

        $manager = $this->getApnManager($container);
        $manager->getDefaultManagerKey();
    }

    /**
     * Test get manager keys
     */
    public function testGetManagerKeys()
    {
        $container = $this->container;
        $container->expects($this->once())
            ->method('getParameter')
            ->with('apple.apn_push.managers');

        $manager = $this->getApnManager($container);
        $manager->getManagerKeys();
    }

    /**
     * Test get manager with manager not found
     *
     * @expectedException Apple\ApnPushBundle\Exceptions\ManagerNotFoundException
     */
    public function testGetManager_NotFound()
    {
        $manager = $this->getApnManager(new Container);
        $manager->getManager('foo');
    }

    /**
     * Test get default manager key
     */
    public function testGetManager_Default()
    {
        $container = $this->container;
        $container->expects($this->once())
            ->method('getParameter')
            ->with('apple.apn_push.default_manager')
            ->will($this->returnValue('default'));

        $container->expects($this->once())
            ->method('get')
            ->with('apple.apn_push.default_notification');

        $container->expects($this->once())
            ->method('has')
            ->with('apple.apn_push.default_notification')
            ->will($this->returnValue(true));

        $manager = $this->getApnManager($container);
        $manager->getManager();
    }

    /**
     * Test get manager by key
     */
    public function testGetManager_ByKey()
    {
        $container = $this->container;
        $container->expects($this->never())
            ->method('getParameter');

        $container->expects($this->once())
            ->method('has')
            ->with('apple.apn_push.key_notification')
            ->will($this->returnValue(true));

        $container->expects($this->once())
            ->method('get')
            ->with('apple.apn_push.key_notification');

        $manager = $this->getApnManager($container);
        $manager->getManager('key');
    }
}