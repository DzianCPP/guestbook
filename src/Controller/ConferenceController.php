<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

use App\Repository\ConferenceRepository;
use App\Repository\CommentRepository;

use App\Entity\Conference;

use App\Traits\DataBuilder;

class ConferenceController extends AbstractController
{
    use DataBuilder;

    private Request $request;

    public function __construct(
        protected RequestStack $requestStack,
        protected Environment $twig,
        protected ConferenceRepository $conferenceRepository,
        protected array $data = []
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    #[Route(
        path: '/',
        name: 'homepage',
        methods: 'GET|HEAD'
    )]
    public function index(): Response
    {
        $this->addItem('conferences', $this->conferenceRepository->findAll());
        $this->addItem('title', 'Conferences');

        $response = new Response(
            $this->twig->render(
                'conference/homepage.html.twig',
                $this->data
            )
        );

        return $response;
    }

    #[Route(
        path: '/conference/{id}',
        name: 'conference',
        methods: 'GET|HEAD',
        requirements: [
            'id' => '\d+'
        ]
    )]
    public function show(
        Conference $conference,
        CommentRepository $commentRepository,
        int $id = 1
    ): Response {
        $this->addItem('conference', $conference);
        $this->addItem('comments', $commentRepository->findBy(['conference' => $conference], ['createdAt' => 'DESC']));
        $this->addItem('title', 'Conference');
        
        return new Response($this->twig->render('conference/show.html.twig', $this->data));
    }
}