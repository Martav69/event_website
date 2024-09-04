<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    #[Route('/event/{id}/checkout', name: 'event_checkout')]
    public function checkout(int $id, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        return $this->render('payment/checkout.html.twig', [
            'stripe_public_key' => $_ENV['STRIPE_PUBLIC_KEY'],
            'event' => $event, // On envoie l'event a la template
        ]);
    }

    #[Route('/create-checkout-session/{id}', name: 'create_checkout_session', methods: ['POST'])]
    public function createCheckoutSession(int $id, EventRepository $eventRepository): JsonResponse
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $event = $eventRepository->find($id);

        if (!$event) {
            return new JsonResponse(['error' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }

        // Conversion du prix en centimes comme Stripe utilise les prix par centime
        $priceInCents = (int) ($event->getPrice() * 100);

        $YOUR_DOMAIN = 'http://localhost:8000'; // Domaine en test a changer si en prod

        $checkout_session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $event->getTitle(), // Recuperation du titre de l'event
                    ],
                    'unit_amount' => $priceInCents, // recuperation du prix de l'event
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/success',
            'cancel_url' => $YOUR_DOMAIN . '/cancel',
        ]);

        return new JsonResponse(['id' => $checkout_session->id]);
    }

    #[Route('/success', name: 'payment_success')]
    public function success(): Response
    {
        return $this->render('payment/success.html.twig');
    }

    #[Route('/cancel', name: 'payment_cancel')]
    public function cancel(): Response
    {
        return $this->render('payment/cancel.html.twig');
    }
}
