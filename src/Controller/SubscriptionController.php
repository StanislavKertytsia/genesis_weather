<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Enum\Duration;
use App\Enum\Frequency;
use App\Form\LoginForm;
use App\Form\SubscribeForm;
use App\Repository\SubscriptionRepository;
use App\Service\EmailService;
use App\Service\JwtService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SubscriptionController extends AbstractController
{
    private SubscriptionRepository $subscriptionRepository;
    private EntityManagerInterface $manager;
    private JwtService $jwtService;
    private EmailService $emailService;

    public function __construct(
        SubscriptionRepository $subscriptionRepository,
        EntityManagerInterface $manager,
        JwtService             $jwtService,
        EmailService           $emailService
    )
    {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->manager = $manager;
        $this->jwtService = $jwtService;
        $this->emailService = $emailService;
    }

    #[Route('/subscribe', name: 'app_subscribe')]
    public function subscribe(Request $request): Response
    {
        $form = $this->createForm(SubscribeForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $isSubscription = $this->subscriptionRepository->findOneBy(['email' => $data['email']]);

            if ($isSubscription) {
                $form->get('email')->addError(new FormError('This email is already subscribed.'));
                return $this->render('subscription/subscribe.html.twig', [
                    'form' => $form,
                ]);
            }

            $subscription = new Subscription();
            $subscription
                ->setEmail($data['email'])
                ->setCity($data['city'])
                ->setFrequency(Frequency::from($data['frequency']))
                ->setConfirmed(false);

            $this->manager->persist($subscription);
            $this->manager->flush();

            $subscriptionToken = $this->jwtService->generateToken(['email' => $subscription->getEmail()], 24 * 3600);
            $link = $this->generateUrl('app_confirm', ['token' => $subscriptionToken], UrlGeneratorInterface::ABSOLUTE_URL);

            $this->emailService->sendEmail(
                $subscription->getEmail(),
                'Confirm your weather subscription',
                'emails/confirmation.html.twig',
                ['link' => $link]);

            $this->addFlash('success', 'Check your email to confirm your subscription.');
            return $this->redirectToRoute('app_subscribe');
        }

        return $this->render('subscription/subscribe.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/confirm/{token}', name: 'app_confirm')]
    public function confirm(string $token, Request $request): Response
    {
        try {
            $payload = $this->jwtService->validateToken($token);
        } catch (\Exception $e) {
            return $this->render('subscription/confirm.html.twig', [
                'error' => 'The confirmation link is invalid. Please subscribe again.',
            ]);
        }

        if (!isset($payload['email'])) {
            return $this->render('subscription/confirm.html.twig', [
                'error' => 'The confirmation link is invalid. Please subscribe again.',
            ]);
        }

        $subscription = $this->subscriptionRepository->findOneBy(['email' => $payload['email']]);

        if (!$subscription) {
            return $this->render('subscription/confirm.html.twig', [
                'error' => 'The confirmation link is invalid. Please subscribe again.',
            ]);
        }

        if ($subscription->isConfirmed()) {
            return $this->render('subscription/confirm.html.twig', [
                'message' => 'Subscription already confirmed.',
            ]);
        }

        $subscription->setConfirmed(true);
        $this->manager->flush();
        $this->emailService->sendEmail($payload['email'], 'Your weather subscription is confirmed', 'emails/confirmed.html.twig', ['email' => $payload['email']]);;

        $authToken = $this->jwtService->generateToken(['email' => $payload['email']], 604800);
        $cookie = Cookie::create('auth_token')
            ->withValue($authToken)
            ->withExpires(time() + 604800)
            ->withHttpOnly(true)
            ->withSecure($request->isSecure());

        $response = new Response(
            $this->renderView('subscription/confirm.html.twig', [
                'message' => 'Subscription confirmed successfully!',
            ])
        );
        $response->headers->setCookie($cookie);
        return $response;
    }

    #[Route('/login', name: 'app_login')]
    public function login(Request $request): Response
    {
        $form = $this->createForm(LoginForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            $subscription = $this->subscriptionRepository->findOneBy(['email' => $email]);

            if ($subscription) {

                if (!$subscription->isConfirmed()) {
                    $this->addFlash('message', 'This email is not confirmed. Please check your inbox and click the confirmation link to activate your subscription.');
                    return $this->redirectToRoute('app_login');
                }

                $authToken = $this->jwtService->generateToken(['email' => $subscription->getEmail()], Duration::WEEK->value);
                $cookie = Cookie::create('auth_token')
                    ->withValue($authToken)
                    ->withExpires(time() + Duration::WEEK->value)
                    ->withHttpOnly(true)
                    ->withSecure($request->isSecure());

                $response = $this->redirectToRoute('app_main');
                $response->headers->setCookie($cookie);
                return $response;

            }

            $this->addFlash('message', 'Something went wrong. Please try again later or subscribe to get notified about the weather in your city.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('subscription/login.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(Request $request): Response
    {
        $response = $this->redirectToRoute('app_main');
        $response->headers->clearCookie('auth_token');
        return $response;
    }

    #[Route('/unsubscribe/{token}', name: 'unsubscribe')]
    public function unsubscribe(
        string                 $token,
        SubscriptionRepository $subscriptionRepository,
        EntityManagerInterface $manager,
        Request                $request
    )
    {
        $cookieToken = $request->cookies->get('auth_token');

        if (!$cookieToken || $cookieToken !== $token) {
            return $this->redirectToRoute('app_main');
        }

        $payload = $this->jwtService->validateToken($token);
        if (!$payload || !isset($payload['email'])) {
            return $this->redirectToRoute('app_main');
        }

        $subscription = $subscriptionRepository->findOneBy(['email' => $payload['email']]);

        if (!$subscription || !$subscription->isConfirmed()) {
            return $this->redirectToRoute('app_main');
        }

        $manager->remove($subscription);
        $manager->flush();
        $response = $this->redirectToRoute('app_login');
        $response->headers->clearCookie('auth_token');

        $this->addFlash('message', 'The subscription has been deleted successfully.');
        return $response;
    }
}
