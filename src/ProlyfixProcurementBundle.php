<?php

namespace Prolyfix\ProcurementBundle;

use App\Entity\Module\ModuleConfiguration;
use App\Entity\Module\ModuleRight;
use App\Module\ModuleBundle;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Prolyfix\ProcurementBundle\Entity\Calendar;
use Prolyfix\ProcurementBundle\Entity\Inventar;
use Prolyfix\ProcurementBundle\Entity\Invoice;
use Prolyfix\ProcurementBundle\Entity\Order;
use Prolyfix\ProcurementBundle\Entity\Product;
use Prolyfix\ProcurementBundle\Entity\Receipt;
use Prolyfix\ProcurementBundle\Entity\ShoppingList;
use Prolyfix\ProcurementBundle\Entity\TypeOfAbsence;
use Prolyfix\ProcurementBundle\Entity\UserProperty;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProlyfixProcurementBundle extends ModuleBundle
{
    const IS_MODULE = true;

    private $authorizationChecker;

    public static function getTables(): array
    {
        return [
            Order::class,
            Product::class,
            Receipt::class,
            ShoppingList::class,
            Inventar::class,
        ];
    }

    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }
        public static function getShortName(): string
    {
        return 'ProcurementBundle';
    }
    public static function getModuleName(): string
    {
        return 'Procurement';
    }
    public static function getModuleDescription(): string
    {
        return 'Procurement Module';
    }
    public static function getModuleType(): string
    {
        return 'module';
    }
    public static function getModuleConfiguration(): array
    {
        return [];
    }

    public static function getModuleRights(): array
    {
        return [
            (new ModuleRight())
                ->setModuleAction(['list', 'show', 'edit', 'new', 'delete'])
                ->setCoverage('company')
                ->setRole('ROLE_ADMIN')
                ->setEntityClass(Inventar::class),
            (new ModuleRight())
                ->setModuleAction(['list', 'show', 'edit', 'new', 'delete'])
                ->setCoverage('company')
                ->setRole('ROLE_ADMIN')
                ->setEntityClass(Order::class),
            (new ModuleRight())
                ->setModuleAction(['list', 'show', 'edit', 'new', 'delete'])
                ->setCoverage('company')
                ->setRole('ROLE_ADMIN')
                ->setEntityClass(Product::class),     
            (new ModuleRight())
                ->setModuleAction(['list', 'show', 'edit', 'new', 'delete'])
                ->setCoverage('company')
                ->setRole('ROLE_ADMIN')
                ->setEntityClass(Receipt::class),          
                (new ModuleRight())
                ->setModuleAction(['list', 'show', 'edit', 'new', 'delete'])
                ->setCoverage('company')
                ->setRole('ROLE_MANAGER')
                ->setEntityClass(Inventar::class),
            (new ModuleRight())
                ->setModuleAction(['list', 'show', 'edit', 'new', 'delete'])
                ->setCoverage('company')
                ->setRole('ROLE_MANAGER')
                ->setEntityClass(Order::class),
            (new ModuleRight())
                ->setModuleAction(['list', 'show', 'edit', 'new', 'delete'])
                ->setCoverage('company')
                ->setRole('ROLE_MANAGER')
                ->setEntityClass(Product::class),     
            (new ModuleRight())
                ->setModuleAction(['list', 'show', 'edit', 'new', 'delete'])
                ->setCoverage('company')
                ->setRole('ROLE_MANAGER')
                ->setEntityClass(Receipt::class),  
                (new ModuleRight())
                ->setModuleAction(['list', 'show', 'edit', 'new', 'delete'])
                ->setCoverage('company')
                ->setRole('ROLE_ROLE_USERMANAGER')
                ->setEntityClass(Inventar::class),
            (new ModuleRight())
                ->setModuleAction(['list', 'show', 'edit', 'new', 'delete'])
                ->setCoverage( 'company')
                ->setRole('ROLE_USER')
                ->setEntityClass(Order::class),
            (new ModuleRight())
                ->setModuleAction(['list', 'show', 'edit', 'new', 'delete'])
                ->setCoverage( 'company')
                ->setRole('ROLE_USER')
                ->setEntityClass(Product::class),     
            (new ModuleRight())
                ->setModuleAction(['list', 'show', 'edit', 'new', 'delete'])
                ->setCoverage('user')
                ->setRole('ROLE_USER')
                ->setEntityClass(Receipt::class),  
        ];
    }

    public  function getMenuConfiguration(): array
    {
        return ['Procurement' => [
            MenuItem::section('Einkauf', 'fas fa-dollar-sign'),
            MenuItem::linkToCrud('Bestellungen', 'fas fa-cart', Order::class),
            MenuItem::linkToCrud('Invoice', 'fas fa-cart', Invoice::class),
            MenuItem::linkToCrud('Products', 'fas fa-cart', Product::class),
            MenuItem::linkToCrud('Belege', 'fas fa-cart', Receipt::class),
            MenuItem::linkToCrud('Inventar', 'fas fa-cart', Inventar::class)->setAction('show'),
        ]];
    }

    public static function getUserConfiguration(): array
    {
        return [];

    }

    public static function getModuleAccess(): array
    {
        return [];
    }




}