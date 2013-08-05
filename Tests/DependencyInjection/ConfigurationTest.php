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
 * Configuration tests
 */
class ConfigurationApnPushTest extends AbstractConfigurationTest
{
    /**
     * Apn push configuration defaults
     */
    public function testDefaults()
    {
        $config = $this->process(array());
        $this->assertNull($config['default_manager']);
        $this->assertNull($config['default_json_unescaped_unicode']);
        $this->assertEquals(array(1, 0), $config['default_read_time']);
        $this->assertCount(0, $config['global_logger_handlers']);
        $this->assertNull($config['default_certificate_file']);
        $this->assertNull($config['default_passphrase']);
        $this->assertNull($config['default_sandbox_certificate_file']);
        $this->assertNull($config['default_sandbox_passphrase']);
        $this->assertCount(0, $config['managers']);
    }

    /**
     * @dataProvider providerReadTime
     */
    public function testDefaultReadTime($readTime, $error)
    {
        if (true === $error) {
            $this->setConfigurationException();
        }

        $config = $this->process(array(
            'default_read_time' => $readTime
        ));
    }

    /**
     * Provider for testing read time errors
     */
    public function providerReadTime()
    {
        return array(
            array(array(1, 0), false),
            array(array(1, 500), false),
            array(array(), false),
            array(null, false),
            array(array(1), true),
            array(array(-1, 0), true),
            array(array(1, -500), true)
        );
    }

    /**
     * @dataProvider providerJsonUnescapedUnicode
     */
    public function testDefaultJsonUnescapedUnicode($option, $error)
    {
        if (true === $error) {
            $this->setConfigurationException();
        }

        $config = $this->process(array(
            'default_json_unescaped_unicode' => $option
        ));
    }

    /**
     * Provider for testing json_unespcaped_unicode option
     */
    public function providerJsonUnescapedUnicode()
    {
        $php5_4 = version_compare(PHP_VERSION, '5.4.0', '>=');

        $datas = array(
            array('foo', true),
            array('bar', true),
            array(11, true),
            array(1, true),
            array(0, true),
            array(false, false),
            array(true, $php5_4 ? false : true)
        );

        return $datas;
    }

    /**
     * @dataProvider providerLoggerHandlers
     */
    public function testDefaultLoggers($handlers, $error)
    {
        if (true === $error) {
            $this->setConfigurationException();
        }

        $config = $this->process(array(
            'global_logger_handlers' => $handlers
        ));
    }

    /**
     * Provider logger handlers
     */
    public function providerLoggerHandlers()
    {
        return array(
            array('foo', true),
            array(1, true),
            array(array(), false),
            array(array('foo', 'bar'), false)
        );
    }

    /**
     * Default notification test
     */
    public function testNotificationsDefault()
    {
        $config = $this->process(array(
            'managers' => array(
                'default' => null
            )
        ));

        $manager = $config['managers']['default'];
        $this->assertFalse($manager['sandbox']);
        $this->assertNull($manager['certificate']);
        $this->assertNull($manager['passphrase']);
        $this->assertArrayNotHasKey('connection', $manager);
        $this->assertArrayNotHasKey('payload_factory', $manager);
    }

    /**
     * @dataProvider providerReadTime
     */
    public function testNotificationReadTime($readTime, $error)
    {
        if (true === $error) {
            $this->setConfigurationException();
        }

        $config = $this->process(array(
            'managers' => array(
                'default' => array(
                    'connection' => array(
                        'read_time' => $readTime
                    )
                )
            )
        ));
    }

    /**
     * @dataProvider providerJsonUnescapedUnicode
     */
    public function testNotificationPayloadJsonUnescapedUnicode($option, $error)
    {
        if (true === $error) {
            $this->setConfigurationException();
        }

        $config = $this->process(array(
            'managers' => array(
                'default' => array(
                    'payload_factory' => array(
                        'json_unescaped_unicode' => $option
                    )
                )
            )
        ));
    }

    /**
     * @dataProvider providerLoggerHandlers
     */
    public function testNotificationLoggerHandlers($handlers, $error)
    {
        if (true === $error) {
            $this->setConfigurationException();
        }

        $config = $this->process(array(
            'managers' => array(
                'default' => array(
                    'logger' => array(
                        'handlers' => $handlers
                    )
                )
            )
        ));
    }

    /**
     * Base testing notification
     */
    public function testNotificationBase()
    {
        $config = $this->process(array(
            'managers' => array(
                'default' => array(
                    'certificate' => 'foo',
                    'passphrase' => 'bar',
                    'sandbox' => true,
                    'connection' => array(
                        'read_time' => array(1, 0)
                    ),
                    'payload_factory' => array(
                        'json_unescaped_unicode' => false
                    )
                ),

                'default_2' => null
            )
        ));
    }

    /**
     * Process configuration
     *
     * @param array $config
     * @param boolean $apnPush
     * @return array
     */
    protected function process(array $config, $apnPush = true)
    {
        $processor = new Processor();

        if (true === $apnPush) {
            $config = array('apn_push' => $config);
        }

        return $processor->processConfiguration(new Configuration(), $config);
    }
}