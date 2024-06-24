<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification;

use Exception;
use NW\WebService\References\Operations\Notification\classes\ContractorRepository;
use NW\WebService\References\Operations\Notification\classes\NotificationCommandCreationService;
use NW\WebService\References\Operations\Notification\classes\NotificationService;
use NW\WebService\References\Operations\Notification\classes\ReferencesOperation;
use NW\WebService\References\Operations\Notification\classes\RequestDto;
use NW\WebService\References\Operations\Notification\classes\TemplateParser;

/**
 * Это контроллер, который обрабатывает http-запрос и рассылает Email и SMS уведомления,
 * используя шаблонизатор сообщений, и возвращает статусы отправки уведомлений.
 * Изначально:
 * - код неработоспособный т.к. содержит в т.ч. ошибки синтаксиса, бизнес-логики, валидации входящих данных.
 * - код совершенно не структурирован - вся логика заключена в единственный метод контроллера.
 * - перемешана логика обработки запроса, валидации, получения данных, отправки уведомлений и формирования ответа
 * - часть переменных имеет бессмысленные короткие названия.
 *
 * Что сделано:
 * - Входящие данные в виде массива сразу валидируются и преобразуются в DTO с типизированными полями, в дальнейшем
 * работа идет только с DTO
 * - Основной функционал реализован через паттерн "Command", основные элементы которого
 *      - NotificationCommand - данные, используемые при отправке уведомлений
 *      - NotificationCommandCreationService - сервис подготовки данных
 *      - NotificationService - сервис, выполняющие отправку сообщений
 * - Получение данных о Contractors (предположительно, из БД) - вынесена в репозиторий вместе с проверками
 * - Функция шаблонизации обернута в сервис
 * - Статусы вынесены в Enum из констант
 * - Исправлен нейминг, синтаксис, типизация
 *
 * p.s. Я принимал others.php как "внешний код", равно как и контракты предложенных "as is" функций.
 * Там тоже не все хорошо - например, getById возвращает self, хотя по логике должен static,
 * класс Status неплохо бы заменить на Enum и т.д.
 */
class TsReturnOperation extends ReferencesOperation
{
    private readonly NotificationService $notificationService;
    private readonly NotificationCommandCreationService $commandCreationService;

    public function __construct()
    {
        //Это обычно делает DI-контейнер )
        $this->notificationService = new NotificationService(
            new TemplateParser()
        );
        $this->commandCreationService = new NotificationCommandCreationService(
            new TemplateParser(),
            new ContractorRepository()
        );
    }

    /**
     * @throws Exception
     */
    public function doOperation(): array
    {
        $data = (array)$this->getRequest('data');
        $request = RequestDto::createFromArray($data);
        $notificationCommand = $this->commandCreationService->create($request);

        return $this->notificationService->send($notificationCommand)->toArray();
    }
}
