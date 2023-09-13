<?php


namespace NW\WebService\References\Operations\Notification;

/**
 * Class TsReturnOperation
 * @package NW\WebService\References\Operations\Notification
 */
class TsReturnOperation extends ReferencesOperation
{
    public const TYPE_NEW    = 1;
    public const TYPE_CHANGE = 2;

    /**
     * @throws \Exception
     */
    public function doOperation(): array
    {
        $data             = (array)$this->getRequest(Params::DATA);
        $resellerId       = $data[Params::RESELLER_ID];
        $notificationType = (int)$data[Params::NOTIFICATION_TYPE];
        $result           = [
            Params::NOTIFICATION_EMPLOYEE_BY_EMAI => false,
            Params::NOTIFICATION_CLIENT_BY_EMAI   => false,
            Params::NOTIFICATION_CLIENT_BY_SMS    => [
                'isSent'  => false,
                'message' => '',
            ],
        ];
        $reseller         = Seller::getById((int)$resellerId);
        $client           = Contractor::getById((int)$data[Params::CLIENT_ID]);
        $creatorId        = Employee::getById((int)$data[Params::CREATOR_ID]);
        $expertId         = Employee::getById((int)$data[Params::EXPERT_ID]);

        $this->volidatingData($result, $resellerId, $notificationType, $reseller, $client, $creatorId, $expertId);

        if ($result[Params::NOTIFICATION_CLIENT_BY_SMS]['message'] !== false) {
            return $result;
        }

        //получаем имя плюс айди
        $cFullName = $client->getFullName();

        $templateData = $this->formedTemplate($notificationType, $resellerId, $data, $creatorId, $expertId, $cFullName);
        // получаем email
        $emailFrom = Seller::getResellerEmailFrom($resellerId);

        // Получаем email сотрудников из настроек
        $emails = Employee::getEmailsByPermit($resellerId, NotificationEvents::TS_GOOD_RETURN);

        if (!empty($emailFrom) && count($emails) > 0) {
            MessagesClient::sendMessage($emails, $emailFrom, $resellerId,
                NotificationEvents::CHANGE_RETURN_STATUS, $templateData, $result);
        }

        // Шлём клиентское уведомление, только если произошла смена статуса
        if ($notificationType === self::TYPE_CHANGE && is_int($data[Params::DIFFERENCES]['to'])) {
            if (!empty($emailFrom) && !empty($client->email)) {
                MessagesClient::sendNotification($emailFrom, $resellerId, $client, NotificationEvents::CHANGE_RETURN_STATUS,
                    (int)$data[Params::DIFFERENCES]['to'], $templateData, $result);
            }
        }

        return $result;
    }

    /**
     * @param            $result
     * @param            $resellerId
     * @param            $notificationType
     * @param            $reseller
     * @param Contractor $client
     * @param            $creatorId
     * @param            $expertId
     *
     * @return array|void
     * @throws \Exception
     */
    private function volidatingData(&$result, $resellerId, $notificationType, $reseller, Contractor $client, $creatorId, $expertId)
    {
        if (empty((int)$resellerId)) {
            $result[Params::NOTIFICATION_CLIENT_BY_SMS]['message'] = 'Empty resellerId';
            return $result;
        }

        if (empty((int)$notificationType)) {
            return throw new \Exception('Empty notificationType', 404);
        }

        if ($reseller === null) {
            return throw new \Exception('Seller not found!', 404);
        }

        if ($client === null || $client->type !== Contractor::TYPE_CUSTOMER || $client->seller->id !== $resellerId) {
            return throw new \Exception('Client not found!', 404);
        }

        if ($creatorId === null) {
            return throw new \Exception('Creator not found!', 404);
        }

        if ($expertId === null) {
            return throw new \Exception('Expert not found!', 404);
        }
    }

    /**
     * @param $notificationType
     * @param $resellerId
     * @param $data
     * @param $creatorId
     * @param $expertId
     * @param $cFullName
     *
     * @return array
     * @throws \Exception
     */
    private function formedTemplate($notificationType, $resellerId, $data, $creatorId, $expertId, $cFullName)
    {
        $differences = '';

        if ($notificationType === self::TYPE_NEW) {
            $differences = __(Params::NEW_POSITION_ADDED, null, $resellerId);
        } elseif ($notificationType === self::TYPE_CHANGE && !empty($data[Params::DIFFERENCES])) {
            $differences = __(Params::POSTION_STATUS_HAS_CHANGED, [
                'FROM' => Status::getName((int)$data[Params::DIFFERENCES]['from']),
                'TO'   => Status::getName((int)$data[Params::DIFFERENCES]['to']),
            ], $resellerId);
        }

        $templateData = [
            'COMPLAINT_ID'       => (int)$data[Params::COMPLAINT_ID],
            'COMPLAINT_NUMBER'   => (string)$data[Params::COMPLAINT_NUMBER],
            'CREATOR_ID'         => (int)$data[Params::CREATOR_ID],
            'CREATOR_NAME'       => $creatorId->getFullName(),
            'EXPERT_ID'          => (int)$data[Params::EXPERT_ID],
            'EXPERT_NAME'        => $expertId->getFullName(),
            'CLIENT_ID'          => (int)$data[Params::CLIENT_ID],
            'CLIENT_NAME'        => $cFullName,
            'CONSUMPTION_ID'     => (int)$data[Params::CONSUMPTION_ID],
            'CONSUMPTION_NUMBER' => (string)$data[Params::CONSUMPTION_NUMBER],
            'AGREEMENT_NUMBER'   => (string)$data[Params::AGREEMENT_NUMBER],
            'DATE'               => (string)$data[Params::DATA],
            'DIFFERENCES'        => $differences,
        ];

        // Если хоть одна переменная для шаблона не задана, то не отправляем уведомления
        foreach ($templateData as $key => $tempData) {
            if (empty($tempData)) {
                throw new \Exception("Template Data ({$key}) is empty!", 500);
            }
        }

        return $templateData;
    }
}