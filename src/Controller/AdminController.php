<?php

namespace App\Controller;

use App\Traits\DataBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\WorkflowInterface;

use App\Entity\Comment;
use App\Message\CommentMessage;
use Doctrine\ORM\EntityManagerInterface;

use Twig\Environment;


class AdminController extends AbstractController
{
    use DataBuilder;

    public function __construct(
        private Environment $twig,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus,
        private array $data = []
    ) {

    }

    #[Route(
        path: '/admin/comment/review/{id}',
        name: 'review_comment',
        methods: 'GET|HEAD'
    )]
    public function review_comment(
        Request $request,
        Comment $comment,
        WorkflowInterface $commentStateMachine
    ): Response {
        $accepted = !$request->query->get('reject');
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