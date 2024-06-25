<?php

declare(strict_types=1);

class StringFieldChecker implements FieldCheckerInterface
{
    public static function check(array $data, string $field): void
    {
        if (!isset($data[$field]) || empty((string)$data[$field])) {
            throw new Exception('Empty ' . $field, 400);
        }
    }
}