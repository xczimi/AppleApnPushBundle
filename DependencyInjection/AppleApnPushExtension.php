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

        if (!empty($config['managers'])) {
            $loader->load('services.xml');
            $this->processApnPushManager($config, $container);
        }
    }

    /**
     * Process Apn Push managers
     *
     * @param array $config
     * @param ContainerBuilder $container
     * @throws \RuntimeException
     */
    protected function processApnPushManager(array $config, ContainerBuilder $container)
    {
        // Managers not found
        if (!count($config['managers'])) {
            throw new \RuntimeException('Not found apn push managers. Please configure "apple.apn_push.managers" or disable notification system.');
        }

        // Get default manager key
        if (count($config['managers']) > 1) {
            if (empty($config['default_manager'])) {
                throw new \RuntimeException('Please configure parameter "apple.apn_push.default_manager".');
            }

            $defaultManager = $config['default_manager'];
        } else if (!$config['default_manager']) {
            list ($defaultManager, $null) = each($config['managers']);
            reset ($config['managers']);
        } else {
            $defaultManager = $config['default_manager'];
        }

        // Default manager not found
        if (!isset($config['managers'][$defaultManager])) {
            throw new \RuntimeException(sprintf(
                'Undefined default notification manager "%s". Allowed managers: "%s".',
                $defaultManager,
                implode('", "', array_keys($config['managers']))
            ));
        }

        $apnPushDefinition = $container->getDefinition('apple.apn_push');
        $apnPushDefinition
            ->addMethodCall('setDefault', array($defaultManager));

        foreach ($config['managers'] as $managerName => $managerInfo) {
            // Set default parameters
            $managerInfo = $this->setDefaultsApnPushManager($managerName, $managerInfo, $config);

            // Create notification service
            $notification = $this->createNotification($container, $managerInfo);

            $notificationId = sprintf('apple.apn_push.%s_notification', $managerName);
            $container->setDefinition($notificationId, $notification);

            // Create feedback service
            $feedback = $this->createFeedback($container, $managerInfo);

            $feedbackId = sprintf('apple.apn_push.%s_feedback', $managerName);
            $container->setDefinition($feedbackId, $feedback);

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

                $notification
                    ->addMethodCall('setLogger', array(
                        new Reference(sprintf('apple.apn_push.%s_logger', $managerName))
                    ));

                $feedback
                    ->addMethodCall('setLogger', array(
                        new Reference(sprintf('apple.apn_push.%s_logger', $managerName))
                    ));
            }

            // Create manager definition
            $managerId = sprintf('apple.apn_push.%s_manager', $managerName);
            $manager = new Definition($container->getParameter('apple.apn_push.manager.class'));
            $manager->setArguments(array(new Reference($notificationId), new Reference($feedbackId)));

            $container->setDefinition($managerId, $manager);

            $apnPushDefinition
                ->addMethodCall('add', array($managerName, new Reference($managerId)));
        }
    }

    /**
     * Create notification system for manager
     *
     * @param ContainerBuilder $container
     * @param array $info
     * @throws \RuntimeException
     * @return \Symfony\Component\DependencyInjection\Definition
     */
    private function createNotification(ContainerBuilder $container, array $info)
    {
        // Check certificate file
        if (!file_exists($info['certificate']) || !is_file($info['certificate'])) {
            throw new \RuntimeException(sprintf(
                'Certificate file "%s" not found.',
                $info['certificate']
            ));
        }

        // Check certificate file readable
        if (!is_readable($info['certificate'])) {
            throw new \RuntimeException(sprintf(
                'Certificate file "%s" not readable!',
                $info['certificate']
            ));
        }

        // Create connection
        $connection = new Definition($container->getParameter('apple.apn_push.notification.connection.class'));
        $connection
            ->setArguments(array(
                $info['certificate'],
                $info['passphrase'],
                (bool) $info['sandbox']
            ))
            ->addMethodCall('setReadTime', $info['connection']['read_time']);

        // Create payload factory
        $payloadFactory = new Definition($container->getParameter('apple.apn_push.notification.payload_factory.class'));
        $payloadFactory->setArguments(array());

        // Usage JSON_UNESCAPED_UNICODE
        if (true === $info['payload_factory']['json_unescaped_unicode']) {
            $payloadFactory->addMethodCall('setJsonUnescapedUnicode', array(true));
        }

        // Create notification service
        $notification = new Definition($container->getParameter('apple.apn_push.notification.class'));
        $notification->setArguments(array($connection, $payloadFactory));

        return $notification;
    }

    /**
     * Create feedback service
     *
     * @param ContainerBuilder $container
     * @param array $info
     */
    private function createFeedback(ContainerBuilder $container, array $info)
    {
        // Check certificate file
        if (!file_exists($info['certificate']) || !is_file($info['certificate'])) {
            throw new \RuntimeException(sprintf(
                'Certificate file "%s" not found.',
                $info['certificate']
            ));
        }

        // Check certificate file readable
        if (!is_readable($info['certificate'])) {
            throw new \RuntimeException(sprintf(
                'Certificate file "%s" not readable!',
                $info['certificate']
            ));
        }

        // Create connection
        $connection = new Definition($container->getParameter('apple.apn_push.feedback.connection.class'));
        $connection
            ->setArguments(array(
                $info['certificate'],
                $info['passphrase'],
                (bool) $info['sandbox']
            ));

        // Create feedback service
        $feedback = new Definition($container->getParameter('apple.apn_push.feedback.class'));
        $feedback
            ->setArguments(array($connection));

        return $feedback;
    }

    /**
     * Set defaults to apn push manager
     *
     * @param string $managerName
     * @param array $managerInfo
     * @param array $config
     * @throws \RuntimeException
     * @return array
     */
    private function setDefaultsApnPushManager($managerName, array $managerInfo, array $config)
    {
        // Add defaults
        $managerInfo += array(
            'loggers' => array(),
            'connection' => array(
                'read_time' => null
            ),
            'payload_factory' => array(
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
                        'Please set certificate file for manager "%s" or set default certificate file.',
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