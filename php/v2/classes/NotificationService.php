<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification\classes;

use Exception;

readonly class NotificationService
{
    public function __construct(
        private TemplateParser $templateParser,
    ) {
    }

    public function send(NotificationCommand $notification): Result
    {
        $result = new Result();
        $result->notificationEmployeeByEmail = $this->sendEmployeeEmails($notification);
        $result->notificationClientByEmail = $this->sendClientEmail($notification);
        $result->notificationClientBySms = $this->sendClientSms($notification, $error);

        if (!empty($error)) {
            $result->errorMessage = $error;
        }

        return $result;
    }

    private function sendClientSms(
        NotificationCommand $notification,
        string &$error // пробрасываем ошибку наружу аналогично NotificationManager::send, раз уж там так криво сделано
    ): bool
    {
        // Шлём клиентское уведомление, только если произошла смена статуса
        if ($notification->notificationType === NotificationTypeEnum::CHANGED) {
            return false;
        }

        if (empty($this->client->mobile)) {
            return false;
        }

        $res = NotificationManager::send(
            $notification->reseller->id,
            $notification->client->id,
            NotificationEvents::CHANGE_RETURN_STATUS,
            $notification->differences->to,
            $notification->templateData,
            // Непонятно, это ошибка с передачей не-инициализированной выше переменной,
            // или передача по ссылке с целью возврата ошибки
            // Буду считать что второе, исхожу из того, что контракт NotificationManager::send() не пересматриваем,
            // хотя возвращать и $res и $error - идея странная
            $error
        );

        return (bool)$res;
    }

    private function sendClientEmail(NotificationCommand $notification,): bool
    {
        // Шлём клиентское уведомление, только если произошла смена статуса
        if ($notification->notificationType === NotificationTypeEnum::CHANGED) {
            return false;
        }

        if (empty($notification->resellerEmail) || empty($notification->client->email)) {
            return false;
        }

        $messages = [
            [ // MessageTypes::EMAIL
                'emailFrom' => $notification->resellerEmail,
                'emailTo' => $notification->client->email,
                'subject' => $this->templateParser->parse(
                    'complaintClientEmailSubject',
                    $notification->templateData,
                    $notification->reseller->id
                ),
                'message' => $this->templateParser->parse('complaintClientEmailBody', $notification->templateData, $notification->reseller->id),
            ],
        ];
        MessagesClient::sendMessage(
            messages: $messages,
            resellerId: $notification->reseller->id,
            clientId: $notification->client->id,
            returnStatus: NotificationEvents::CHANGE_RETURN_STATUS,
            differenceTo: $notification->differences->to,
        );

        return true;
    }

    private function sendEmployeeEmails(NotificationCommand $notification,): bool
    {
        if (empty($this->resellerEmail) || count($notification->employeeEmails) === 0) {
            return false;
        }

        // Тут, похоже, была ошибка в логике (отправка по одному сообщению в цикле),
        // поскольку метод sendMessage принимает список сообщений.
        $messages = [];
        foreach ($notification->employeeEmails as $email) {
            $messages[] = [ // MessageTypes::EMAIL
                'emailFrom' => $this->resellerEmail,
                'emailTo' => $email,
                'subject' => $this->templateParser->parse(
                    'complaintEmployeeEmailSubject',
                    $notification->templateData,
                    $notification->reseller->id
                ),
                'message' => $this->templateParser->parse(
                    'complaintEmployeeEmailBody',
                    $notification->templateData,
                    $notification->reseller->id
                ),
            ];
        }
        //Сигнатура метода sendMessage не совпадает в двух вызовах, если какие-то параметры необязательные,
        // то в PHP 8.2 можно как-то так:
        MessagesClient::sendMessage(
            messages: $messages,
            resellerId: $notification->reseller->id,
            returnStatus: NotificationEvents::CHANGE_RETURN_STATUS
        );

        return true;
    }
}