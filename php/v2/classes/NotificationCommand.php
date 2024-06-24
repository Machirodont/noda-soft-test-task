<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification\classes;

class NotificationCommand
{

    // Должен быть Seller, но из-за ошибки Contractor::getById():self вместо static оставлю так
    public ?Contractor $reseller;

    public ?Contractor $client;

    public ?Contractor $creator;

    public ?Contractor $expert;

    public array $templateData;

    public string $resellerEmail;

    /**
     * @var string[]
     */
    public array $employeeEmails = [];

    public NotificationTypeEnum $notificationType;

    public DifferencesDto $differences;
}