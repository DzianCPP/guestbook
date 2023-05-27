<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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

        return $this->render('conference/homepage.html.twig', $this->data);
    }

    #[Route(
        path: '/conference/{slug}',
        name: 'conference',
        methods: 'GET|HEAD'
    )]
    public function show(
        Conference $conference,
        CommentRepository $commentRepository,
        string $slug = ''
    ): Response {
        $offset = max(0, $this->request->query->getInt('offset', 0));
        $paginator = $commentRepository->getCommentPaginator($conference, $offset);
        $this->addItem('conference', $conference);
        $this->addItem('comments', $paginator);
        $this->addItem('previous', $offset - CommentRepository::PAGINATOR_PER_PAGE);
        $this->addItem('next', min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE));
        $this->addItem('title', 'Conference');

        return $this->render('conference/show.html.twig', $this->data);
    }
}