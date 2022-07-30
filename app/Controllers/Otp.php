<?php

defined('BASEPATH') or exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions

require APPPATH . '/libraries/REST_Controller.php';
include_once APPPATH . 'libraries/gcm.php';
// include_once APPPATH . 'libraries/CSVReader.php';

class Otp extends REST_Controller
{
    private $_exec_time_start;

    public function __construct()
    {

        // Construct the parent class
        parent::__construct();

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['user_get']['limit'] = 1; // 500 requests per hour per user/key
        $this->methods['user_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['user_delete']['limit'] = 50; // 50 requests per hour per user/key
        $this->load->model("User_model");
        $this->load->model("Otp_model");
        $this->load->library('Rscode');
        $this->_exec_time_start = microtime(true);
        setlocale(LC_MONETARY, 'en_GB');
    }

    public function signup_send_post()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = $this->post();
        $data_json = $data;

        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/otp/signup_send',
            'request_method' => 'POST',
            'request_content' => serialize($data),
        );
        //
        // 2. Log the service call
        //
        $request_id = $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check
        //  POST otp/signup/send(channel, msisdn, expiry-time); channel: "WA/SMS", msisdn, expiry-time: 90s
        $res_code;
        $res;

        //check last send otp

        // if ($data_json['channel'] == 'WA') {
        //     $type = 'W';
        // } else if ($data_json['channel'] == 'SMS') {
        //     $type = 'S';
        // } else if ($data_json['channel'] == 'Telegram') {
        //     $type = 'T';
        // } else if ($data_json['channel'] == 'EMAIL') {
        //     $type = 'E';
        // }

        // if ($data_json['channel'] == 'EMAIL') {

            $data = array(
                // 'iduser' => $res[0]['iduser'],
                'otp_code' => rand(10000000, 99999999),
                'otp_retry_count' => 0,
                'otp_target' => $data_json['email'],
                'type' => 'E',
                'status' => 'N',
            );
            $filter['filter'] = array('otp_target' => $data_json['email'], 'status' => 'S', 'ts_expired' => date("Y-m-d H:i:s"));
            $filterupdate = array('otp_target' => $data_json['email'], 'status' => 'S');
        // }

        $otp = $this->Otp_model->get_login_otp('*', $filter);
        if ($otp) {
            $dataVoid = array(
                'otp_code' => $otp[0]['otp_code'],
                'otp_target' => $otp[0]['otp_target'],
                'status' => 'V',
            );

            $updateOtp = $this->Otp_model->update_login_otp($dataVoid, $filterupdate);
        }
        //insert login otp
        $newOtp = $this->Otp_model->insert_login_otp($data);
        //send to service by channel
        // if ($data_json['channel'] == 'EMAIL') {
            // $res = $this->_send_email($data);
            $parEmail = array(
                'email' => $data_json['email'],
                'message' => 'Email OTP Register: Your OTP is ' . $data['otp_code'] . '. Keep this code privately. Enjoy for being FOMO!',
                'subject' => 'OTP registration',
            );
            $res = $this->_send_email($parEmail);
        // }

        $adate = date("Y-m-d H:i:s");
        // $duration = 1090;
        $duration = $data_json['expiry-time'];
        $dateinsec = strtotime($adate);
        $newdate = $dateinsec + $duration;
        // echo date('D M H:i:s Y', $newdate);
        $filterSent = array('idotp' => $newOtp);
        $datasent = array(
            'ts_last_otp_sent' => $adate,
            'status' => ($res == 1) ? 'S' : 'N',
            'ts_expired' => date("Y-m-d H:i:s", $newdate),
        );

        $res = $this->Otp_model->update_login_otp($datasent, $filterSent);

        $res_code = REST_Controller::HTTP_OK;
        $res = $this->rscode->response('0000', true);
        $this->set_response($res, $res_code);

        //
        // Finishing and log it up
        // config_item('ecommerce:order_status')['ecommerce'][$item->order_status];

        //usleep( 5*1000000 );
        $exec_time = microtime(true) - $this->_exec_time_start;

        $data = array(
            'iduser_access_log_request' => $request_id,
            'execution_time' => $exec_time,
            'response_code' => $res['errorcode'],
            'response_content' => serialize($res),
        );
        $this->User_model->insert_user_access_log_response($data);

    }

    public function signup_check_post()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = $this->post();
        $data_json = $data;

        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/otp/check',
            'request_method' => 'POST',
            'request_content' => serialize($data),
        );
        //
        // 2. Log the service call
        //
        $request_id = $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res;

        //check last send otp
        $filter['filter'] = array('otp_target' => $data_json['email'], 'otp_code' => $data_json['otp_code'], 'status' => 'S', 'ts_expired' => date("Y-m-d H:i:s"));
        $otp = $this->Otp_model->get_login_otp('*', $filter);
        // print_r($otp);
        if ($otp) {
            $filter = array('idotp' => $otp[0]['idotp']);
            $data = array(
                'status' => 'C',
            );
            $res = $this->Otp_model->update_login_otp($data, $filter);
            //login add session

            $res_code = REST_Controller::HTTP_OK;
            $res = $this->rscode->response('0000', true);
            $this->set_response($res, $res_code);
        } else {
            $res_code = REST_Controller::HTTP_OK;
            $res = $this->rscode->response('0171', false);
            $this->set_response($res, $res_code);
        }

        //
        // Finishing and log it up
        // config_item('ecommerce:order_status')['ecommerce'][$item->order_status];

        //usleep( 5*1000000 );
        $exec_time = microtime(true) - $this->_exec_time_start;

        $data = array(
            'iduser_access_log_request' => $request_id,
            'execution_time' => $exec_time,
            'response_code' => $res['errorcode'],
            'response_content' => serialize($res),
        );
        $this->User_model->insert_user_access_log_response($data);

    }

    // LOGIN

    public function loginSend_post()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = $this->post();
        $data_json = $data;

        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/otp/loginSend',
            'request_method' => 'POST',
            'request_content' => serialize($data),
        );
        //
        // 2. Log the service call
        //
        $request_id = $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res;
        $filter['filter']['msisdn'] = $data_json['msisdn'];
        $res = $this->User_model->get_user('*', $filter);

        if (empty($res)) {
            $res = $this->rscode->response('0106', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {
            //check last send otp
            if ($data_json['channel'] == 'WA') {
                $type = 'W';
            } else if ($data_json['channel'] == 'SMS') {
                $type = 'S';
            } else if ($data_json['channel'] == 'Telegram') {
                $type = 'T';
            }

            $filter['filter'] = array('otp_target' => $data_json['msisdn'], 'status' => 'S', 'ts_expired' => date("Y-m-d H:i:s"), 'type' => $type);

            // $filter['filter'] = array('otp_target' => $data_json['msisdn'], 'status' => 'S', 'ts_expired' => date("Y-m-d H:i:s"));
            $otp = $this->Otp_model->get_login_otp('*', $filter);
            if ($otp) {
                $dataVoid = array(
                    'iduser' => $otp[0]['iduser'],
                    'otp_code' => $otp[0]['otp_code'],
                    // 'otp_retry_count' => $otp[0]['otp_retry_count'],
                    'otp_target' => $otp[0]['otp_target'],
                    'status' => 'V',
                );
                $filterupdate = array('otp_target' => $data_json['msisdn'], 'status' => 'S');

                $updateOtp = $this->Otp_model->update_login_otp($dataVoid, $filterupdate);
            }
            //insert login otp
            $data = array(
                'iduser' => $res[0]['iduser'],
                'otp_code' => rand(1000, 9999),
                'otp_retry_count' => 0,
                'otp_target' => $data_json['msisdn'],
                'type' => $type,
                'status' => 'N',
            );
            $newOtp = $this->Otp_model->insert_login_otp($data);

            //send to service by channel
            if ($data_json['channel'] == 'WA') {
                // http://localhost:8888/wa/send/?devicename=marta&to=62811200480&msg=mantap&devicename=marta
                $msg = 'WA OTP Login: Your Login OTP is *' . $data['otp_code'] . '*. Keep this code privately. Enjoy *klubstory*!';
                $msg = array('devicename' => 'otp',
                    'to' => $data['otp_target'],
                    'msg' => $msg,
                );
                $msg = http_build_query($msg);
                $url = 'http://localhost:8889/wa/send/?' . $msg;
                $res = $this->_getCURL($url);
                $sentStatus = 1;

            } elseif ($data_json['channel'] == 'SMS') {
                $res = $this->_sendSMS($data['otp_target'], $data['otp_code'], 'Login');
                if ($res['text'] == 'Success') {
                    $sentStatus = 1;
                }
            } elseif ($data_json['channel'] == 'EMAIL') {
                $parEmail = array(
                    'email' => $data_json['email'],
                    'message' => 'Email OTP Login: Your OTP is ' . $data['otp_code'] . '. Keep this code privately. Enjoy Klubstory!',
                    'subject' => 'OTP Login',
                );
                $res = $this->_send_email($parEmail);
            }

            $adate = date("Y-m-d H:i:s");
            $duration = $data_json['expiry-time'];
            $dateinsec = strtotime($adate);
            $newdate = $dateinsec + $duration;
            // echo date('D M H:i:s Y', $newdate);
            $filterSent = array('idotp' => $newOtp);
            $datasent = array(
                'ts_last_otp_sent' => $adate,
                'status' => ($sentStatus == 1) ? 'S' : 'N',
                'ts_expired' => date("Y-m-d H:i:s", $newdate),
                // 'otp_retry_count' => $data['otp_retry_count'] ? $data['otp_retry_count'] + 1 : $data['otp_retry_count'] + 1,
            );

            $res = $this->Otp_model->update_login_otp($datasent, $filterSent);

            $res_code = REST_Controller::HTTP_OK;
            $res = $this->rscode->response('0000', $res);
            $this->set_response($res, $res_code);

        }
        //
        // Finishing and log it up
        // config_item('ecommerce:order_status')['ecommerce'][$item->order_status];

        //usleep( 5*1000000 );
        $exec_time = microtime(true) - $this->_exec_time_start;

        $data = array(
            'iduser_access_log_request' => $request_id,
            'execution_time' => $exec_time,
            'response_code' => $res['errorcode'],
            'response_content' => serialize($res),
        );
        $this->User_model->insert_user_access_log_response($data);

    }

    // Reset Password

    public function resetPassword_post()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = $this->post();
        $data_json = $data;

        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/otp/resetPassword',
            'request_method' => 'POST',
            'request_content' => serialize($data),
        );
        //
        // 2. Log the service call
        //
        $request_id = $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res;
        if ($this->post('email')) {
            $filter['filter']['email'] = $data_json['email'];
        } else {
            $filter['filter']['msisdn'] = $data_json['msisdn'];

        }

        // $res = $this->User_model->get_user('*', $filter);

        // if (empty($res)) {
        //     $res = $this->rscode->response('0106', null);
        //     $session = false;
        //     $res_code = REST_Controller::HTTP_UNAUTHORIZED;
        //     $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        // } else {
        //check last send otp
        if ($data_json['channel'] == 'WA') {
            $type = 'W';
            $target = $this->post('msisdn');
            $otp_code = rand(1000, 9999);
        } else if ($data_json['channel'] == 'SMS') {
            $type = 'S';
            $target = $this->post('msisdn');
            $otp_code = rand(1000, 9999);
        } else if ($data_json['channel'] == 'TELEGRAM') {
            $type = 'T';
            $target = $this->post('msisdn');
            $otp_code = rand(1000, 9999);
        } else if ($data_json['channel'] == 'EMAIL') {
            $type = 'E';
            $target = $this->post('email');
            $otp_code = rand(10000000, 99999999);
        }

        $filter['filter'] = array('otp_target' => $target, 'status' => 'S', 'ts_expired' => date("Y-m-d H:i:s"), 'type' => $type);
        $otp = $this->Otp_model->get_login_otp('*', $filter);
        if ($otp) {
            $dataVoid = array(
                'iduser' => $otp[0]['iduser'],
                'otp_code' => $otp[0]['otp_code'],
                // 'otp_retry_count' => $otp[0]['otp_retry_count'],
                'otp_target' => $otp[0]['otp_target'],
                'status' => 'V',
            );
            $filterupdate = array('otp_target' => $target, 'status' => 'S');

            $updateOtp = $this->Otp_model->update_login_otp($dataVoid, $filterupdate);
        }
        //insert login otp
        $data = array(
            'iduser' => $iduser,
            'otp_code' => $otp_code,
            'otp_retry_count' => 0,
            'otp_target' => $target,
            'type' => $type,
            'status' => 'N',
        );
        $newOtp = $this->Otp_model->insert_login_otp($data);
        $sentStatus = 0;

        //send to service by channel
        if ($data_json['channel'] == 'WA') {
            // http://localhost:8888/wa/send/?devicename=marta&to=62811200480&msg=mantap&devicename=marta
            // $msg = 'Klubstory: Kode verifikasi ' . $data['otp_code'] . '. JANGAN BERIKAN kode kepada siapapun, TERMASUK TIM Klubstory.';
            $msg = 'WA OTP : Your OTP is ' . $data['otp_code'] . '. Keep this code privately. Enjoy Klubstory!';

            $msg = array('devicename' => 'otp',
                'to' => $data['otp_target'],
                'msg' => $msg,
            );
            $msg = http_build_query($msg);
            $url = 'http://localhost:8889/wa/send/?' . $msg;
            $res = $this->_getCURL($url);
            $sentStatus = 1;

        } elseif ($data_json['channel'] == 'SMS') {
            $res = $this->_sendSMS($data['otp_target'], $data['otp_code'], 'Reset Password');
            if ($res['text'] == 'Success') {
                $sentStatus = 1;
            }
        } elseif ($data_json['channel'] == 'EMAIL') {
            $parEmail = array(
                'email' => $data_json['email'],
                'message' => 'Email OTP Reset Password: Your OTP is ' . $data['otp_code'] . '. Keep this code privately. Enjoy Klubstory!',
                'subject' => 'OTP Reset Password',
            );
            $res = $this->_send_email($parEmail);
            $sentStatus = 1;

        }

        $adate = date("Y-m-d H:i:s");
        $duration = $data_json['expiry-time'];
        $dateinsec = strtotime($adate);
        $newdate = $dateinsec + $duration;
        // echo date('D M H:i:s Y', $newdate);
        $filterSent = array('idotp' => $newOtp);
        $datasent = array(
            'ts_last_otp_sent' => $adate,
            'status' => ($sentStatus == 1) ? 'S' : 'N',
            'ts_expired' => date("Y-m-d H:i:s", $newdate),
            // 'otp_retry_count' => $data['otp_retry_count'] ? $data['otp_retry_count'] + 1 : $data['otp_retry_count'] + 1,
        );

        $res = $this->Otp_model->update_login_otp($datasent, $filterSent);
        if ($res > 0) {
            $res = true;
        } else {
            $res = false;
        }
        $res_code = REST_Controller::HTTP_OK;
        $res = $this->rscode->response('0000', $res);
        $this->set_response($res, $res_code);

        // }
        //
        // Finishing and log it up
        // config_item('ecommerce:order_status')['ecommerce'][$item->order_status];

        //usleep( 5*1000000 );
        $exec_time = microtime(true) - $this->_exec_time_start;

        $data = array(
            'iduser_access_log_request' => $request_id,
            'execution_time' => $exec_time,
            'response_code' => $res['errorcode'],
            'response_content' => serialize($res),
        );
        $this->User_model->insert_user_access_log_response($data);

    }

    // Reset Complete profile

    public function completeprofile_post()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = $this->post();
        $data_json = $data;

        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/otp/completeprofile',
            'request_method' => 'POST',
            'request_content' => serialize($data),
        );
        //
        // 2. Log the service call
        //
        $request_id = $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        //
        // 3. Check access token
        //
        $res_code;
        $res;
        $session = true;
        // print_r($this->get('iduser'));
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            // $res = $this->rscode->response('00102', null);
            $res = $this->rscode->response('0102', null);

            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {
            //check last send otp
            // if ($data_json['channel'] == 'WA') {
            //     $type = 'W';
            // } else if ($data_json['channel'] == 'SMS') {
            //     $type = 'S';
            // } else if ($data_json['channel'] == 'TELEGRAM') {
            //     $type = 'T';
            // } else if ($data_json['channel'] == 'EMAIL') {
            $type = 'E';
            // }

            $filter['filter'] = array('otp_target' => $data_json['email'], 'status' => 'S', 'ts_expired' => date("Y-m-d H:i:s"), 'type' => $type);
            $otp = $this->Otp_model->get_login_otp('*', $filter);
            if ($otp) {
                $dataVoid = array(
                    'iduser' => $otp[0]['iduser'],
                    'otp_code' => $otp[0]['otp_code'],
                    // 'otp_retry_count' => $otp[0]['otp_retry_count'],
                    'otp_target' => $otp[0]['otp_target'],
                    'status' => 'V',
                );
                $filterupdate = array('otp_target' => $data_json['email'], 'status' => 'S');

                $updateOtp = $this->Otp_model->update_login_otp($dataVoid, $filterupdate);
            }
            //insert login otp
            $data = array(
                'iduser' => $iduser,
                'otp_code' => rand(10000000, 99999999),
                'otp_retry_count' => 0,
                'otp_target' => $data_json['email'],
                'type' => $type,
                'status' => 'N',
            );
            $newOtp = $this->Otp_model->insert_login_otp($data);
            $sentStatus = 0;

            $parEmail = array(
                'email' => $data_json['email'],
                'message' => 'Email OTP to complete your profile : Your OTP is ' . $data['otp_code'] . '. Keep this code privately. Enjoy Klubstory!',
                'subject' => 'OTP Complete Profile',
            );
            $res = $this->_send_email($parEmail);
            $sentStatus = 1;

            $adate = date("Y-m-d H:i:s");
            $duration = $data_json['expiry-time'];
            $dateinsec = strtotime($adate);
            $newdate = $dateinsec + $duration;
            // echo date('D M H:i:s Y', $newdate);
            $filterSent = array('idotp' => $newOtp);
            $datasent = array(
                'ts_last_otp_sent' => $adate,
                'status' => ($sentStatus == 1) ? 'S' : 'N',
                'ts_expired' => date("Y-m-d H:i:s", $newdate),
                // 'otp_retry_count' => $data['otp_retry_count'] ? $data['otp_retry_count'] + 1 : $data['otp_retry_count'] + 1,
            );

            $res = $this->Otp_model->update_login_otp($datasent, $filterSent);

            $res_code = REST_Controller::HTTP_OK;
            $res = $this->rscode->response('0000', $res);
            $this->set_response($res, $res_code);

        }
        //
        // Finishing and log it up
        // config_item('ecommerce:order_status')['ecommerce'][$item->order_status];

        //usleep( 5*1000000 );
        $exec_time = microtime(true) - $this->_exec_time_start;

        $data = array(
            'iduser_access_log_request' => $request_id,
            'execution_time' => $exec_time,
            'response_code' => $res['errorcode'],
            'response_content' => serialize($res),
        );
        $this->User_model->insert_user_access_log_response($data);

    }

    public function _getCURL($url)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        return $res;
    }

    public function _send_email($data)
    {
//
        $email = $data['email'];
        $subject = $data['subject'];
        $message = $data['message'];
        $file = '';
        // $bcc
        $config = array(
            'protocol' => 'smtp',
            'smtp_host' => 'ssl://smtp.googlemail.com',
            'smtp_port' => 465,
            'smtp_user' => 'klubstory@gmail.com',
            'smtp_pass' => 'klubstory1212',
            'mailtype' => 'html',
            'charset' => 'iso-8859-1',
            'wordwrap' => true,
        );

        $this->load->library('email', $config);
        $this->email->set_newline("\r\n");
        $this->email->from('klubstory@gmail.com');
        $this->email->to($email);
        if (!empty($bcc)) {
            $this->email->bcc($bcc);
        }
        $this->email->subject($subject);
        $this->email->message($message);
        if (!empty($file)) {
            $this->email->attach($file);
        }
        if ($this->email->send()) {
            return 1;
        } else {
            return show_error($this->email->print_debugger());
        }

    }

    public function _sendSMS($telepon, $otp, $action)
    {
        $text = $otp;
        $pecah = preg_split('//', $text, -1, PREG_SPLIT_NO_EMPTY);
//            print_r($pecah);
        //            echo "<br/>";
        $otp = '';
        foreach ($pecah as $id) {
            $otp .= $id . '-';
        }
        $otp = substr_replace($otp, '', strlen($otp) - 1, 1);

        $userkey = 'cb8923c6996c';
        $passkey = 'vnphtk47zn';
//        $message = '#klubstory# Masukkan  ' . $otp . '  di aplikasi, untuk ' . $action . ' Terima Kasih.';
        $message = '#klubstory# Please enter ' . $otp . ' on the application to complete your ' . $action . ' process. Thank You!';
        $url = 'https://gsm.zenziva.net/api/sendsms/';
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, array(
            'userkey' => $userkey,
            'passkey' => $passkey,
            'nohp' => $telepon,
            'pesan' => $message,
        ));
        $results = json_decode(curl_exec($curlHandle), true);
        curl_close($curlHandle);
        return $results;
    }

}
