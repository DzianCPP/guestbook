<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;

use App\Repository\ConferenceRepository;
use App\Repository\CommentRepository;

use App\Entity\Conference;
use App\Entity\Comment;

use App\Message\CommentMessage;

use App\Form\CommentType;

use App\Traits\DataBuilder;

class ConferenceController extends AbstractController
{
    use DataBuilder;

    private Request $request;

    public function __construct(
        protected RequestStack $requestStack,
        protected ConferenceRepository $conferenceRepository,
        protected EntityManagerInterface $entityManager,
        private MessageBusInterface $bus,
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
        $this->addItems([
            'title' => 'Conferences',
            'conferences' => $this->conferenceRepository->findAll()
        ]);

        return $this->render('conference/homepage.html.twig', $this->data)->setSharedMaxAge(3600);
    }

    #[Route(
        path: '/conference/{slug}',
        name: 'conference',
        methods: 'GET|HEAD|POST'
    )]
    public function show(
        Conference $conference,
        CommentRepository $commentRepository,
    #[Autowire('%photo_dir%')] string $photo_dir, string $slug = ''): Response
    {
        $offset = max(0, $this->request->query->getInt('offset', 0));
        $paginator = $commentRepository->getCommentPaginator($conference, $offset);

        $this->addItems([
            'conference' => $conference,
            'comments' => $paginator,
            'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
            'title' => "Conference - {$conference}",
            'conferences' => $this->conferenceRepository->findAll()
        ]);

        $comment = new Comment();
        $comment_form = $this->createForm(CommentType::class, $comment);
        $comment_form->handleRequest($this->request);

        if ($comment_form->isSubmitted() && $comment_form->isValid()) {
            $comment->setConference($conference);
            if ($photo = $comment_form['photo']->getData()) {
                $filename = bin2hex(random_bytes(6)) . '.' . $photo->guessExtension();
                $photo->move($photo_dir, $filename);
                $comment->setPhotoFilename($filename);
            }

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            $context = $this->getContext();

            $this->bus->dispatch(new CommentMessage($comment->getId(), $context));
            return $this->redirectToRoute('conference', ['slug' => $conference->getSlug()]);
        }

        $this->addItem('comment_form', $comment_form);

        return $this->render('conference/show.html.twig', $this->data);
    }

    private function getContext(): array
    {
        return [
            'user_ip' => $this->request->getClientIp(),
            'user_agent' => $this->request->headers->get('user-agent'),
            'referrer' => $this->request->headers->get('referrer'),
            'permalink' => $this->request->getUri()
        ];
    }

    #[Route(
        path: '/conference_header',
        name: 'conference_header',
        methods: 'GET|HEAD'
    )]
    public function conferenceHeader(): Response
    {
        $this->addItem('conferences', $this->conferenceRepository->findAll());
        
        return $this->render('conference/header.html.twig', $this->data)->setSharedMaxAge(3600);
    }
}