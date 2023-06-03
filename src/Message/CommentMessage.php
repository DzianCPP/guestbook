<?php

namespace App\Message;

class CommentMessage
{
    public function __construct(
        private int $comment_id,
        private array $context = []
    ) {
    }

    public function getCommentId(): int
    {
        return $this->comment_id;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}