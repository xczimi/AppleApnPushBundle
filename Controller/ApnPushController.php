<?php

/**
 * This file is part of the AppleApnPushBundle package
 *
 * (c) Vitaliy Zhuk <zhuk2205@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Apple\ApnPushBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Apple\ApnPushBundle\Form\SendPushType;
use Apple\ApnPush\Notification\SendException;

/**
 * Apn push actions
 */
class ApnPushController extends Controller
{
    /**
     * Apn push test
     *
     * @param Request $request
     */
    public function testAction(Request $request)
    {
        $apnPush = $this->get('apple.apn_push');

        $form = $this->createForm(new SendPushType($apnPush), array(
            'default' => $apnPush->getDefault()
        ));

        // Errors and status
        $error = $sendStatus = null;

        if ('POST' === $request->getMethod()) {
            $form->bind($request);
            $formData = $form->getData();

            // Get manager
            $manager = $apnPush->getManager($formData['manager']);

            // Create message
            try {
                /** @var \Apple\ApnPush\Notification\MessageInterface $message */
                $message = $manager->createMessage(
                    $formData['token'],
                    $formData['body']
                );

                if ($formData['badge']) {
                    $message->setBadge($formData['badge']);
                }

                if ($formData['sound']) {
                    $message->setSound($formData['sound']);
                }

                if ($formData['custom_data']) {
                    if (!$otherData = @json_decode($formData['custom_data'], true)) {
                        throw new \InvalidArgumentException(sprintf(
                            'Can\'t decode custom data (JSON:json_decode). Last json error: "%s"',
                            json_last_error()
                        ));
                    }

                    $message->setCustomData($otherData);
                }
            } catch (\InvalidArgumentException $e) {
                $error = $e->getMessage();
                goto _returnForm;
            }

            try {
                $sendStatus = $manager->sendMessage($message);
            } catch (SendException $e) {
                $error = (string) $e;
            }
        }

        _returnForm:

        return $this->render('AppleApnPushBundle::send_push.html.twig', array(
            'form' => $form->createView(),
            'success' => $sendStatus,
            'error' => $error
        ));
    }
}