<?php

namespace AppBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class LoginListener
{
    /**
     * @var string
     */
    protected $locale;

    /**
     * Router
     *
     * @var Router
     */
    protected $router;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     * @param Router $router The router
     */
    public function __construct(ContainerInterface $container, Router $router)
    {
        $this->container = $container;
        $this->router = $router;
    }

    public function handle(AuthenticationEvent $event)
    {
        $token = $event->getAuthenticationToken();
        if ($token->getUser() != "anon.")
            $this->locale = $token->getUser()->getLocale();
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($this->locale !== null) {
            $request = $event->getRequest();
            $request->getSession()->set('_locale', $this->locale);
        }
    }
}