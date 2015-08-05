<?php

namespace SpikeTeam\UserBundle\Helper;

class UserHelper{

    public function __construct() {
    }

    /**
     * Process number so that it will work w/ Twilio
     *
     * @param string $phoneNumber
     * @return string
     */
    public function processNumber($phoneNumber)
    {
        $return = false;
        $phoneNumber = str_replace(
            array('-', ' ', '(', ')', '+', '{', '}', '.', '/', '\\'), '', $phoneNumber
        );
        if (ctype_digit($phoneNumber)) {
            switch (strlen($phoneNumber)) {
                case 10:
                    $return = '1' . $phoneNumber;
                    break;
                case 11:
                    $return = $phoneNumber;
                    break;
            }
        }
        return $return;
    }
}
