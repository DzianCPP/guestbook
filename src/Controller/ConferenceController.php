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
        path: '/hello/{name}',
        name: 'homepage',
        methods: 'GET',
        requirements: [
            'name' => '[a-zA-Z]+'
        ]
    )]
    public function index(string $name = ""): Response
    {
        $greet = $this->setGreet($this->request, $name);
        return $this->render('conference/homepage.html.twig', [
            'title' => 'Guestbook',
            'greet' => $greet
        ]);
    }
}