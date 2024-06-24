<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification\classes;

class DifferencesDto extends BaseRequestDto
{
    /**
     * @return array<string,string>
     */
    public static function getFields(): array
    {
        return [
            'from' => 'int',
            'to' => 'int',
        ];
    }

    public function __construct(
        public int $from,
        public int $to,
    ) {
    }

    public static function createFromArray(array $data): self
    {
        self::validateInputData($data);

        return new self(
            (int)$data['from'],
            (int)$data['to'],
        );
    }
}