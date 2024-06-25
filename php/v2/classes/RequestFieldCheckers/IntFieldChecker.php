<?php

declare(strict_types=1);

class IntFieldChecker implements FieldCheckerInterface
{
    public static function check(array $data, string $field): void
    {
        if (!isset($data[$field]) || empty((int)$data[$field])) {
            throw new Exception('Empty ' . $field, 400);
        }
    }
}