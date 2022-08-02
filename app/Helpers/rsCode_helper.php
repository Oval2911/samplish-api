<?php


function tempResponse($errorcode, $data = null)
{
    
 $error_messages = [
    '00000' => 'OK',
    '00001' => 'Empty result',
    '00002' => 'Failed adding new data',
    '00003' => 'Failed updating data',
    '00004' => 'New data are succesfully added',
    '00005' => 'Success',
    '00006' => 'Failed login',
    '00101' => 'Email and password does not match',
    '00102' => 'Access token not granted',
    '00103' => 'Email already exists',
    '00104' => 'Unknown Error',
];

    $resp = [
        'errorcode' => $errorcode,
        'errormsg' => $error_messages[$errorcode],
        'data' => $data,
    ];
    return $resp;
}
