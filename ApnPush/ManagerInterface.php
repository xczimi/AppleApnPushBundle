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
use Apple\ApnPush\Notification\MessageInterface;
use Apple\ApnPush\Notification\NotificationInterface;

interface ManagerInterface
{
    /**
     * Set notification service
     *
     * @param NotificationInterface $notification
     */
    public function setNotification(NotificationInterface $notification);

    /**
     * Get notification service
     *
     * @return NotificationInterface
     */
    public function getNotification();

    /**
     * Set feedback service
     *
     * @param FeedbackInterface $feedback
     */
    public function setFeedback(FeedbackInterface $feedback);

    /**
     * Get feedback service
     *
     * @return FeedbackInterface
     */
    public function getFeedback();

    /**
     * Create new message
     *
     * @param string $deviceToken
     * @param string $body
     * @param integer $identifier
     * @return \Apple\ApnPush\Notification\MessageInterface
     */
    public function createMessage($deviceToken = null, $body = null, $identifier = null);

    /**
     * Send message
     *
     * @param MessageInterface $message
     */
    public function sendMessage(MessageInterface $message);

    /**
     * Get invalid devices
     *
     * @return array|\Apple\ApnPush\Feedback\Device[]
     */
    public function getInvalidDevices();
}