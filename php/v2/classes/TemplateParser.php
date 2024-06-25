<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification\classes;

class TemplateParser
{
    //  Обертка функции __
    public function parse(
        string $templateName,
        ?array $templateData,
        int $resellerId,
    ): string {
        return __($templateName, $templateData, $resellerId);
    }
}