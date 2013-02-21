<?php

/**
 * This file is part of the AppleApnPushBundle package
 *
 * (c) Vitaliy Zhuk <zhuk2205@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Apple\ApnPushBundle\Tests\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Apple\ApnPushBundle\DependencyInjection\Configuration;

/**
 * Abstract configuration test
 */
abstract class AbstractConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Set configuration exception
     */
    protected function setConfigurationException()
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
    }
}