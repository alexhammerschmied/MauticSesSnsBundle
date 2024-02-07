<?php

namespace MauticPlugin\MauticSesSnsBundle\EventSubscriber;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\TransportWebhookEvent;
use Mautic\EmailBundle\Model\TransportCallback;
use Mautic\LeadBundle\Entity\DoNotContact;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

class CallbackSubscriber implements EventSubscriberInterface
{
    private $transportCallback;
    private $coreParametersHelper;

    public function __construct(TransportCallback $transportCallback, CoreParametersHelper $coreParametersHelper)
    {
        $this->transportCallback = $transportCallback;
        $this->coreParametersHelper = $coreParametersHelper;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EmailEvents::ON_TRANSPORT_WEBHOOK => 'onTransportWebhookCallbackRequest',
        ];
    }

public function onTransportWebhookCallbackRequest(TransportWebhookEvent $webhookEvent): void
{
    $dsn = $this->coreParametersHelper->get('mailer_transport');

    // Check if the DSN indicates Amazon SES API usage
    if (strpos($dsn, 'ses+api') === false) {
        return;
    }

    $content = json_decode($webhookEvent->getRequest()->getContent(), true);
    
    // Check if this is a SNS subscription confirmation
    if (isset($content['Type']) && $content['Type'] === 'SubscriptionConfirmation') {
        // Handle subscription confirmation here
        // Visit the SubscribeURL to confirm
        $webhookEvent->setResponse(new Response('Subscription confirmed', Response::HTTP_OK));
        return;
    }

    // Assuming notification handling
    if (isset($content['Type']) && $content['Type'] === 'Notification') {
        $message = json_decode($content['Message'], true);

        // Example: Process bounce notifications
        if ($message['notificationType'] === 'Bounce') {
            $bounce = $message['bounce'];
            $type = ($bounce['bounceType'] === 'Permanent') ? DoNotContact::BOUNCED : DoNotContact::UNSUBSCRIBED;
            $reason = $bounce['bounceType'] . ' bounce: ' . $bounce['bounceSubType'];

            foreach ($bounce['bouncedRecipients'] as $recipient) {
                $email = $recipient['emailAddress'];
                $this->transportCallback->addFailureByAddress($email, $reason, $type);
            }
        }

        // Add more conditions to handle complaints, deliveries, etc.

        $webhookEvent->setResponse(new Response('Notification processed', Response::HTTP_OK));
    } else {
        $webhookEvent->setResponse(new Response('Invalid SNS message', Response::HTTP_BAD_REQUEST));
    }
}
}
