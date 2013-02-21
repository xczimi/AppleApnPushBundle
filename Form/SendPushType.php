<?php

/**
 * This file is part of the AppleApnPushBundle package
 *
 * (c) Vitaliy Zhuk <zhuk2205@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Apple\ApnPushBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Apple\ApnPushBundle\ApnPush\ApnPushManagerInterface;

/**
 * Send push form type
 */
class SendPushType extends AbstractType
{
    /**
     * @var ApnPushManagerInterface
     */
    protected $apnPush;

    /**
     * Construct
     *
     * @param ApnPushManagerInterface
     */
    public function __construct(ApnPushManagerInterface $apnPush)
    {
        $this->apnPush = $apnPush;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $apnPushNotifications = array_combine(
            $this->apnPush->getManagerKeys(),
            $this->apnPush->getManagerKeys()
        );

        $builder
            ->add('notification', 'choice', array(
                'required' => TRUE,
                'choices' => $apnPushNotifications,
                'label' => 'form.notification_manager',
                'translation_domain' => 'apple_apn_push'
            ))
            ->add('token', 'text', array(
                'required' => TRUE,
                'max_length' => 64,
                'label' => 'form.device_token',
                'translation_domain' => 'apple_apn_push'
            ))
            ->add('body', 'text', array(
                'required' => TRUE,
                'max_length' => 255,
                'label' => 'form.body_message',
                'translation_domain' => 'apple_apn_push'
            ))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'apple_apn_push';
    }
}