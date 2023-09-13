<?php

namespace NW\WebService\References\Operations\Notification;

/**
 * @property Seller $seller
 */
class Contractor
{
    const TYPE_CUSTOMER = 0;

    public $id;
    public $type;
    public $name;
    public $email;
    public $mobile;

    /**
     * @param int $resellerId
     *
     * @return static
     */
    public static function getById(int $resellerId): self
    {
        return new self($resellerId); // fakes the getById method
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->name . ' ' . $this->id;
    }


}