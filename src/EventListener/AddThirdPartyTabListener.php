<?php
namespace Prolyfix\ProcurementBundle\EventListener;

use Prolyfix\ProcurementBundle\Controller\OrderCrudController;

class AddThirdPartyTabListener
{

    public function __construct(
    )
    {}
    public function onAppShowThirdparty( $event)
    {
        $availableConfigurations = $event->getData();
        
        $availableConfigurations['Orders'] = ['controller'=>OrderCrudController::class, 'action'=>'tabThirdParty'];
        $event->setData($availableConfigurations);
    }
}