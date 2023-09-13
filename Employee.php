<?php

namespace NW\WebService\References\Operations\Notification;

/**
 * Class Employee
 * @package NW\WebService\References\Operations\Notification
 */
class Employee extends Contractor
{

    /**
     * @param $resellerId
     * @param $event
     *
     * @return string[]
     */
   public static function getEmailsByPermit($resellerId, $event)
    {
        // fakes the method
        return ['someemeil@example.com', 'someemeil2@example.com'];
    }
}