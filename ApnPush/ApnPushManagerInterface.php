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

use Apple\ApnPush\Messages\DefaultMessage;

/**
 * Interface for control apn push managers
 */
interface ApnPushManagerInterface
{
    /**
     * Get manager by key
     *
     * @param string |null $key
     */
    public function getManager($key);

    /**
     * Get default manager key
     *
     * @return string
     */
    public function getDefaultManagerKey();

    /**
     * Get manager keys
     *
     * @return array
     */
    public function getManagerKeys();

    /**
     * Create message
     *
     * @param string $deviceToken
     * @param string $body
     * @param integer $identifier
     */
    public function createMessage($deviceToken = null, $body = null, $identifier = null);
}