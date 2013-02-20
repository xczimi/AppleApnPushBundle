<?php

/**
 * This file is part of the AppleApnPushBundle package
 *
 * (c) Vitaliy Zhuk <zhuk2205@gmail.com>
 *
 * For the full copyring and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Apple\ApnPushBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Apple ApnPush Extension
 */
class AppleApnPushExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if ($config['enable']) {
            $loader->load('services.xml');
            $this->processApnPushManager($config, $container);
        }
    }

    /**
     * Process Apn Push managers
     *
     * @param array $config
     * @param ContainerBuilder $container
     */
    protected function processApnPushManager(array $config, ContainerBuilder $container)
    {
        if (!count($config['notification_managers'])) {
            throw new \RuntimeException('Not found apn push managers. Please configure "apple.apn_push.notification_managers" or disable notification system.');
        }

        if (count($config['notification_managers']) > 1) {
            if (empty($config['default_notification_manager'])) {
                throw new \RuntimeException('Please configure parameter "apple.apn_push.default_notification_manager".');
            }

            $defaultNotification = $config['default_notification_manager'];
        } else if (!$config['default_notification_manager']) {
            list ($defaultNotification, $null) = each ($config['notification_managers']);
            reset ($config['notification_managers']);
        } else {
            $defaultNotification = $config['default_notification_manager'];
        }

        if (!isset($config['notification_managers'][$defaultNotification])) {
            throw new \RuntimeException(sprintf(
                'Undefined default notification manager "%s". Allowed managers: "%s".',
                $defaultNotification,
                implode('", "', array_keys($config['notification_managers']))
            ));
        }

        foreach ($config['notification_managers'] as $managerName => $managerInfo) {
            // Set default certificate
            $managerInfo = $this->setDefaultsApnPushManager($managerName, $managerInfo, $config);

            // Check file
            if (!file_exists($managerInfo['certificate']) || !is_file($managerInfo['certificate'])) {
                throw new \RuntimeException(sprintf(
                    'Ceritifcate file "%s" not found.',
                    $managerInfo['certificate']
                ));
            }

            // Check file readable
            if (!is_readable($managerInfo['certificate'])) {
                throw new \RuntimeException(sprintf(
                    'Ceritificate file "%s" not readable!',
                    $managerInfo['certificate']
                ));
            }

            // Create connection
            $connectionId = sprintf('apple.apn_push.%s_connection', $managerName);
            $container->setDefinition($connectionId, new DefinitionDecorator('apple.apn_push.connection'))
                ->setArguments(array(
                    $managerInfo['certificate'],
                    $managerInfo['passphrase'],
                    (bool) $managerInfo['sandbox']
                ));

            // Set read time to connection
            $container->getDefinition($connectionId)
                ->addMethodCall('setReadTime', $managerInfo['connection']['read_time']);

            // Create payload
            $payloadFactoryId = sprintf('apple.apn_push.%s_payload_factory', $managerName);
            $container->setDefinition($payloadFactoryId, new DefinitionDecorator('apple.apn_push.payload_factory'))
                ->setArguments(array());

            // Usage JSON_UNESCAPED_UNICODE
            if (true === $managerInfo['payload_factory']['json_unescaped_unicode']) {
                $container->getDefinition($payloadFactoryId)
                    ->addMethodCall('setJsonUnescapedUnicode', array(true));
            }

            // Create notification service
            $container->setDefinition(sprintf('apple.apn_push.%s_notification', $managerName), new DefinitionDecorator('apple.apn_push.notification'))
                ->setArguments(array(
                    new Reference($payloadFactoryId),
                    new Reference($connectionId)
                ));

            // Logger
            if (!empty($managerInfo['logger']) && !empty($managerInfo['logger']['handlers'])) {
                $loggerName = $managerInfo['logger']['name'] ? $managerInfo['logger']['name'] : 'apple.apn_push.' . $managerName;

                $logger = new DefinitionDecorator('monolog.logger.apple.apn_push');
                $logger->replaceArgument(0, $loggerName);

                foreach ($managerInfo['logger']['handlers'] as $handlerName) {
                    $handlerServiceId = 'monolog.handler.' . $handlerName;
                    $logger
                        ->addMethodCall('pushHandler', array(new Reference($handlerServiceId)));
                }

                $container->setDefinition(sprintf('apple.apn_push.%s_logger', $managerName), $logger);

                $container->getDefinition(sprintf('apple.apn_push.%s_notification', $managerName))
                    ->addMethodCall('setLogger', array(
                        new Reference(sprintf('apple.apn_push.%s_logger', $managerName))
                    ));
            }
        }

        $container->setParameter('apple.apn_push.default_manager', $defaultNotification);
        $container->setParameter('apple.apn_push.managers', array_keys($config['notification_managers']));
    }

    /**
     * Set defaults to apn push manager
     *
     * @param string $managerName
     * @param array $managerInfo
     * @param array $config
     */
    private function setDefaultsApnPushManager($managerName, array $managerInfo, array $config)
    {
        // Add defaults
        $managerInfo += array(
            'service' => null,
            'loggers' => array(),
            'connection' => array(
                'service' => null,
                'read_time' => null
            ),
            'payload_factory' => array(
                'service' => null,
                'json_unescaped_unicode' => null
            ),
            'logger' => array(
                'handlers' => array(),
                'name' => null
            )
        );

        // check certificate file
        if (null === $managerInfo['certificate']) {
            if (false === $managerInfo['sandbox']) {
                if (!$config['default_certificate_file']) {
                    throw new \RuntimeException(sprintf(
                        'Please set ceritificate file for manager "%s" or set default certificate file.',
                        $managerName
                    ));
                }

                $managerInfo['certificate'] = $config['default_certificate_file'];
            } else {
                if (!$config['default_sandbox_certificate_file']) {
                    throw new \RuntimeException(sprintf(
                        'Please set certificate file for manager "%s" or set default certificate file for sandbox mode.',
                        $managerName
                    ));
                }

                $managerInfo['certificate'] = $config['default_sandbox_certificate_file'];
            }
        }

        // Not found passphrase
        if (null === $managerInfo['passphrase']) {
            if (false === $managerInfo['sandbox']) {
                $managerInfo['passphrase'] = $config['default_passphrase'];
            } else {
                $managerInfo['passphrase'] = $config['default_sandbox_passphrase'];
            }
        }

        // Not found read time
        if (null === $managerInfo['connection']['read_time']) {
            $managerInfo['connection']['read_time'] = $config['default_read_time'];
        }

        // Not found json unescaped unicode
        if (null === $managerInfo['payload_factory']['json_unescaped_unicode']) {
            $managerInfo['payload_factory']['json_unescaped_unicode'] = $config['default_json_unescaped_unicode'];
        }

        // Add global loggers
        foreach ($config['global_logger_handlers'] as $logger) {
            if (!in_array($logger, $managerInfo['logger']['handlers'])) {
                $managerInfo['logger']['handlers'][] = $logger;
            }
        }

        return $managerInfo;
    }
}