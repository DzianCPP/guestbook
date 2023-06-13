<?php

namespace App\Traits;

trait SessionBuilder
{
    private const MAX_IDLE_TIME = 60;

    public function sessionStart(): void
    {
        $this->request->getSession()->start();
    }

    public function addSessionItem(string $attribute = '', string $value = '')
    {
        if (!$attribute || !$value) {
            return;
        }

        $this->request->getSession()->set($attribute, $value);
    }

    public function getSessionCreatedAt(): int
    {
        return $this->request->getSession()->getMetadataBag()->getCreatedAt();
    }

    public function getSessionLastUsed(): int
    {
        return $this->request->getSession()->getMetadataBag()->getLastUsed();
    }

    public function isSessionExpired(): bool
    {
        if (time() - $this->getSessionLastUsed() > self::MAX_IDLE_TIME) {
            return true;
        }

        return false;
    }

    public function getLifetime(): int
    {
        return $this->request->getSession()->getMetadataBag()->getLifetime();
    }

    public function sessionInvalidate(): void
    {
        $this->request->getSession()->invalidate();
    }
}