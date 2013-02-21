<?php

/**
 * This file is part of the AppleApnPushBundle package
 *
 * (c) Vitaliy Zhuk <zhuk2205@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Apple\ApnPushBundle\ApnPush;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Apple\ApnPush\Messages\DefaultMessage;
use Apple\ApnPushBundle\Exceptions\ManagerNotFoundException;

/**
 * Manager push notifications
 */
class ApnPushManager implements ApnPushManagerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Construct
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function getManager($key = null)
    {
        if (null === $key) {
            $key = $this->getDefaultManagerKey();
        }

        $notificationKey = 'apple.apn_push.' . $key . '_notification';

        if (!$this->container->has($notificationKey)) {
            throw new ManagerNotFoundException(sprintf(
                'Not found notification manager "%s" by key "%s".',
                $notificationKey, $key
            ));
        }

        return $this->container->get($notificationKey);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultManagerKey()
    {
        return $this->container->getParameter('apple.apn_push.default_manager');
    }

    /**
     * {@inheritDoc}
     */
    public function getManagerKeys()
    {
        return $this->container->getParameter('apple.apn_push.managers');
    }

    /**
     * {@inheritDoc}
     */
    public function createMessage($deviceToken = null, $body = null, $identifier = null)
    {
        $message = new DefaultMessage();

        if (null !== $deviceToken) {
            $message->setDeviceToken($deviceToken);
        }

        if (null !== $body) {
            $message->setBody($body);
        }

        if (null !== $identifier) {
            $message->setIdentifier($identifier);
        }

        return $message;
    }
}