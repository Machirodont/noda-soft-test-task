<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification\classes;

use Exception;

class RequestDto extends BaseRequestDto
{
    /**
     * @return array<string,string>
     */
    public static function getFields(): array
    {
        return [
            'resellerId' => 'int',
            'notificationType' => 'int',
            'clientId' => 'int',
            'creatorId' => 'int',
            'expertId' => 'int',
            'complaintId' => 'int',
            'consumptionId' => 'int',
            'complaintNumber' => 'string',
            'consumptionNumber' => 'string',
            'agreementNumber' => 'string',
            'date' => 'string',
            'differences' => 'array',
        ];
    }

    public function __construct(
        public int $resellerId,
        public NotificationTypeEnum $notificationType,
        public int $clientId,
        public int $creatorId,
        public int $expertId,
        public int $complaintId,
        public string $complaintNumber,
        public int $consumptionId,
        public string $consumptionNumber,
        public string $agreementNumber,
        public string $date,
        public DifferencesDto $differences,
    ) {
    }

    /**
     * @throws Exception
     */
    public static function createFromArray(array $data): self
    {
        self::validateInputData($data);

        return new self(
            (int)$data['resellerId'],
            NotificationTypeEnum::from($data['notificationType']),
            (int)$data['clientId'],
            (int)$data['creatorId'],
            (int)$data['expertId'],
            (int)$data['complaintId'],
            (string)$data['complaintNumber'],
            (int)$data['consumptionId'],
            (string)$data['consumptionNumber'],
            (string)$data['agreementNumber'],
            (string)$data['date'],
            DifferencesDto::createFromArray($data['differences']),
        );
    }
}