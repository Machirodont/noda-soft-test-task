<?php

namespace NW\WebService\References\Operations\Notification\classes;

use ArrayFieldChecker;
use FieldCheckerInterface;
use IntFieldChecker;
use StringFieldChecker;

class BaseRequestDto
{
    private const CHECKERS = [
        'int' => IntFieldChecker::class,
        'string' => StringFieldChecker::class,
        'array' => ArrayFieldChecker::class,
    ];

    /**
     * @return array<string,string>
     */
    public static function getFields(): array
    {
        return [];
    }

    public static function validateInputData(array $data): void
    {
        $fields = self::getFields();
        foreach ($fields as $fieldName => $fieldType) {
            if(!isset(self::CHECKERS[$fieldType])) {
                continue;
            }
            /** @var FieldCheckerInterface $checker */
            $checker= self::CHECKERS[$fieldType];
            $checker::check($data, $fieldName);
        }
    }
}