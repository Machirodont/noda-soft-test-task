<?php

declare(strict_types=1);

class ArrayFieldChecker implements FieldCheckerInterface
{

    /**
     * @throws Exception
     */
    public static function check(array $data, string $field): void
    {
        if (!isset($data[$field]) || !is_array($data[$field])) {
            throw new Exception('Wrong or empty ' . $field, 400);
        }
    }
}