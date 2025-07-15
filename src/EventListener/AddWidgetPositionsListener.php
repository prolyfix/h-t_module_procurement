<?php
namespace Prolyfix\ProcurementBundle\EventListener;

use App\Event\ModifiableArrayEvent;
use Doctrine\ORM\EntityManagerInterface;
use Prolyfix\ProcurementBundle\Widget\ShoppingListWidget;
use Prolyfix\RssBundle\Widget\RssWidget;
use Prolyfix\TimesheetBundle\Widget\DayViewWidget;
use Prolyfix\TimesheetBundle\Widget\WeekWorkingTimeWidget;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment as Twig;

class AddWidgetPositionsListener
{
    public function __construct(private EntityManagerInterface $em, private Security $security, private Twig $twig)
    {
    }

    public function onAppConfigureWidgetPositions(ModifiableArrayEvent $event)
    {
        $availableWidgets = $event->getData();
        $availableWidgets[] = new ShoppingListWidget($this->em, $this->security, $this->twig);
        $event->setData($availableWidgets);
    }
}