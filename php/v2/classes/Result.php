<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification\classes;

class Result
{
    public function __construct(
        public bool $notificationEmployeeByEmail = false,
        public bool $notificationClientByEmail = false,
        public bool $notificationClientBySms = false,
        public string $errorMessage = '',
    ) {
    }

    public function toArray(): array
    {
        return [
            'notificationEmployeeByEmail' => $this->notificationEmployeeByEmail,
            'notificationClientByEmail' => $this->notificationClientByEmail,
            'notificationClientBySms' => [
                'isSent' => $this->notificationClientBySms,
                'message' => $this->errorMessage
            ],
        ];
    }
}