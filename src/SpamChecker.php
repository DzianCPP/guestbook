<?php

namespace App;

use App\Entity\Comment;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpamChecker
{
    private $endpoint;
    public function __construct(
        private HttpClientInterface $client,
    #[Autowire('%env(SPAM_API_KEY)%')] string $api_key)
    {
        $this->endpoint = "https://${api_key}.rest.akismet.com/1.1/comment-check";
    }

    public function getSpamScore(Comment $comment, array $context): int
    {
        $response = $this->client->request('POST', $this->endpoint, [
            'body' => array_merge($context, [
                'blog' => 'https://guestbook.example.com',
                'comment_type' => 'comment',
                'comment_author' => $comment->getAuthor(),
                'comment_author_email' => $comment->getEmail(),
                'comment_content' => $comment->getText(),
                'comment_date_gmt' => $comment->getCreatedAt()->format('c'),
                'blog_lang' => 'en',
                'blog_charset' => 'UTF-8',
                'is_test' => true
            ])
        ]);

        $headers = $response->getHeaders();
        if (($headers['x-akismet-pro-tip'][0] ?? '') === 'discard') {
            return 2;
        }

        $content = $response->getContent();
        if (isset($headers['x-akismet-debug-help'][0])) {
            throw new \RuntimeException("Unable to check for spam: {$content} ({$headers['x-akismet-debug-help'][0]})");
        }

        if ($content === 'true') {
            return 1;
        }

        return 0;
    }
}