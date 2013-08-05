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

use Apple\ApnPush\Feedback\FeedbackInterface;
use Apple\ApnPush\Notification\Message;
use Apple\ApnPush\Notification\MessageInterface;
use Apple\ApnPush\Notification\NotificationInterface;

/**
 * Apn push manager
 */
class Manager implements ManagerInterface
{
    /**
     * @var NotificationInterface
     */
    protected $notification;

    /**
     * @var FeedbackInterface
     */
    protected $feedback;

    /**
     * Construct
     *
     * @param NotificationInterface $notification
     * @param FeedbackInterface $feedback
     */
    public function __construct(NotificationInterface $notification = null, FeedbackInterface $feedback = null)
    {
        $this->notification = $notification;
        $this->feedback = $feedback;
    }

    /**
     * Set feedback service
     *
     * @param FeedbackInterface $feedback
     * @return ManagerInterface
     */
    public function setFeedback(FeedbackInterface $feedback)
    {
        $this->feedback = $feedback;

        return $this;
    }

    /**
     * Get feedback service
     *
     * @return FeedbackInterface
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * Set notification service
     *
     * @param NotificationInterface $notification
     * @return ManagerInterface
     */
    public function setNotification(NotificationInterface $notification)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Get notification service
     *
     * @return NotificationInterface
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Create new message
     *
     * @param string $deviceToken
     * @param string $body
     * @param integer $identifier
     * @return MessageInterface
     */
    public function createMessage($deviceToken = null, $body = null, $identifier = null)
    {
        $message = new Message();

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

    /**
     * Send message
     *
     * @param string MessageInterface
     * @return bool
     */
    public function sendMessage(MessageInterface $message)
    {
        return $this->notification->send($message);
    }

    /**
     * Get invalid devices
     *
     * @return array|\Apple\ApnPush\Feedback\Device[]
     */
    public function getInvalidDevices()
    {
        return $this->feedback->getInvalidDevices();
    }
}