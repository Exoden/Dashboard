<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Controller;

use HWI\Bundle\OAuthBundle\Event\FilterUserResponseEvent;
use HWI\Bundle\OAuthBundle\Event\FormEvent;
use HWI\Bundle\OAuthBundle\Event\GetResponseUserEvent;
use HWI\Bundle\OAuthBundle\HWIOAuthEvents;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class ConnectController extends Controller
{
    /**
     * Shows a registration form if there is no user logged in and connecting
     * is enabled.
     *
     * @param Request $request a request
     * @param string  $key     key used for retrieving the right information for the registration form
     *
     * @return Response
     *
     * @throws NotFoundHttpException if `connect` functionality was not enabled
     * @throws AccessDeniedException if any user is authenticated
     * @throws \RuntimeException
     */
    public function registrationAction(Request $request, $key)
    {
        $connect = $this->container->getParameter('hwi_oauth.connect');
        if (!$connect) {
            throw new NotFoundHttpException();
        }

        $hasUser = $this->isGranted($this->container->getParameter('hwi_oauth.grant_rule'));
        if ($hasUser) {
            $this->addFlash('error', "Cannot connect already registered account.");
            return $this->redirectToRoute('homepage');
//            throw new AccessDeniedException('Cannot connect already registered account.');
        }

        $session = $request->getSession();
        $error = $session->get('_hwi_oauth.registration_error.'.$key);
        $session->remove('_hwi_oauth.registration_error.'.$key);

        if (!$error instanceof AccountNotLinkedException) {
            $this->addFlash('error', "Cannot register an account.");
            return $this->redirectToRoute('homepage');
//            throw new \RuntimeException('Cannot register an account.', 0, $error instanceof \Exception ? $error : null);
        }

        $userInformation = $this
            ->getResourceOwnerByName($error->getResourceOwnerName())
            ->getUserInformation($error->getRawToken())
        ;

        /* @var $form FormInterface */
        if ($this->container->getParameter('hwi_oauth.fosub_enabled')) {
            // enable compatibility with FOSUserBundle 1.3.x and 2.x
            if (interface_exists('FOS\UserBundle\Form\Factory\FactoryInterface')) {
                $form = $this->container->get('hwi_oauth.registration.form.factory')->createForm();
            } else {
                $form = $this->container->get('hwi_oauth.registration.form');
            }
        } else {
            $form = $this->container->get('hwi_oauth.registration.form');
        }

        $formHandler = $this->container->get('hwi_oauth.registration.form.handler');
        if ($formHandler->process($request, $form, $userInformation)) {
            $event = new FormEvent($form, $request);
            $this->get('event_dispatcher')->dispatch(HWIOAuthEvents::REGISTRATION_SUCCESS, $event);

            $this->container->get('hwi_oauth.account.connector')->connect($form->getData(), $userInformation);

            // Authenticate the user
            $this->authenticateUser($request, $form->getData(), $error->getResourceOwnerName(), $error->getAccessToken());

            if (null === $response = $event->getResponse()) {
                if ($targetPath = $this->getTargetPath($session)) {
                    $response = $this->redirect($targetPath);
                } else {
                    $response = $this->render('@HWIOAuth/Connect/registration_success.html.twig', array(
                        'userInformation' => $userInformation,
                    ));
                }
            }

            $event = new FilterUserResponseEvent($form->getData(), $request, $response);
            $this->get('event_dispatcher')->dispatch(HWIOAuthEvents::REGISTRATION_COMPLETED, $event);

            return $response;
        }

        // reset the error in the session
        $session->set('_hwi_oauth.registration_error.'.$key, $error);

        $event = new GetResponseUserEvent($form->getData(), $request);
        $this->get('event_dispatcher')->dispatch(HWIOAuthEvents::REGISTRATION_INITIALIZE, $event);

        if ($response = $event->getResponse()) {
            return $response;
        }

        return $this->render('@HWIOAuth/Connect/registration.html.twig', array(
            'key' => $key,
            'form' => $form->createView(),
            'userInformation' => $userInformation,
        ));
    }

    /**
     * Get a resource owner by name.
     *
     * @param string $name
     *
     * @return ResourceOwnerInterface
     *
     * @throws NotFoundHttpException if there is no resource owner with the given name
     */
    protected function getResourceOwnerByName($name)
    {
        foreach ($this->container->getParameter('hwi_oauth.firewall_names') as $firewall) {
            $id = 'hwi_oauth.resource_ownermap.'.$firewall;
            if (!$this->container->has($id)) {
                continue;
            }

            $ownerMap = $this->container->get($id);
            if ($resourceOwner = $ownerMap->getResourceOwnerByName($name)) {
                return $resourceOwner;
            }
        }

        throw new NotFoundHttpException(sprintf("No resource owner with name '%s'.", $name));
    }

    /**
     * Authenticate a user with Symfony Security.
     *
     * @param Request       $request
     * @param UserInterface $user
     * @param string        $resourceOwnerName
     * @param string        $accessToken
     * @param bool          $fakeLogin
     */
    protected function authenticateUser(Request $request, UserInterface $user, $resourceOwnerName, $accessToken, $fakeLogin = true)
    {
        try {
            $this->container->get('hwi_oauth.user_checker')->checkPreAuth($user);
            $this->container->get('hwi_oauth.user_checker')->checkPostAuth($user);
        } catch (AccountStatusException $e) {
            // Don't authenticate locked, disabled or expired users
            return;
        }

        $token = new OAuthToken($accessToken, $user->getRoles());
        $token->setResourceOwnerName($resourceOwnerName);
        $token->setUser($user);
        $token->setAuthenticated(true);

        $this->get('security.token_storage')->setToken($token);

        if ($fakeLogin) {
            // Since we're "faking" normal login, we need to throw our INTERACTIVE_LOGIN event manually
            $this->container->get('event_dispatcher')->dispatch(
                SecurityEvents::INTERACTIVE_LOGIN,
                new InteractiveLoginEvent($request, $token)
            );
        }
    }

    /**
     * @param SessionInterface $session
     *
     * @return string|null
     */
    private function getTargetPath(SessionInterface $session)
    {
        foreach ($this->container->getParameter('hwi_oauth.firewall_names') as $providerKey) {
            $sessionKey = '_security.'.$providerKey.'.target_path';
            if ($session->has($sessionKey)) {
                return $session->get($sessionKey);
            }
        }

        return null;
    }
}
