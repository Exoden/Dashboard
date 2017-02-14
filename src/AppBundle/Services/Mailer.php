<?php
namespace AppBundle\Services;

use AppBundle\Entity\MailSent;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Templating\EngineInterface;

class Mailer
{
    protected $em;
    protected $mailer;
    protected $templating;
    private $from = "marc.vanren@gmail.com";
    private $reply = "marc.vanren@gmail.com";
    private $name = "Marc Vanrenterghem";

    public function __construct(EntityManager $em, $mailer, EngineInterface $templating)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    protected function sendMessage($mailSent)
    {
        $mail = \Swift_Message::newInstance();

        $mail
            ->setFrom($mailSent->getFromUser()->getEmail(), $mailSent->getFromUser()->getUsername())
            ->setTo($mailSent->getToUser()->getEmail())
            ->setSubject($mailSent->getSubject())
            ->setBody($mailSent->getBody())
            ->setReplyTo($mailSent->getFromUser()->getEmail(), $mailSent->getFromUser()->getUsername())
            ->setContentType('text/html');

        $this->mailer->send($mail);
    }

    public function sendSimpleMail(User $from, User $to, $subject, $title, $msg)
    {
        $template = 'AppBundle:Emails:simple.html.twig';
        $body = $this->templating->render($template, array('user' => $to, 'title' => $title, 'msg' => $msg));

        $mailSent = new MailSent();
        $mailSent->setFromUser($from);
        $mailSent->setToUser($to);
        $mailSent->setSubject($subject);
        $mailSent->setBody($body);
        $this->em->persist($mailSent);
        $this->em->flush();

        $this->sendMessage($mailSent);
    }
}