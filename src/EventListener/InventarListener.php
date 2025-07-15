<?php

namespace Prolyfix\ProcurementBundle\EventListener;

use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Prolyfix\ProcurementBundle\Controller\InventarCrudController;
use Prolyfix\ProcurementBundle\Controller\ReceiptCrudController;
use Prolyfix\ProcurementBundle\Entity\Inventar;
use Prolyfix\ProcurementBundle\Entity\Receipt;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class InventarListener
{
    private $isProcessing = false;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private MailerInterface $mailer,
        private ParameterBagInterface $params,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function postPersist(Inventar $inventar,  $event): void
    {
        $user = $this->security->getUser();
        $roles = $user->getRoles();
        $product = $inventar->getProduct();
        $stock = $this->entityManager->getRepository(Inventar::class)->StockByProduct( $product)[0]['quantity'];
        $admins = $this->entityManager->getRepository(User::class)->findActiveAdmins();
        if ($product->getTenant() == null)
            return;
        if ($stock < $product->getMinimalQuantity() && $product->getMinimalQuantity() > 0) {
            foreach($admins as $admin) {
                $notification = new Notification();
                $notification->setUser($admin);
                $notification->setStatut("unread");
                $notification->setFromUser($this->security->getUser());
                $notification->setTitle("Produkt nachbestellen");
                $path = $this->urlGenerator->generate('admin', [
                    'crudAction'            => 'show',
                    'crudControllerFqcn'    => InventarCrudController::class,
                ]);
                $notification->setPath($path);
                $this->entityManager->persist($notification);
                $this->entityManager->flush();

                $email = new TemplatedEmail();
                $email->from(new Address($this->params->get('email_sender'), $this->params->get('email_sender_name')))
                    ->to($admin->getEmail())
                    ->subject('Produkt nachbestellen')
                    ->htmlTemplate('email/inventar_bestellung.html.twig')
                    ->context([
                        'inventar' => $inventar
                    ]);
                $this->mailer->send($email);
            }
        }
    }
}
