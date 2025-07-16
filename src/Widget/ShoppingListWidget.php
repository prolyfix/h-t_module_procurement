<?php 
namespace Prolyfix\ProcurementBundle\Widget;

use App\Widget\WidgetInterface;
use Doctrine\ORM\EntityManagerInterface;
use Prolyfix\ProcurementBundle\Entity\ShoppingList;
use Prolyfix\NoteBundle\Entity\Note;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment as Twig;

class ShoppingListWidget implements WidgetInterface
{
    private EntityManagerInterface $em;
    private Twig $twig;
    public static function getModule(): ?string
    {
        return 'ProcurementBundle';
    }
    
    public static function isGrantedForRole(): string
    {
        return 'ROLE_USER';
    }
    public function getContext(): array
    {
        return [
            'title' => 'ShoppingListWidget',
            'content' => 'ShoppingList content',
        ];
    }

    public function getTemplate(): string
    {
        return '@ShoppingListBundle/widget/shopping_list_widget.html.twig';
    }

    public function getHeight(): int
    {
        return 200;
    }

    public function getWidth(): int
    {
        return 4;
    }   

    public function getName(): string
    {
        return 'ShoppingListWidget';
    }

    public function isForThisUserAvailable(): bool
    {
        return true;
    }
    public function __construct(EntityManagerInterface $em,private Security $security, Twig $twig)
    {
        $this->em = $em;
        $this->twig = $twig;
    }

    public function render(): string
    {
        $items = $this->em->getRepository(ShoppingList::class)->findBy(['state'=>'open']);
        return 	$this->twig->render('@ProlyfixProcurement/widget/shopping_list_widget.html.twig',[
            'title' => 'ShoppingListWidget',
            'content' => 'ShoppingList content',
            'items' => $items,
        ]);
    }
}
