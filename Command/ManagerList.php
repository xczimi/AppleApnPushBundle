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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Manager list command
 */
class ManagerList extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('apple:apn-push:manager-list')
            ->setDescription('View all notification manager')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $apnPush = $this->getContainer()->get('apple.apn_push');

        if (!count($keys = array_keys($apnPush->all()))) {
            $output->writeln('<error>Managers not found. Size: 0.</error>');
        }

        $output->writeln(sprintf('<comment>Default manager: </comment><info>%s</info>', $apnPush->getDefault()));
        $output->writeln('');

        $output->writeln('<comment>Managers list:</comment>');
        $output->writeln('');

        foreach ($keys as $key) {
            $manager = $apnPush->getManager($key);
            $notification = $manager->getNotification();
            $payload = $notification->getPayloadFactory();
            $connection = $notification->getConnection();
            $connectionReadTime = $connection->getReadTime();

            $output->writeln(sprintf('<info>%s:</info>', $key));
            $output->writeln('<comment>Notification:</comment>');
            $output->writeln(sprintf('  %-30s<info>%s</info>', 'Class:', get_class($manager)));
            $output->writeln('<comment>Payload factory:</comment>');
            $output->writeln(sprintf('  %-30s<info>%s</info>', 'Class:', get_class($payload)));
            $output->writeln(sprintf('  %-30s<info>%s</info>', 'Json unescaped unicode:', $payload->getJsonUnescapedUnicode() ? 'true' : 'false'));
            $output->writeln('<comment>Notification connection:</comment>');
            $output->writeln(sprintf('  %-30s<info>%s</info>', 'Class:', get_class($connection)));
            $output->writeln(sprintf('  %-30s<info>%s</info>', 'Select read time:', $connectionReadTime[0] . '.' . $connectionReadTime[1] . ' sec.'));
            $output->writeln('');
            $output->writeln('');
        }

        $output->writeln('');
    }
}