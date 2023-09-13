<?php

namespace NW\WebService\References\Operations\Notification;

class MessagesClient
{

    /**
     * @param array $emails
     * @param       $emailFrom
     * @param       $ressellerId
     * @param       $event
     * @param       $templateData
     * @param       $result
     *
     * @return void
     */
    public static function sendMessage(array $emails,$emailFrom, $ressellerId,  $event,$templateData,&$result)
    {
        // делаем отправку
        foreach ($emails as $email) {
           [
                0 => [ // MessageTypes::EMAIL
                       'emailFrom' => $emailFrom,
                       'emailTo'   => $email,
                       'subject'   => __(Params::COMPLAINT_EMPLOYEE_EMAIL_SUBJECT, $templateData, $ressellerId),
                       'message'   => __(Params::COMPLAINT_EMPLOYEE_EMAIL_BODY, $templateData, $ressellerId),
                ],
            ];
            // делаем отправку
            $result[Params::NOTIFICATION_EMPLOYEE_BY_EMAI] = true;
        }
    }

    /**
     * @param $emailFrom
     * @param $resellerId
     * @param $client
     * @param $events
     * @param $differences
     * @param $templateData
     * @param $result
     *
     * @return void
     */
    public static function sendNotification($emailFrom, $resellerId, $client, $events, $differences,$templateData,&$result)
    {
        // делаем отправку
        $messege= [ // MessageTypes::EMAIL
               'emailFrom' => $emailFrom,
               'emailTo'   => $client->email,
               'subject'   => __(Params::COMPLAINT_CLIENT_EMAIL_SUBJECT, $templateData, $resellerId),
               'message'   => __(Params::COMPLAINT_CLIENT_EMAIL_BODY, $templateData, $resellerId),
    ];
        $result[Params::NOTIFICATION_CLIENT_BY_EMAI] = true;

        if (!empty($client->mobile)) {
            self::sendSMS($resellerId, $client->id, NotificationEvents::CHANGE_RETURN_STATUS, (int)$differences, $templateData, $error);
            if (is_null($error)) {
                $result[Params::NOTIFICATION_CLIENT_BY_SMS]['isSent'] = true;
            }
            if (!empty($error)) {
                $result[Params::NOTIFICATION_CLIENT_BY_SMS]['message'] = $error;
            }
        }
    }

    /**
     * @param                     $resellerId
     * @param                     $clientID
     * @param                     $changeStatus
     * @param                     $differences
     * @param                     $templateData
     * @param MessagesClient|null $error
     *
     * @return void
     */
    public static function sendSMS($resellerId, $clientID, $changeStatus, $differences, $templateData, & $error = null)
    {
        $statusSMS = true;
        ///условно делаем отпрвку
        if ($statusSMS === false) {
            $error = "Mistake to send SMS";
        }
    }
}