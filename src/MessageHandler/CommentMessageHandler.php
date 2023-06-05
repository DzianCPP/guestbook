<?php

namespace App\MessageHandler;

use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\SpamChecker;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;

use Psr\Log\LoggerInterface;

#[AsMessageHandler]
class CommentMessageHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CommentRepository $commentRepository,
        private SpamChecker $spamChecker,
        private MessageBusInterface $bus,
        private WorkflowInterface $commentStateMachine,
        private ?LoggerInterface $logger = null,
        private MailerInterface $mailer,
    #[Autowire('%admin_email%')] private string $admin_email)
    {
    }

    public function __invoke(CommentMessage $message)
    {
        if (!$comment = $this->commentRepository->find($message->getCommentId())) {
            return;
        }

        if ($this->commentStateMachineCan($comment, ['accept'])) {
            $score = $this->spamChecker->getSpamScore($comment, $message->getContext());
            $transition = match ($score) {
                2 => 'reject_spam',
                1 => 'might_be_spam',
                default => 'accept'
            };

            $this->commentStateMachine->apply($comment, $transition);
            $this->entityManager->flush();
            $this->bus->dispatch($message);
        }

        if ($this->commentStateMachineCan($comment, ['publish', 'publish_ham'])) {
            $this->sendNotificationEmail($comment);
        } elseif ($this->logger) {
            $this->logger->debug(
                'Dropping comment message',
                [
                    'comment' => $comment->getId(),
                    'state' => $comment->getState()
                ]
            );
        }
    }

    private function sendNotificationEmail(object $comment): void
    {
        $notificationEmail = (new NotificationEmail())
            ->subject('New comment')
            ->htmlTemplate('emails/comment_notification.html.twig')
            ->from($this->admin_email)
            ->to($this->admin_email)
            ->context($comment->getContext());

        $this->mailer->send($notificationEmail);
    }

    private function commentStateMachineCan(object $comment, array $states): bool
    {
        foreach ($states as $state) {
            if ($this->commentStateMachine->can($comment, $state)) {
                return true;
            }
        }

        return false;
    }

    //TODO make it async! Visit config/bundles/messenger.yaml
}