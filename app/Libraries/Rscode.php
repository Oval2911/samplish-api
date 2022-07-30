<?php
//print_r($reports);<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Rscode
{
    protected $error_messages = [
        '0000' => 'OK',
        '0001' => 'Empty result',
        '0005' => 'Empty Poll Answer',
        '0002' => 'Failed adding new data',
        '0003' => 'Failed updating data',
        '0004' => 'Failed deleting data',
        '0005' => 'word that related to klubstory can not be use for a klub name',
        '0006' => 'Failed Grant user Create Klub ',
        '0007' => 'This user have been vote for this poll ',
        '0099' => 'Custom Error',
        '0100' => 'Failed Send OTP',
        '0101' => 'User and password does not match',
        '0102' => 'Access token not granted',
        '0103' => 'MSISDN already exists',
        '0104' => 'Failed creating referral code',
        '0105' => 'Failed verification code',
        '0106' => 'MSISDN Not exists',
        '0107' => 'OTP Code Not Valid',
        '0108' => 'Failed Save Password',
        '0109' => 'Password not match, please check your password written in your My Profile - Setting - Account section',
        '0110' => 'Email does not match',
        '0111' => 'You can not use your last password',
        '0151' => 'Cannot register new user',
        '0161' => 'Vendor not active',
        '0160' => 'Failed updating user data',
        '0165' => 'Failed inquiry',
        '0170' => 'Failed payment',
        '0171' => 'Failed validation code',
        '0172' => 'Role not granted',
        '0173' => 'This event has reached maximum attendees',
        '0174' => 'Only member can attend this Event',
        '0099' => 'Logout Failed',
        '0201' => 'User does not belong the group',
        '0300' => 'This user already invited',
        '0301' => 'This user cannot do reaction twice',
        '0901' => 'This POLL has reach maximal participant',

        '0302' => 'This Poll Result has already been sent',
        '0303' => 'This Klub not yet request VIP poll',
        '0304' => 'This Poll is not expired yet',

        // 08XX reserved for market
        '0800' => 'Error when creating Shopping Cart',
        '0801' => 'Product is not found or not active',
        '0802' => 'Cannot remove this Shopping Cart',
        '0803' => 'Failed to checkout this cart',
        '0804' => 'You are not the owner of this cart',
        '0805' => 'Failed processing this cart. Please contact Administrator',
        '0806' => 'Transaction is not found',
        '0807' => 'You are not the owner of this transaciton',
        '0808' => 'Invalid action',
        '0809' => 'Only admins could do this action',
        '0810' => 'Product promote is not found',
        '0811' => 'The store is not yours',
        '0812' => 'Shopping cart already processed',

    ];

    public function response($errorcode, $data)
    {
        $resp = [
            'errorcode' => $errorcode,
            'errormsg' => $this->error_messages[$errorcode],
            'data' => $data,
        ];
        return $resp;
    }

    public function responseBCA($data)
    {

        return $data;
    }
}
