<?php

namespace NW\WebService\References\Operations\Notification;

/**
 * Class Status
 * @package NW\WebService\References\Operations\Notification
 */
class Status
{
    public $id;
    public  $name;

    /**
     * @param int $id
     *
     * @return string
     */
    public static function getName(int $id): string
    {
        $status_names = [
            0 => 'Completed',
            1 => 'Pending',
            2 => 'Rejected',
        ];

        return  $status_names[$id];
    }
}