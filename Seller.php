<?php

namespace NW\WebService\References\Operations\Notification;

/**
 * Class Seller
 * @package NW\WebService\References\Operations\Notification
 */
class Seller extends Contractor
{
    /**
     * @param $resellerId
     *
     * @return string
     */
   public static function getResellerEmailFrom($resellerId)
    {
        // по идеи здесь должен быть запрос к бд
        return 'contractor@example.com';
    }
}