<?php

/**
 * This file is part of the AppleApnPushBundle package
 *
 * (c) Vitaliy Zhuk <zhuk2205@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Apple\ApnPushBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Application;

/**
 * AppleApnPush
 */
class AppleApnPushBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $builder)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function registerCommands(Application $application)
    {
        $application->addCommands(array(
            new Command\ManagerList(),
            new Command\SendPush()
        ));
    }
}