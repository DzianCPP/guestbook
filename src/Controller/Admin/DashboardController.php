<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Conference;
use App\Entity\Comment;

use App\Traits\SessionBuilder;
use App\Traits\DataBuilder;

use Twig\Environment;

class DashboardController extends AbstractDashboardController
{
    use SessionBuilder;
    use DataBuilder;

    private ?Request $request = null;

    public function __construct(
        private RequestStack $requestStack,
        private Environment $twig,
        private array $data = []
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->checkIfSessionIsValid();
    }

    #[Route(path: '/admin', name: 'admin')]
    public function admin(): Response
    {
        $urlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect($urlGenerator->setController(ConferenceCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Guestbook')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('Back to website', 'fas fa-home', 'homepage');
        yield MenuItem::linkToCrud('Conferences', 'fas fa-map-marker-alt', Conference::class);
        yield Menuitem::linkToCrud('Comments', 'fa fa-comments', Comment::class);
    }

    private function checkIfSessionIsValid(): false|Response
    {
        if (!$this->isSessionExpired()) {
            return false;
        }

        $this->sessionInvalidate();
        return new Response($this->twig->render('admin/session_expired.html.twig', [
            'session_expired_message' => 'Oops... your session has expired. Try to re-login or something...'
        ]));
    }
}