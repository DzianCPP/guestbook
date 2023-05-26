<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Traits\Greet;

class ConferenceController extends AbstractController
{
    use Greet;

    private Request $request;

    public function __construct(
        protected RequestStack $requestStack
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    #[Route(
        path: '/',
        name: 'homepage',
        methods: 'GET'
    )]
    public function index(): Response
    {
        $greet = $this->setGreet($this->request);
        return $this->render('conference/homepage.html.twig', [
            'title' => 'Guestbook',
            'greet' => $greet
        ]);
    }
}