<?php

namespace NodaSoft\Result\Notification;

use NodaSoft\DataMapper\EntityInterface\MessageRecipientEntity;

class NotificationResult
{
    /** @var bool */
    private $isSent;

    /** @var string */
    private $errorMessage;

    /** @var MessageRecipientEntity */
    private $recipient;

    public function isSent(): bool
    {
        return $this->isSent ?? false;
    }

    public function setIsSent(bool $isSent): void
    {
        $this->isSent = $isSent;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage ?? "";
    }

    public function setErrorMessage(string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    public function toArray(): array
    {
        $array = [];
        foreach ($this as $key => $value) {
            $array[$key] = $value;
        }
        return $array;
    }

    public function getRecipient(): MessageRecipientEntity
    {
        return $this->recipient;
    }

    public function setRecipient(MessageRecipientEntity $recipient): void
    {
        $this->recipient = $recipient;
    }
}
