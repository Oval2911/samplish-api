<?php


// params: error code, data || null, custom error message
function tempResponse($errorcode, $data=null, $msg="")
{
    
    $error_messages = [
        '00000' => 'OK',
        '00001' => 'Empty result',
        '00002' => 'Failed adding new data',
        '00003' => 'Failed updating data',
        '00004' => 'New data are succesfully added',
        '00005' => 'Success',
        '00006' => 'Failed login',
        '00007' => 'Failed deleting data',
        '00101' => 'Email and password does not match',
        '00102' => 'Access token not granted',
        '00103' => 'Email already exists',
        '00104' => 'Internal server error',
    ];

    $errormsg = $error_messages[$errorcode];
    if(is_object($msg) || is_array($msg)){
        foreach($msg as $k => $v){ // if error message from validation
            $errormsg = $v;
            break;
        }
    }else if($msg!=""){ // if string
        $errormsg = $msg;
    }

    $resp = [
        'errorcode' => $errorcode,
        'errormsg' => $errormsg,
        'data' => $data,
    ];
    return $resp;
}
