<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\EventSubscriber\ContacterNousRequestEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class MailingSuscriberSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly MailerInterface $mailer)
    {
    }

    public function onContacterNousRequestEvent(ContacterNousRequestEvent $event): void
    {
        $data = $event->data;
        $email = (new TemplatedEmail())
         ->to('digitaldreams@brainsense.com')
         ->from($data->email)
         ->subject('Nouveau message de votre site')
         ->htmlTemplate('emails/emailSend.html.twig')
         ->context(['data' => $data]);
         $this->mailer->send($email);
    }

    public function onUserAuthEvent(UserAuthEvent $event){
        $data = $event->user;
        $email = (new TemplatedEmail())
        ->to('digitaldreams@brainsense.com')
        ->from($data['email'])
        ->subject('Nouveau message de votre site')
        ->htmlTemplate('emails/emailConnect.html.twig')
        ->context(['data' => $data]);
        $this->mailer->send($email);
    }

    public function onLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        if(!$user instanceof User){
            return;
        } 
        $email = (new Email())
         ->to($user->getEmail())
         ->from('digitaldreams@brainsense.com')
         ->subject('Connexion')
         ->text('Vous vous êtes connecté !');
         $this->mailer->send($email);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContacterNousRequestEvent::class => 'onContacterNousRequestEvent',
            // InteractiveLoginEvent::class => 'onLogin',
            UserAuthEvent::class => 'onUserAuthEvent',
        ];
    }
}
