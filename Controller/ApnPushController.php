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
use Apple\ApnPush\Notification\SendExceptionInterface;

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
            'notification' => $apnPush->getDefaultManagerKey()
        ));

        // Errors and status
        $error = $sendStatus = null;

        if ('POST' === $request->getMethod()) {
            $form->bind($request);
            $formData = $form->getData();

            // Create message
            try {
                $message = $apnPush->createMessage(
                    $formData['token'],
                    $formData['body']
                );

                if ($formData['badge']) {
                    $message->getApsData()->setBadge($formData['badge']);
                }

                if ($formData['sound']) {
                    $message->getApsData()->setSound($formData['sound']);
                }

                if ($formData['custom_data']) {
                    if (!$otherData = @json_decode($formData['custom_data'], true)) {
                        throw new \InvalidArgumentException(sprintf(
                            'Can\'t decode custom data (JSON:json_decode). Last json error: "%s"',
                            json_last_error()
                        ));
                    }

                    $message->addCustomData($otherData);
                }
            } catch (\InvalidArgumentException $e) {
                $error = $e->getMessage();
                goto _returnForm;
            }

            $notification = $apnPush->getManager($formData['notification']);
            try {
                $sendStatus = $notification->sendMessage($message);
            } catch (SendExceptionInterface $e) {
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