<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

final class WhoAmIController extends AbstractController
{
    public function __invoke(): ?UserInterface
    {
        return $this->getUser();
    }
}
