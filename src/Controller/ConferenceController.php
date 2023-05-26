<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{
    #[Route(
        path: '/',
        name: 'homepage',
        methods: 'GET'
    )]
    public function index(): Response
    {
        return $this->render('conference/homepage.html.twig', [
            'title' => 'Guestbook',
        ]);
    }
}