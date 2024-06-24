<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification\classes;

use Exception;

class ContractorRepository
{
    /**
     * @throws Exception
     */
    public function getContractorById(
        int $id,
        string $class = Contractor::class,
        string $entityName = 'Contractor'
    ): Contractor {
        $seller = $class::getById($id);
        if ($seller === null) {
            throw new Exception($entityName . ' not found!', 400);
        }

        return $seller;
    }
}