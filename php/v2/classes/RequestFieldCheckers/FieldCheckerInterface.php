<?php

declare(strict_types=1);

interface FieldCheckerInterface
{
    /**
     * @throws Exception
     */
    public static function check(array $data, string $field): void;
}