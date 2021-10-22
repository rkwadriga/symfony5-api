<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Security;

class SetIsMeOnCurrentUserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Security $security
    ) {}

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        /** @var User|null $user */
        $user = $this->security->getUser();
        if ($user === null) {
            return;
        }

        $user->setIsMe(true);
    }

    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class => 'onKernelRequest',
        ];
    }
}
