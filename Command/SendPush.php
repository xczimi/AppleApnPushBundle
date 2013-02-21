<?php

/**
 * This file is part of the AppleApnPushBundle package
 *
 * (c) Vitaliy Zhuk <zhuk2205@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Apple\ApnPushBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Send push notification
 */
class SendPush extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('apple:apn-push:send')
            ->setDescription('Send push to iOS device')
            ->addArgument('device-token', InputArgument::REQUIRED, 'Device token')
            ->addArgument('message', InputArgument::REQUIRED, 'Message')
            ->addOption('manager', NULL, InputOption::VALUE_OPTIONAL, 'Notification manager')
            ->addOption('sound', NULL, InputOption::VALUE_OPTIONAL, 'Use sound key in APS data')
            ->addOption('badge', NULL, InputOption::VALUE_OPTIONAL, 'Use badge key in APS data')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $nm = $this->getContainer()->get('apple.apn_push');

        $notification = $nm->getManager($input->getOption('manager'));

        $message = $nm->createMessage($input->getArgument('device-token'), $input->getArgument('message'));

        $notification->sendMessage($message);

        $output->writeln('<info>Success send message.</info>');
    }
}