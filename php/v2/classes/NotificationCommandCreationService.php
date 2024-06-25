<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification\classes;

use Exception;
use NW\WebService\References\Operations\Notification\Employee;
use NW\WebService\References\Operations\Notification\Seller;

use function NW\WebService\References\Operations\Notification\getEmailsByPermit;
use function NW\WebService\References\Operations\Notification\getResellerEmailFrom;

readonly class NotificationCommandCreationService
{
    public function __construct(
        private TemplateParser $templateParser,
        private ContractorRepository $contractorRepository,
    ) {
    }

    /**
     * @throws Exception
     */
    public function create(RequestDto $request): NotificationCommand
    {
        $notificationCommand = new NotificationCommand();
        // Кейс с Empty resellerId потребует анализа внешнего кода- возможно, понадобится его фиксить
        // т.к. мы меняем контракт в этом случае (вместо результата отдаем исключение).

        $notificationCommand->reseller = $this->contractorRepository->getContractorById($request->resellerId, Seller::class, 'Seller');

        $notificationCommand->client = $this->contractorRepository->getContractorById($request->clientId);
        $this->validateClient($notificationCommand->client, $request->resellerId);
        $creator = $this->contractorRepository->getContractorById($request->creatorId, Employee::class, 'Creator');
        $expert = $this->contractorRepository->getContractorById($request->creatorId, Employee::class, 'Expert');

        //Выносим формирование строки $differences в отдельный метод
        $differences = $this->getDifferencesDescription(
            $request->notificationType,
            $request->differences,
            $request->resellerId
        );

        $notificationCommand->notificationType = $request->notificationType;
        $notificationCommand->differences = $request->differences;

        $notificationCommand->templateData = [
            'COMPLAINT_ID' => $request->complaintId,
            'COMPLAINT_NUMBER' => $request->complaintNumber,
            'CREATOR_ID' => $creator->id,
            'CREATOR_NAME' => $creator->getFullName(),
            'EXPERT_ID' => $expert->id,
            'EXPERT_NAME' => $expert->getFullName(),
            'CLIENT_ID' => $notificationCommand->client->id,
            'CLIENT_NAME' => $notificationCommand->client->getFullName(),
            'CONSUMPTION_ID' => $request->consumptionId,
            'CONSUMPTION_NUMBER' => $request->consumptionNumber,
            'AGREEMENT_NUMBER' => $request->agreementNumber,
            'DATE' => $request->date,
            'DIFFERENCES' => $differences,
        ];

        // Если хоть одна переменная для шаблона не задана, то не отправляем уведомления
        foreach ($notificationCommand->templateData as $key => $tempData) {
            if (empty($tempData)) {
                throw new Exception("Template Data ({$key}) is empty!", 500);
            }
        }

        $notificationCommand->resellerEmail = getResellerEmailFrom($request->resellerId);

        if (!empty($this->resellerEmail)) {
            // Получаем email сотрудников из настроек
            $notificationCommand->employeeEmails = getEmailsByPermit(
                $notificationCommand->reseller->id,
                'tsGoodsReturn'
            );
        }

        return $notificationCommand;
    }


    private function getDifferencesDescription(
        NotificationTypeEnum $notificationType,
        DifferencesDto $differences,
        int $resellerId,
    ): string {
        if ($notificationType === NotificationTypeEnum::NEW) {
            return $this->templateParser->parse('NewPositionAdded', null, $resellerId);
        }

        if ($notificationType === NotificationTypeEnum::CHANGED && !empty($data['differences'])) {
            $templateData = [
                'FROM' => Status::getName($differences->from),
                'TO' => Status::getName($differences->to),
            ];

            return $this->templateParser->parse('PositionStatusHasChanged', $templateData, $resellerId);
        }

        return '';
    }

    /**
     * @throws Exception
     */
    private function validateClient(?Contractor $client, int $resellerId): void
    {
        if ($client === null || $client->type !== Contractor::TYPE_CUSTOMER || $client->Seller->id !== $resellerId) {
            throw new Exception('Client not found!', 400);
        }
    }

}