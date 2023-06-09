<?php

namespace App\Controller;

use App\Traits\DataBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\HttpKernel\HttpCache\StoreInterface;
use Symfony\Component\HttpKernel\KernelInterface;

use App\Entity\Comment;
use App\Message\CommentMessage;
use Doctrine\ORM\EntityManagerInterface;

use Twig\Environment;

#[Route('/admin')]
class AdminController extends AbstractController
{
    use DataBuilder;

    private ?Request $request = null;

    public function __construct(
        private Environment $twig,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus,
        private RequestStack $requestStack,
        private array $data = []
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    #[Route(
        path: '/comment/review/{id}',
        name: 'review_comment',
        methods: 'GET|HEAD'
    )]
    public function review_comment(
        Comment $comment,
        WorkflowInterface $commentStateMachine
    ): Response {
        $accepted = !$this->request->query->get('reject');
        if (!$transition = $this->getTransition($comment, $commentStateMachine, $accepted)) {
            return new Response('Comment already reviewed or in a different state');
        }

        $commentStateMachine->apply($comment, $transition);
        $this->entityManager->flush();

        if ($accepted) {
            $this->bus->dispatch(new CommentMessage($comment->getId()));
        }

        $this->addItems(['transition' => $transition, 'comment' => $comment]);

        return $this->render('admin/review.html.twig', $this->data);
    }

    #[Route('/http-cache/{uri<.*>}', methods: ['PURGE'])]
    public function purgeHttpCache(
        KernelInterface $kernel,
        StoreInterface $store,
        string $uri
    ): Response {
        if ($kernel->getEnvironment() === 'prod') {
            return new Response('KO', 400);
        }

        $store->purge($this->request->getSchemeAndHttpHost() . '/' . $uri);

        return new Response('Done');
    }

    private function getTransition(
        Comment $comment,
        WorkflowInterface $commentStateMachine,
        bool $accepted = true
    ): string|false {
        if ($commentStateMachine->can($comment, 'publish')) {
            return $accepted ? 'publish' : 'reject';
        }

        if ($commentStateMachine->can($comment, 'publish_ham')) {
            return $accepted ? 'publish_ham' : 'reject_ham';
        }

        return false;
    }
}