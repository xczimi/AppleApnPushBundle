<?php

/**
 * This file is part of the AppleApnPushBundle package
 *
 * (c) Vitaliy Zhuk <zhuk2205@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Apple\ApnPushBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Apple System Configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('apple_apn_push');

        $this->configApnPush($rootNode);

        return $treeBuilder;
    }

    /**
     * Create apn push connection read time option
     *
     * @param string $name
     */
    private function createApnPushConnectionReadTimeTree($name, $defaultValue = array(1, 0))
    {
        $builder = new TreeBuilder();
        $node = $builder->root($name);

        $node
            ->defaultValue($defaultValue)
            ->validate()
                ->ifTrue(function($v) { return !$v ? null :  count($v) != 2; })
                ->thenInvalid('Must be two parameters, [second, milisecond].')
            ->end()
            ->validate()
                ->ifTrue(function($v) { return !$v ? null : !is_int($v[0]); })
                ->thenInvalid('First parameter must be integer.')
            ->end()
            ->validate()
                ->ifTrue(function($v) { return !$v ? null : $v[0] < 0; })
                ->thenInvalid('First parameter "seconds" can\'t be less then zero.')
            ->end()
            ->validate()
                ->ifTrue(function($v) { return !$v ? null : !is_int($v[1]); })
                ->thenInvalid('Second parameter must be a integer.')
            ->end()
            ->validate()
                ->ifTrue(function($v) { return !$v ? null : $v[1] < 0; })
                ->thenInvalid('Second parameter "milisecond" can\'t be less then zero.')
            ->end()
            ->prototype('scalar')->end();

        return $node;
    }

    /**
     * Set configuration for apn push system
     *
     * @param ArrayNodeDefinition $rootNode
     */
    protected function configApnPush(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                // Enable push notification
                ->booleanNode('enable')
                    ->defaultFalse()
                    ->info('Enable apn push notification')
                ->end()

                // Default notification manager
                ->scalarNode('default_notification_manager')
                    ->defaultNull()
                    ->info('Default notification manager')
                ->end()

                // Default json unescaped unicode option
                ->scalarNode('default_json_unescaped_unicode')
                    ->defaultNull()
                    ->info('Usage JSON_UNESCAPED_UNICODE as default in all managers')
                    ->validate()
                        ->ifTrue(function($v) { return $v === null ? false : !is_bool($v); })
                        ->thenInvalid('Option "json_unescaped_unicode" must be a boolean or null.')
                    ->end()
                    ->validate()
                        ->ifTrue(function($v) { return $v === true ? version_compare(PHP_VERSION, '5.4.0', '<') : false; })
                        ->thenInvalid('Can\'t use JSON_UNESCAPED_UNICODE option at PHP < 5.4')
                    ->end()
                ->end()

                // Connection read time
                ->append($this->createApnPushConnectionReadTimeTree('default_read_time'), array(1, 0))

                // Global loggers
                ->arrayNode('global_logger_handlers')
                    ->prototype('scalar')->end()
                ->end()

                // Default certificate file
                ->scalarNode('default_certificate_file')
                    ->defaultNull()
                    ->info('Default certificate file for all manager with disable sandbox mode.')
                    ->end()

                ->scalarNode('default_passphrase')
                    ->defaultNull()
                    ->info('Default passphrase for certificate file with disable sandbox mode')
                    ->end()

                // Default certificate file for sandbox mode
                ->scalarNode('default_sandbox_certificate_file')
                    ->defaultNull()
                    ->info('Default certificate file for all manager with enable sandbox mode.')
                    ->end()

                ->scalarNode('default_sandbox_passphrase')
                    ->defaultNull()
                    ->info('Default passphrase for certificate file with enable sandbox mode')
                    ->end()

                // Notification managers
                ->arrayNode('notification_managers')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                    ->children()
                        // Payload factory configuration
                        ->arrayNode('payload_factory')
                            ->children()
                                ->scalarNode('json_unescaped_unicode')
                                    ->defaultNull()
                                    ->info('Usage JSON_UNESCAPED_UNICODE in json_encode function. Only PHP >= 5.4')
                                    ->validate()
                                        ->ifTrue(function($v) { return $v === null ? false : !is_bool($v); })
                                            ->thenInvalid('Option "json_unescaped_unicode" must be a boolean or null.')
                                        ->end()
                                    ->validate()
                                        ->ifTrue(function($v) { return $v === true ? version_compare(PHP_VERSION, '5.4.0', '<') : false; })
                                            ->thenInvalid('Can\'t use JSON_UNESCAPED_UNICODE option at PHP < 5.4')
                                        ->end()
                                    ->end()
                            ->end()
                        ->end()

                        // Connection configuration
                        ->arrayNode('connection')
                            ->children()
                                ->append($this->createApnPushConnectionReadTimeTree('read_time'))
                            ->end()
                        ->end()

                        // Sandbox
                        ->booleanNode('sandbox')
                            ->defaultFalse()
                            ->info('Usage sandbox mode')
                            ->end()

                        // Certificate file
                        ->scalarNode('certificate')
                            ->defaultNull()
                            ->info('Path to certificate file')
                            ->example('%kernel.root_dir%/apn_push/certificate.pem')
                            ->end()

                        // Passphrase
                        ->scalarNode('passphrase')
                            ->defaultNull()
                            ->info('Passphrase a file certificate')
                            ->end()

                        // Loggers
                        ->arrayNode('logger')
                            ->children()
                                ->arrayNode('handlers')
                                    ->defaultValue(array())
                                    ->prototype('scalar')
                                    ->end()
                                ->end()
                                ->scalarNode('name')
                                    ->defaultNull()
                                    ->info('Logger name')
                                    ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}