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

use Apple\ApnPushBundle\Exception\ManagerNotFoundException;

/**
 * Push notification storage (ApnPush core)
 */
class ApnPush
{
    /**
     * @var array
     */
    protected $managers = array();

    /**
     * @var string
     */
    protected $default;

    /**
     * Set default manager name
     *
     * @param string $name
     * @return ApnPush
     */
    public function setDefault($name)
    {
        $this->default = $name;

        return $this;
    }

    /**
     * Get default manager name
     *
     * @return string
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Add manager to storage
     *
     * @param string $name
     * @param ManagerInterface $manager
     * @return ApnPush
     */
    public function add($name, ManagerInterface $manager)
    {
        $this->managers[$name] = $manager;

        return $this;
    }

    /**
     * Get manager from storage
     *
     * @param string $name
     * @return ManagerInterface
     */
    public function get($name)
    {
        if (!isset($this->managers[$name])) {
            throw new ManagerNotFoundException(sprintf(
                'Manager "%s" not found.',
                $name
            ));
        }

        return $this->managers[$name];
    }

    /**
     * _alias: get
     * @return ManagerInterface
     */
    public function getManager($name = null)
    {
        if (null === $name) {
            $name = $this->default;
        }

        return $this->get($name);
    }

    /**
     * Get all manager
     *
     * @return array|ManagerInterface[]
     */
    public function all()
    {
        return $this->managers;
    }
}