<?php

namespace Prolyfix\ProcurementBundle\EventListener;

use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Prolyfix\ProcurementBundle\Controller\ReceiptCrudController;
use Prolyfix\ProcurementBundle\Entity\Receipt;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class ReceiptListener
{
    private $isProcessing = false;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private MailerInterface $mailer,
        private ParameterBagInterface $params,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function postPersist(Receipt $receipt,  $event): void
    {
        $user = $this->security->getUser();
        $roles = $user->getRoles();
        if ($receipt->getOwner() == null)
            return;
        if ($receipt->getOwner()->getManager() !== null && !in_array('ROLE_ADMIN', $roles)) {
            $notification = new Notification();
            $notification->setUser($receipt->getOwner()->getManager());
            $notification->setStatut("unread");
            $notification->setFromUser($this->security->getUser());
            $notification->setTitle("eingereichte Quittung");
            $path = $this->urlGenerator->generate('admin', [
                'crudAction'            => 'detail',
                'crudControllerFqcn'    => ReceiptCrudController::class,
                'entityId'              => $receipt->getId(),
            ]);
            $notification->setPath($path);
            $this->entityManager->persist($notification);
            $this->entityManager->flush();

            $email = new TemplatedEmail();
            $email->from(new Address($this->params->get('email_sender'), $this->params->get('email_sender_name')))
                ->to($receipt->getOwner()->getManager()->getEmail())
                ->subject('Eingereichte Quittung')
                ->htmlTemplate('email/belege.html.twig')
                ->context([
                    'receipt' => $receipt
                ]);
            $this->mailer->send($email);
        }
    }
}
