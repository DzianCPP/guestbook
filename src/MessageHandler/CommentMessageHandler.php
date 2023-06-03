<?php

namespace App\MessageHandler;

use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\SpamChecker;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CommentMessageHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CommentRepository $commentRepository,
        private SpamChecker $spamChecker
    ) {
    }

    public function __invoke(CommentMessage $message)
    {
        if (!$comment = $this->commentRepository->find($message->getCommentId())) {
            return;
        }

        $spamScore = $this->spamChecker->getSpamScore($comment, $message->getContext());

        if ($spamScore === 2) {
            $comment->setState('spam');
        }

        if ($spamScore !== 2) {
            $comment->setState('published');
        }

        $this->entityManager->flush();
    }
}