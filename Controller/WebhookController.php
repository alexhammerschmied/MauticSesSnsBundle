<?php

namespace MauticPlugin\MauticSesSnsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends AbstractController
{
    public function callbackAction(Request $request)
    {
        // Assuming $request->getContent() returns the JSON payload from AWS SNS
        $data = json_decode($request->getContent(), true);

        // Log or process the data here
        // For example, just logging the received data:
        $this->get('logger')->info('Received webhook: ' . $request->getContent());

        // You should implement your logic here based on the $data received
        // For example, verifying the message type and handling subscription confirmations or notifications

        return new Response('Webhook received', Response::HTTP_OK);
    }
}
