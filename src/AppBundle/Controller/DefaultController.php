<?php

namespace AppBundle\Controller;

use AppBundle\Entity\MailSent;
use AppBundle\Form\MailSentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('AppBundle::homepage.html.twig');
    }

    /**
     * @Route("/change-locale/{language}", name="change_locale")
     */
    public function changeLocaleAction($language = null)
    {
        if ($language != null)
        {
            // On enregistre la langue en session
            $this->get('session')->set('_locale', $language);
        }

        // on tente de rediriger vers la page d'origine
        $url = $this->container->get('request')->headers->get('referer');
        if (empty($url))
        {
            $url = $this->container->get('router')->generate('homepage');
        }

        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if ($user->getLocale() != $language) {
            $user->setLocale($language);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }

        return new RedirectResponse($url);
    }

    /**
     * @Route("/detail-profile/{user_id}", name="detail_profile")
     */
    public function detailProfileAction(Request $request, $user_id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $profile = $em->getRepository('AppBundle:User')->find($user_id);
        if (!$profile) {
            throw $this->createNotFoundException();
        }

        return $this->render('FOSUserBundle:Profile:detail.html.twig', array('profile' => $profile, 'user' => $user));
    }

    /**
     * @Route("/my-friends", name="my_friends")
     */
    public function myFriendsAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->render('AppBundle::my_friends.html.twig', array('profile' => $user));
    }

    /**
     * @Route("/my-achievements", name="my_achievements")
     */
    public function myAchievementsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        // TODO : Get achievements from user

        return $this->render('AppBundle::my_achievements.html.twig', array(/* TODO : Add achievements list */));
    }

    /**
     * @Route("/news", name="news_page")
     */
    public function newsPageAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $news = $em->getRepository('AppBundle:News')->getOrderedNews();

        return $this->render('AppBundle::news_page.html.twig', array('all_news' => $news));
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contactAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('AppBundle:User')->findOneBy(array('email' => "marc.vanren@gmail.com"));

        return $this->render('AppBundle::contact.html.twig', array('user' => $user));
    }

    /**
     * @Route("/send-mail/{user_id}", name="send_mail")
     */
    public function sendMailToUserAction(Request $request, $user_id)
    {
        $em = $this->getDoctrine()->getManager();

        $fromUser = $this->get('security.token_storage')->getToken()->getUser();
        $toUser = $em->getRepository('AppBundle:User')->find($user_id);

        $mailSent = new MailSent();
        $form = $this->createForm(MailSentType::class, $mailSent);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.mailer')->sendSimpleMail($fromUser, $toUser, "suject", "title", "message");
            return $this->redirectToRoute('detail_profile', array('user_id' => $toUser->getId()));
        }

        return $this->render('AppBundle::form_mail.html.twig', array('fromUser' => $fromUser, 'toUser' => $toUser, 'form' => $form->createView()));
    }
}
