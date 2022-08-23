<?php
namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use App\Models\AuthModel;
use App\Models\User_model;
// use App\Models\Otp_model;
// use App\Models\Md_model;
// use App\Models\Notif_model;

use App\Models\SamplersModel;

class User extends ResourceController
{
    private $_exec_time_start;

    public function __construct()
    {

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['user_get']['limit'] = 1; // 500 requests per hour per user/key
        $this->methods['user_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['user_delete']['limit'] = 50; // 50 requests per hour per user/key
        //print_r($this->methods);

        $this->request = \Config\Services::request();

        $this->AuthModel  = new AuthModel();
        $this->User_model  = new User_model();
        // $this->Otp_model  = new Otp_model();
        // $this->Md_model  = new Md_model();
        // $this->Notif_model  = new Notif_model();

        $this->samplersModel  = new SamplersModel();

        helper(['custom', 'rsCode']);

        $this->_exec_time_start = microtime(true);
        setlocale(LC_MONETARY, 'en_GB');
    }

    public function test_get()
    {
        echo $this->encrypt_decrypt_login('encrypt', 'bcacred');
        // $first_name = $this->get('first');
        // $last_name = $this->get('last');

        // $resp = "Hello " . $first_name . ' ' . $last_name;

        // $res_code = REST_Controller::HTTP_OK;
        // $this->set_response($resp, $res_code);
    }

    public function testFirebase_get()
    {

        // 3. send notification
        // $gcm = new GCM();
        // $msg = array("body" => "Test send firebase", "title" => "test", "click_action" => "FLUTTER_NOTIFICATION_CLICK");
        // $result = $gcm->send_notification('e5TDcGd4R46onRtVH3tOk7:APA91bGQwm8ZVwvGKMFEPkNdr2jWBXN8EU18e-fOd1wEc-QwaNslOyU622rOHVY-sS2mgR7okE3eVx73BMUdqQRF7S_uNXl9nHRDtPb7ME2jI_r2v0HueFBB9Cy7rI_CeAMrfnpip_gp', $msg);

        $klubmembernotif = $this->Klub_model->get_klubmember(array('klub_member.iduser'), array('filter' => array('klub_member.idklub' => 1, 'klub_member.iduser !=' => 5)));
        $fcmMember = array();
        foreach ($klubmembernotif as $vk) {
            $fcmMember[] = $vk['iduser'];

        }
        // 2. cek fcmid and send notif

        $filter['filter'] = array('status' => 0, 'fcm_id !=' => '');
        $filter['filterIn'] = array('iduser' => $fcmMember);
        // print_r($filter);
        $datafcmid = $this->User_model->get_agent_login_session('fcm_id', $filter);
        // $rslt['regid'] = $datafcmid;
        $fcmid = array();
        if ($datafcmid) {
            foreach ($datafcmid as $c) {
                // $fcmid[] = $c['fcm_id'];

                array_push($fcmid, $c['fcm_id']);
            }
            $gcm = new GCM();
            $msg = array("body" => 'empty', "title" => "test", "click_action" => "FLUTTER_NOTIFICATION_CLICK");
            $result = $gcm->send_notification($fcmid, $msg);

            $rslt['fcmid'] = $result;
        }
        print_r($result);

    }

    public function test_post()
    {
        $day = array(
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            'sunday',
        );

        $opr = array();
        foreach ($day as $kd => $vd) {
            $opr[$vd] = $_POST[$vd];
            unset($_POST[$vd]);
        }
        $_POST['operational'] = json_encode($opr);
        print_r(json_encode($_POST));
        // $first_name = $this->post('first');
        // $last_name = $this->post('last');
        // var_dump(getallheaders());
        // echo 'halo ' . $first_name . ' ' . $last_name;

        // if (isset($_FILES)) {
        //     echo 'Ada file';

        //     var_dump($_FILES);
        // }

        // $res_code = REST_Controller::HTTP_OK;
        // $this->set_response(null, $res_code);

    }

    public function test_put()
    {
        $first_name = $this->put('first');
        $last_name = $this->put('last');

        $resp = "Hello " . $first_name . ' ' . $last_name;

        $res_code = REST_Controller::HTTP_OK;
        $this->set_response($resp, $res_code);
    }

    // User Login
    public function login_post()
    {
        //
        // 1. Get the parameters
        //
        // (type, msisdn, otp-code, platform, fcm_id)

        $post_data = ($this->post());

        $platform = $post_data['platform'];
        $fcmid = $post_data['fcm_id'];
        $location = $post_data['location'];
        // $type = $post_data['role'];

        $data = $this->post();
        $data_json = $data;
        $data = array(
            'platform' => $platform,
            'location' => $location,
            'service' => '/user/login',
            'request_method' => 'POST',
            'request_content' => serialize($data),
        );
        //
        // 2. Log the service call
        //
        $request_id = $this->User_model->insert_user_access_log_request($data);

        date_default_timezone_set('Asia/Jakarta');
        
        
            if ($this->post('login_type') == 'SSO') {

                $filterUser = array(
                    'email' => $this->post('email'),
                    // 'password' => $this->encrypt_decrypt_login('encrypt', $this->post('password')),
                );
            } else {
                $filterUser = array(
                    'email' => $this->post('email'),
                    'password' => $this->encrypt_decrypt_login('encrypt', $this->post('password')),
                );
            }
            $res = $this->User_model->login($filterUser, $platform, $fcmid);
            if ($res) {
                $res = $this->rscode->response('0000', $res);
                $session = false;
                $res_code = REST_Controller::HTTP_UNAUTHORIZED;
                $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
            } else {
                $cekuser = $this->User_model->get_user(array('*'), array("filter" => array('email' => $this->post('email'))));

                if ($cekuser) {
                    $res = $this->rscode->response('0109', null);
                } else {
                    $res = $this->rscode->response('0110', null);
                }

                $session = false;
                $res_code = REST_Controller::HTTP_UNAUTHORIZED;
                $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
            }

        //
        // Finishing and log it up
        //

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

    public function userlogout_post()
    {
        //
        // 1. Get the parameters
        //
        $post_data = ($this->post());

        $iduser = $post_data['iduser'];
        $access_token = $post_data['access_token'];
        $platform = $post_data['platform'];

        $res = $this->User_model->logout($iduser, $access_token);

        if ($res > 0) {

            $res = $this->rscode->response('0000', true);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {

            $res = $this->rscode->response('0099', false);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        }
    }

// User Cek Token Status

    public function tokenStatus_post()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->post('iduser');
        $access_token = $this->post('access_token');
        $platform = $this->post('platform');
        $location = $this->post('location');
        $data = $this->post();
        $data_json = ($data);
        //var_dump($data);
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/user/tokenstatus',
            'request_method' => 'post',
            'request_content' => serialize($this->post()),
        );

        //
        // 2. Log the service call
        //
        $request_id =
        $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res;
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        // print_r($r);
        if ($r == 0) {
            // $res = $this->rscode->response('00102', null);
            $res = $this->rscode->response('0102', false);

            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {

            $res = $this->rscode->response('0000', true);
            $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
        }
        //
        // Finishing and log it up
        //

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

// User change FCM

    public function fcmUpdate_put()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = $this->put();
        $data_json = ($data);
        //var_dump($data);
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/user/fcmUpdate',
            'request_method' => 'put',
            'request_content' => serialize($this->post()),
        );

        //
        // 2. Log the service call
        //
        $request_id =
        $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res;
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform, $data_json);
        // print_r($r);
        if ($r == 0) {
            // $res = $this->rscode->response('00102', null);
            $res = $this->rscode->response('0102', false);

            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {

            $res = $this->rscode->response('0000', true);
            $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
        }
        //
        // Finishing and log it up
        //

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

    public function upload_post()
    {
        print_r($_FILES);

        echo '-end-';
    }

    public function encrypt_decrypt($action, $string)
    {
        $output = false;
        $encrypt_method = "AES-128-CBC";
        $secret_key = 'samplish!5ucc3S5';
        // $secret_key = 'klubstory!5ucc3S5!';
        $secret_iv = 'samplishhsuccess';
        // hash
        $key = ($secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = $secret_iv;
        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = ($output);
        } else if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }

    public function encrypt_decrypt_login($action, $string)
    {
        $output = false;
        $encrypt_method = "AES-128-CBC";
        // $secret_key = 'klubstory5ucc3S5';
        $secret_key = 'samplish!5ucc3S5!';
        $secret_iv = 'samplishhsuccess';
        // hash
        $key = ($secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = $secret_iv;
        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = ($output);
        } else if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }

    public function users_put()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = $this->put();
        $data_json = ($data);
        //var_dump($data);
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/users/profile/',
            'request_method' => 'put',
            'request_content' => serialize($this->get()),
        );

        //
        // 2. Log the service call
        //
        $request_id =
        $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res = false;
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            // $res = $this->rscode->response('00102', null);
            $res = $this->rscode->response('0102', null);

            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {

            if ($this->put('password')) {

                $data_json['password'] = $this->encrypt_decrypt_login('encrypt', $data_json['password']);

            }

            // A: Active\nI: Inactive\nD: Deleted\n

            if ($this->put('status')) {
                if ($this->put('status') == 'Active') {
                    $status = 'A';
                } else if ($this->put('status') == 'Inactive') {
                    $status = 'I';
                } else if ($this->put('status') == 'Deleted') {
                    $status = 'D';
                }
                $data_json['status'] = $status;

            }

            $filter['iduser'] = $this->get('iduser');
            $res = $this->User_model->update_user($data_json, $filter);
            if ($res == 1) {
                $res = true;
            } else {
                $res = false;
            }
            $res = $this->rscode->response('0000', $res);
            $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
        }
        //
        // Finishing and log it up
        //

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

    public function recoverPassword_put()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = $this->put();
        $data_json = ($data);
        //var_dump($data);
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/users/recoverpassword/',
            'request_method' => 'put',
            'request_content' => serialize($this->get()),
        );

        //
        // 2. Log the service call
        //
        $request_id =
        $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Cek OTP and update Password
        //

        $filterOtp['filter'] = array(
            'otp_target' => $this->put('email'),
            'otp_code' => $this->put('otp_code'),
            'status' => 'S',
            'ts_expired' => date("Y-m-d H:i:s"),
        );
        //Cek OTP

        // print_r($filterOtp);

        $otp = $this->Otp_model->get_login_otp('*', $filterOtp);
        // print_r($otp);
        if ($otp) {
            $dataUpdateOtp = array(
                'status' => 'C',
            );
            $filterupdate = array('otp_target' => $data_json['email'], 'otp_code' => $this->put('otp_code'));

            // if ($updateOtp) {
            // Cek Login
            // $dataUpdate = array();
            // if ($data_json['password']) {

            $dataUpdate = array('password' => $this->encrypt_decrypt_login('encrypt', $data_json['password']));

            // }

            // $filter['iduser'] = $otp[0]['iduser'];
            $filter['email'] = $this->put('email');
            $res = $this->User_model->update_user($dataUpdate, $filter);

            // print_r($res);
            if ($res == '1') {

                // Update OTP Claimed
                $updateOtp = $this->Otp_model->update_login_otp($dataUpdateOtp, $filterupdate);

                $res = $this->rscode->response('0000', true);
                $session = false;
                $res_code = REST_Controller::HTTP_UNAUTHORIZED;
                $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
            } else if ($res == '0') {

                $res = $this->rscode->response('0111', null);
                $session = false;
                $res_code = REST_Controller::HTTP_UNAUTHORIZED;
                $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
            } else if ($res == '-1') {

                $res = $this->rscode->response('0108', null);
                $session = false;
                $res_code = REST_Controller::HTTP_UNAUTHORIZED;
                $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
            }

            // }
        } else {
            $res = $this->rscode->response('0107', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        }

        //
        // Finishing and log it up
        //

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

    public function password_put()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = $this->put();
        $data_json = $data;
        //var_dump($data);
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/password/',
            'request_method' => 'put',
            'request_content' => serialize($this->get()),
        );
        //
        // 2. Log the service call
        //
        $request_id =
        $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res;
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {

            $datapass = array(
                'password' => $this->encrypt_decrypt_login('encrypt', $data_json['newpassword']),
            );
            if ($this->get('oldpassword')) {

                $filter = array(
                    'iduser' => $this->get('iduser'),
                    'password' => $this->encrypt_decrypt_login('encrypt', $data_json['oldpassword']),
                );
            } else {

                $filter = array(
                    'iduser' => $this->get('iduser'),
                    // 'password' => $this->encrypt_decrypt_login('encrypt', $data_json['oldpassword']),
                );
            }

            $res = $this->User_model->update_user($datapass, $filter);
            if ($res == 1) {
                $res = true;
            } else {
                $res = false;
            }
            //        $data = $this->post('report_image');
            //        $data = urldecode($data);
            //        $data_json = json_decode($data, true);
            //        $status = "";

            //
            //        if ($res['errorcode'] == '00000' && $res2['errorcode'] == '00000') {
            //            $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
            //        }

            $res = $this->rscode->response('0000', $res);
            $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
        }
        //
        // Finishing and log it up
        //

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

    public function notifConfig_put()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = $this->put();
        $data_json = $data;
        //var_dump($data);
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/user/notifConfig/',
            'request_method' => 'put',
            'request_content' => serialize($this->get()),
        );
        //
        // 2. Log the service call
        //
        $request_id =
        $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res;
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {
            $filter = array(
                'iduser' => $this->get('iduser'),
            );

            $res = $this->User_model->update_userNotificationConfig($data_json, $filter);
            if ($res == 1) {
                $res = true;
            } else {
                $res = false;
            }
            //        $data = $this->post('report_image');
            //        $data = urldecode($data);
            //        $data_json = json_decode($data, true);
            //        $status = "";

            //
            //        if ($res['errorcode'] == '00000' && $res2['errorcode'] == '00000') {
            //            $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
            //        }

            $res = $this->rscode->response('0000', $res);
            $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
        }
        //
        // Finishing and log it up
        //

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

    public function completeprofile_put()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = $this->put();
        $data_json = ($data);
        //var_dump($data);
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/users/completeprofile/',
            'request_method' => 'put',
            'request_content' => serialize($this->get()),
        );

        //
        // 2. Log the service call
        //
        $request_id =
        $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res;
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            // $res = $this->rscode->response('00102', null);
            $res = $this->rscode->response('0102', null);

            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {
            if ($this->put('email')) {
                $otp_target = $this->put('email');
                $type = 'E';
            } else if ($this->put('msisdn')) {
                $otp_target = $this->put('msisdn');
                $type = 'WA';
            }

            $filterOtp['filter'] = array(
                // 'otp_target' => $otp_target,
                'otp_code' => $this->put('otp_code'),
                'status' => 'S',
                // 'type' => $type,
                // 'ts_expired' => date("Y-m-d H:i:s"),
            );
            //Cek OTP

            // print_r($filterOtp);

            $otp = $this->Otp_model->get_login_otp('*', $filterOtp);
            // print_r($otp);
            if ($otp) {
                $dataUpdateOtp = array(
                    'status' => 'C',
                );
                $filterupdate = array(
                    // 'otp_target' => $data_json['email'],
                    'otp_code' => $this->put('otp_code'));

                // Update OTP Claimed
                $updateOtp = $this->Otp_model->update_login_otp($dataUpdateOtp, $filterupdate);
                if ($updateOtp) {
                    if ($this->put('email')) {
                        $updateData = array(
                            'email' => $data_json['email'],
                        );
                    }
                    if ($this->put('password')) {

                        $updateData['password'] = $this->encrypt_decrypt_login('encrypt', $data_json['password']);

                    }
                    if ($this->put('msisdn')) {

                        $updateData['msisdn'] = $data_json['msisdn'];

                    }

                    $filter['iduser'] = $this->get('iduser');
                    $res = $this->User_model->update_user($updateData, $filter);

                    if ($res > 0) {
                        $res = true;
                    } else {
                        $res = false;
                    }
                    $res = $this->rscode->response('0000', $res);
                    $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
                }
            } else {

                $res = $this->rscode->response('0107', null);
                $session = false;
                $res_code = REST_Controller::HTTP_UNAUTHORIZED;
                $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
            }

        }
        //
        // Finishing and log it up
        //

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

    public function profile_put()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = $this->put();
        $data_json = $data;
        //var_dump($data);
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/password/',
            'request_method' => 'put',
            'request_content' => serialize($this->get()),
        );
        //
        // 2. Log the service call
        //
        $request_id =
        $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res;
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {

            $datapass = array(
                'password' => strtolower(md5($data_json['newpassword'])),
            );
            $filter['filter'] = array(
                'iduser' => $this->get('iduser'),
                'password' => strtolower(md5($data_json['oldpassword'])),
            );

            $res = $this->User_model->update_user($datapass, $filter);

            //        $data = $this->post('report_image');
            //        $data = urldecode($data);
            //        $data_json = json_decode($data, true);
            //        $status = "";

            //
            //        if ($res['errorcode'] == '00000' && $res2['errorcode'] == '00000') {
            //            $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
            //        }

            $res = $this->rscode->response('0000', $res);
            $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
        }
        //
        // Finishing and log it up
        //

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

    public function grantStatusCreateClub_put()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = $this->put();
        $data_json = $data;
        //var_dump($data);
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => 'user/grantStatusCreateClub/',
            'request_method' => 'put',
            'request_content' => serialize($this->get()),
        );
        //
        // 2. Log the service call
        //
        $request_id =
        $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res;
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else { $col = array('*');

            $filter = [];
            $filter['filter'] = $this->get();
            foreach ($filter['filter'] as $key => $value) {
                if ($key == 'access_token') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'platform') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'location') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'n_item') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'page') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'sort_by') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'fullname') {
                    if (!empty($filter['filter']['fullname'])) {
                        unset($filter['filter']['iduser']);
                    } else {
                        unset($filter['filter']['iduser']);
                        $filter['filter']['fullname'] = 'xxx999';
                    }
                    // unset($filter['filter']['iduser']);
                } else if ($key == 'username') {
                    if (!empty($filter['filter']['username'])) {
                        unset($filter['filter']['iduser']);
                    } else {
                        unset($filter['filter']['iduser']);
                        $filter['filter']['username'] = 'xxx999';
                    }

                } else if ($value == 'null') {
                    $filter['filter'][$key] = null;
                }
            }

            $data = $this->User_model->get_user($col, $filter);
            if ($data[0]['status_create_club'] == 1 && $data[0]['token_create_club'] > 0) {

                $datapass = array(
                    'status_create_club' => 1,
                    'token_create_club' => 7,
                );
                $filtergrant = array(
                    'iduser' => $this->put('iduser_grant'),
                );

                $resgrant = $this->User_model->update_user($datapass, $filtergrant);

                $datapassUser = array(
                    // 'status_create_club' => 1,
                    'token_create_club' => ((int) $data[0]['token_create_club'] - 1),
                );
                $filteruser = array(
                    'iduser' => $this->get('iduser'),
                );

                $resgrant = $this->User_model->update_user($datapassUser, $filteruser);

                $res = $this->rscode->response('0000', true);

                // Notification user connect request
                // 1. get POST

                $gcm = new GCM();
                $modul = 'USER';
                $type = 'USER_GRANT_CREATE_KLUB';
                $body = 'User Grant Create Klub';

                // 2. cek fcmid and send notif

                $fcmMember = array();
                $fcmMember[] = $this->put('iduser_grant');
                $fcmMember[] = $iduser;

                // 3. insert notif

                foreach ($fcmMember as $vk) {
                    $postNotif = array(
                        'modul' => $modul,
                        'topic' => $vk,
                        'type' => $type,
                        'iduser' => $vk,
                        'is_viewed' => 'N',
                        'is_action' => 'N',
                        'text' => 'User Grant Create Klub',
                        // 'confirm_status' => $data_json['status'],
                        'related_id' => $iduser . ',' . $this->put('iduser_grant'),
                        'related_key' => 'iduser_sender',
                    );
                    $insertnotification = $gcm->notificationPost($postNotif);
                    $postNotif['idnotification'] = $insertnotification;

                    // 4. cek summary notif user, insert or update it
                    $filterGetNotif = array('filter' => array(
                        'iduser' => $vk,
                    ));
                    $notificationuserget = $gcm->notificationUserget(array('*'), $filterGetNotif);

                    if ($notificationuserget) {
                        if ($modul == 'POST') {
                            $dataNotifUserUpdate['post'] = (int) $notificationuserget[0]['post'] + 1;
                        } elseif ($modul == 'EVENT') {
                            $dataNotifUserUpdate['event'] = (int) $notificationuserget[0]['event'] + 1;
                        } elseif ($modul == 'POLL') {
                            $dataNotifUserUpdate['poll'] = (int) $notificationuserget[0]['poll'] + 1;
                        } elseif ($modul == 'MARKET') {
                            $dataNotifUserUpdate['market'] = (int) $notificationuserget[0]['market'] + 1;
                        } elseif ($modul == 'KLUB') {
                            $dataNotifUserUpdate['klub'] = (int) $notificationuserget[0]['klub'] + 1;
                        }

                        // $gcm->notificationuserupdate($dataNotifUserUpdate, array('idnotification_user' => $notificationuserget[0]['idnotification_user']));

                        // counter Bell

                        $dataNotifUserBellUpdate['bell'] = (int) $notificationuserget[0]['bell'] + 1;
                        $gcm->notificationuserupdate($dataNotifUserBellUpdate, array('idnotification_user' => $notificationuserget[0]['idnotification_user']));

                    } else {
                        $postInsertnotifUser = array(
                            'iduser' => $vk,
                        );

                        if ($modul == 'POST') {
                            $postInsertnotifUser['post'] = 1;
                        } elseif ($modul == 'EVENT') {
                            $postInsertnotifUser['event'] = 1;
                        } elseif ($modul == 'POLL') {
                            $postInsertnotifUser['poll'] = 1;
                        } elseif ($modul == 'MARKET') {
                            $postInsertnotifUser['market'] = 1;
                        } elseif ($modul == 'KLUB') {
                            $postInsertnotifUser['klub'] = 1;
                        }
                        // counter Bell
                        $postInsertnotifUser['bell'] = 1;

                        $gcm->notificationUserPost($postInsertnotifUser);
                    }
                }

                $filter['filter'] = array('status' => 0, 'fcm_id !=' => '');
                $filter['filterIn'] = array('iduser' => $fcmMember);

                $datafcmid = $this->User_model->get_agent_login_session('fcm_id', $filter);

                $fcmid = array();
                if ($datafcmid) {

                    // 5. get user info
                    // $dataklub = $this->Klub_model->get_klub($col, array('filter' => array('idklub' => $klub)));
                    $cekuser = $this->User_model->get_user(array('*'), array("filter" => array('iduser' => $iduser)));
                    $grantsuser = $this->User_model->get_user(array('*'), array("filter" => array('iduser' => $this->put('iduser_grant'))));
                    // 6. send notif and Update user summary/counter

                    foreach ($datafcmid as $c) {

                        array_push($fcmid, $c['fcm_id']);
                    }
                    $msgNotif = array("notification" => array("body" => $grantsuser[0]['fullname'] . ' has been granted access to create klub Appsite by' . $cekuser[0]['fullname'], "title" => $body, "click_action" => "FLUTTER_NOTIFICATION_CLICK"));
                    // $msgNotif = '';
                    $resultFCM = $gcm->send_notification($fcmid, $msgNotif, array('modul' => $modul, 'type' => $type, 'iduser_grant' => $this->put('iduser_grant')));
                    // print_r($resultFCM);
                }

            } else {
                $res = $this->rscode->response('0006', false);
            }

            //        $data = $this->post('report_image');
            //        $data = urldecode($data);
            //        $data_json = json_decode($data, true);
            //        $status = "";

            //
            //        if ($res['errorcode'] == '00000' && $res2['errorcode'] == '00000') {
            //            $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
            //        }

            $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
        }
        //
        // Finishing and log it up
        //

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

    public function usersimage_post()
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
        $data_json = ($data);
        //var_dump($data);
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/users/',
            'request_method' => 'put',
            'request_content' => serialize($this->get()),
        );

        //
        // 2. Log the service call
        //
        $request_id =
        $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res;
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {

            $target_dir = "assets/images/user/";
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            // Check if image file is a actual image or fake image
            // if(isset($_POST["submit"])) {
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check !== false) {
                $uploadOk = 1;
            } else {
                $uploadOk = 0;
            }

            if (file_exists($target_file)) {
                $uploadOk = 0;
            }

            if ($uploadOk == 0) {
                // if everything is ok, try to upload file
            } else {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {

                } else {

                }
            }

            $datapass = array(
                'user_image' => $target_file,
            );
            $filter['filter'] = array(
                'iduser' => $this->get('iduser'),
            );

            $res = $this->User_model->update_user($datapass, $filter);

            $res = $this->rscode->response('0000', $res);
            $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
        }
        //
        // Finishing and log it up
        //

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

    public function register()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $data_json = $this->request->getPost();
        

        //
        // 2. Log the service call
        //
        
        //
        // 3. Check msisdn
        //
        $res_code;
        $res;
        if ($data_json['register_type'] == 'NONSSO') {
            $filter['filter']['email'] = $data_json['email'];
        } elseif ($data_json['register_type'] == 'SSO') {
            $filter['filter']['email'] = $data_json['email'];
        }

        $is_exists = $this->User_model->get_user('*', $filter);

        // $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($is_exists) {
            
            $res = tempResponse('00103', null);
            
            $session = false;
            return $this->respond($res); // CREATED (201) being the HTTP response code
        } else {
            if (!isset($data_json['register_type']) || $data_json['register_type'] == 'NONSSO') {
                // 4. check otp-code /otp/signup/check/
                
                    // 5. insert data user
                    $data = array(
                        'username' => $this->get_rand_alphanumeric(7),
                        'fullname' => $data_json['fullname'],
                        'email' => $data_json['email'],
                        'password' => $this->encrypt_decrypt_login('encrypt', $data_json['password']),
                        'register_type' => 'NONSSO',
                        'pic_profile_url' => '',
                    );
                    $iduser = $this->User_model->insert_user($data);

                    // Generate QR
                    // $dataUpdate = array('qrcode_path' => $this->qrcodeGenerate->generateQr('profile', $iduser));

                    // $filterUpdate['iduser'] = $iduser;
                    // $updateUser = $this->User_model->update_user($dataUpdate, $filterUpdate);

                    // create_session($userid, $platform, $fcmid)   6. post fcm
                    if ($iduser) {
                        $access_token = $this->AuthModel->create_session($iduser, $data_json['platform'], $data_json['fcm_id'], $data_json['role']);

                        if($data_json['role'] == 'company'){
                        
                            $dataUser['email'] = $data_json['email'];
                            $dataUser['name'] = $data_json['fullname'];
                            $updateUser = $this->User_model->insert_user_company($dataUser);
    
                        }elseif($data_json['role'] == 'sampler'){
    
                            $dataUser['name'] = $data_json['fullname'];
                            $dataUser['email'] = $data_json['email'];
                            $updateUser = $this->User_model->insert_user_sampler($dataUser);
    
                        }
    
                        // update related id dan key
    
                        $filterUpdate['iduser'] = $iduser;
                        $dataUpdate = array(
                            'related_key'=> $data_json['role'],
                            'related_id' => $updateUser
                        );
                        $updateUser = $this->User_model->update_user($dataUpdate, $filterUpdate);
                        if (!file_exists('./assets/' . $iduser)) {
                            mkdir('./assets/' . $iduser, 0777, true);
                        }
                        
                    }

                    if ($access_token) {

                        $filter['filter']['iduser'] = $iduser;
                        $is_exists = $this->User_model->get_user('*', $filter);

                        $data = array(
                            'access_token' => $access_token,
                            'user_profile' => $is_exists,
                            'profile_status' => 'notComplete',
                        );
                        
                        $res = tempResponse('00000', $data);
                        return $this->respond($res);
                    } else {                    
                        $res = tempResponse('00002', null);
                        
                        $session = false;
                        return $this->respond($res);
                    }

            } elseif ($data_json['register_type'] == 'SSO') {

                // 5. insert data user
                $data = array(
                    'username' => $this->get_rand_alphanumeric(7),
                    'email' => $data_json['email'],
                    'fullname' => $data_json['fullname'],
                    'register_type' => $data_json['register_type'],
                    'pic_profile_url' => '',
                );
                $iduser = $this->User_model->insert_user($data);

                // Generate QR
                // $dataUpdate = array('qrcode_path' => $this->qrcodeGenerate->generateQr('profile', $iduser));

                // $filterUpdate['iduser'] = $iduser;
                // $updateUser = $this->User_model->update_user($dataUpdate, $filterUpdate);

                // create_session($userid, $platform, $fcmid)   6. post fcm
                if ($iduser) {
                    $access_token = $this->AuthModel->create_session($iduser, $data_json['platform'], $data_json['fcm_id'], $data_json['role']);

                    if($data_json['role'] == 'company'){
                        
                        $dataUser['email'] = $data_json['email'];
                        $dataUser['name'] = $data_json['fullname'];
                        $updateUser = $this->User_model->insert_user_company($dataUser);

                    }elseif($data_json['role'] == 'sampler'){

                        $dataUser['name'] = $data_json['fullname'];
                        $dataUser['email'] = $data_json['email'];
                        $updateUser = $this->User_model->insert_user_sampler($dataUser);

                    }

                    // update related id dan key

                    $filterUpdate['iduser'] = $iduser;
                    $dataUpdate = array(
                        'related_key'=> $data_json['role'],
                        'related_id' => $updateUser
                    );
                    $updateUser = $this->User_model->update_user($dataUpdate, $filterUpdate);

                    if (!file_exists('./assets/' . $iduser)) {
                        mkdir('./assets/' . $iduser, 0777, true);
                    }
                }

                if ($access_token) {

                    $filter['filter']['iduser'] = $iduser;
                    $is_exists = $this->User_model->get_user('*', $filter);

                    $data = array(
                        'access_token' => $access_token,
                        'user_profile' => $is_exists,
                        'profile_status' => 'notComplete'
                    );
                    
                    $res = tempResponse('00000', $data);
                    return $this->respond($res);
                } else {                    
                    $res = tempResponse('00002', null);
                    
                    $session = false;
                    return $this->respond($res);
                }
            }
        }
        //
        // Finishing and log it up

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
    /*

    // GET Function

     */

    public function session_get()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/session/',
            'request_method' => 'GET',
            'request_content' => serialize($this->get()),
        );
        //
        // 2. Log the service call
        //
        $request_id =
        $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res;
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        }

        //
        // 4. Do the things..
        //
        if ($session === true) {
            $col = array('*');

            $filter = [];
            $filter['filter'] = $this->get();
            foreach ($filter['filter'] as $key => $value) {
                if ($key == 'access_token') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'platform') {
                    // unset($filter['filter'][$key]);
                } else if ($key == 'location') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'n_item') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'page') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'iduser') {
                    // ($filter['filter'][$key]);
                } else if ($key == 'sort_by') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'group') {
                    unset($filter['filter'][$key]);
                } else if ($value == 'null') {
                    $filter['filter'][$key] = null;
                }
            }

            // Limit

            $filter['limit'] = [];
            $filter['limit']['n_item'] = $this->get('n_item');
            $filter['limit']['page'] = $this->get('page');

            // Sort By
            $sortby = $this->get('sort_by');
            if (isset($sortby)) {
                $sort_by = explode(';', $sortby);
                for ($i = 0; $i < count($sort_by); $i += 2) {
                    $key = $sort_by[$i];
                    $sort = $sort_by[$i + 1];
                    $filter['sort'][$key] = $sort;
                }
            }

            // Group
            $filter['group'] = $this->get('group');

            $data = $this->User_model->get_user_access_login_session($col, $filter);

            if ($data != null) {
                $res_code = REST_Controller::HTTP_OK;
                $res = $this->rscode->response('0000', $data);
                $this->set_response($res, $res_code);
            } else {
                $res_code = REST_Controller::HTTP_OK;
                $res = $this->rscode->response('0001', $data);
                $this->set_response($res, $res_code);
            }
        }

        //
        // Finishing and log it up
        //

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

    public function users_get()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/user/users/',
            'request_method' => 'GET',
            'request_content' => serialize($this->get()),
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
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        // print_r($r);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        }

        //
        // 4. Do the things..
        //
        if ($session === true) {
            $col = array('*');

            $filter = [];
            $filter['filter'] = $this->get();
            foreach ($filter['filter'] as $key => $value) {
                if ($key == 'access_token') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'platform') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'location') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'n_item') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'page') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'sort_by') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'fullname') {
                    if (!empty($filter['filter']['fullname'])) {
                        unset($filter['filter']['iduser']);
                    } else {
                        unset($filter['filter']['iduser']);
                        $filter['filter']['fullname'] = 'xxx999';
                    }
                    // unset($filter['filter']['iduser']);
                } else if ($key == 'username') {
                    if (!empty($filter['filter']['username'])) {
                        unset($filter['filter']['iduser']);
                    } else {
                        unset($filter['filter']['iduser']);
                        $filter['filter']['username'] = 'xxx999';
                    }

                } else if ($value == 'null') {
                    $filter['filter'][$key] = null;
                }
            }

            if ($this->get('iduser_friend')) {
                if (!empty($this->get('iduser_friend'))) {
                    $filter['filter']['iduser'] = $this->get('iduser_friend');
                    unset($filter['filter']['iduser_friend']);
                }
            }

            if ($iduser != '1036') {
                $filter['filternot'] = array('iduser !=' => '1036');
            }

            // print_r($_SESSION);

            $data = $this->User_model->get_user($col, $filter);

            $filterconn = array();
            $filterconn['filter']['user_connection.iduser'] = $iduser;
            $dataConn = $this->User_model->get_userconnection($col, $filterconn);

            $colcountry = array('idmd_country', 'name');
            $colcity = array('idmd_city', 'name');
            $colstate = array('idmd_state', 'name');
            $coldistrict = array('idmd_district', 'district_name');
            $colsubdistrict = array('idmd_subdistrict', 'subdistrict_name');
            if ($data) {
                foreach ($data as $k => $v) {

                    if ($v['status_create_club'] == '1' || $v['status_create_club'] == 1) {
                        $data[$k]['status_create_club'] = true;
                    } else {
                        $data[$k]['status_create_club'] = false;
                    }

                    $country = $this->Md_model->get_country($colcountry, array('filter' => array('idmd_country' => $v['idmd_country'])));
                    if ($country) {
                        $data[$k]['idmd_country'] = $country[0]['idmd_country'];
                        $data[$k]['country_name'] = $country[0]['name'];
                    }

                    $state = $this->Md_model->get_state($colstate, array('filter' => array('idmd_state' => $v['idmd_state'])));
                    if ($state) {
                        $data[$k]['idmd_state'] = $state[0]['idmd_state'];
                        $data[$k]['state_name'] = $state[0]['name'];
                    }

                    $city = $this->Md_model->get_city($colcity, array('filter' => array('idmd_city' => $v['idmd_city'])));
                    if ($city) {
                        $data[$k]['idmd_city'] = $city[0]['idmd_city'];
                        $data[$k]['city_name'] = $city[0]['name'];
                    }

                    $district = $this->Md_model->get_district($coldistrict, array('filter' => array('idmd_district' => $v['idmd_district'])));
                    if ($district) {
                        $data[$k]['idmd_district'] = $district[0]['idmd_district'];
                        $data[$k]['district_name'] = $district[0]['district_name'];
                    }

                    $subdistrict = $this->Md_model->get_subdistrict($colsubdistrict, array('filter' => array('idmd_subdistrict' => $v['idmd_subdistrict'])));
                    if ($subdistrict) {
                        $data[$k]['idmd_subdistrict'] = $subdistrict[0]['idmd_subdistrict'];
                        $data[$k]['subdistrict_name'] = $subdistrict[0]['subdistrict_name'];
                    }

                    if ($this->get('iduser_friend')) {
                        $filterfriend = array();
                        $filterfriend['filter']['user_connection.iduser'] = $this->get('iduser');
                        $filterfriend['filter']['user_connection.iduser_friend'] = $this->get('iduser_friend');
                        $friend = $this->User_model->get_userconnection($col, $filterfriend);
                        if ($friend) {

                            // $filterreport = array();
                            // $filterreport['filter']['user_connection.iduser'] = $this->get('iduser');
                            // $filterreport['filter']['user_connection.iduser_report'] = $this->get('iduser_friend');
                            // $report = $this->User_model->get_userconnection($col, $filterreport);
                            // if ($report) {

                            // }else{

                            // }
                            $data[$k]['action'][] = 'UNCONNECT';
                            $data[$k]['action'][] = 'BLOCK';
                            $data[$k]['action'][] = 'REPORT';
                        } else {
                            $data[$k]['action'] = null;
                        }
                    }

                    $data[$k]['status'] = '';
                    if ($this->get('iduser_friend') == $this->get('iduser')) {
                        $data[$k]['status'] = 'CONNECTED';
                    } else {
                        if ($dataConn) {
                            foreach ($dataConn as $kc => $vc) {
                                if ($vc['iduser'] == $v['iduser']) {

                                    $data[$k]['status'] = 'CONNECTED';
                                } else {
                                    // print_r($vc['iduser']);
                                    $userRequest = $this->User_model->get_userconnectionrequest($col, array('filter' => array('user_connection_request.iduser_requester' => $iduser, 'user_connection_request.iduser' => $v['iduser'], 'request_status' => 'NEW')));
                                    if ($userRequest) {
                                        $data[$k]['status'] = 'WAIT';
                                    } else {
                                        if ($data[$k]['status'] == '') {
                                            $data[$k]['status'] = 'UNCONNECTED';
                                        }

                                    }
                                }
                            }
                        } else {

                            $userRequest = $this->User_model->get_userconnectionrequest($col, array('filter' => array('user_connection_request.iduser_requester' => $iduser, 'user_connection_request.iduser' => $v['iduser'], 'request_status' => 'NEW')));
                            if ($userRequest) {
                                $data[$k]['status'] = 'WAIT';
                            } else {
                                if ($data[$k]['status'] == '') {
                                    $data[$k]['status'] = 'UNCONNECTED';
                                }
                            }
                        }
                    }

                    $notifConfig = $this->Notif_model->get_userNotificationConfig($col, array('filter' => array('iduser' => $v['iduser'])));
                    if ($notifConfig) {
                        $data[$k]['notif_config'] = $notifConfig[0];
                    } else {
                        $data[$k]['notif_config'] = null;
                    }
                }
            } else {
                $data = null;
            }

            if ($data != null) {
                $res_code = REST_Controller::HTTP_OK;
                $res = $this->rscode->response('0000', $data);
                $this->set_response($res, $res_code);
            } else {
                $res_code = REST_Controller::HTTP_OK;
                $res = $this->rscode->response('0001', $data);
                $this->set_response($res, $res_code);
            }
        }

        //
        // Finishing and log it up
        //

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

    public function connectionlist_get()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/users connection list/',
            'request_method' => 'GET',
            'request_content' => serialize($this->get()),
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
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        }

        //
        // 4. Do the things..
        //
        if ($session === true) {
            $col = array('user.iduser', 'user.fullname', 'user.pic_profile_url', 'user.idmd_country', 'user.idmd_city');

            $filter = [];
            $filter['filter'] = $this->get();
            foreach ($filter['filter'] as $key => $value) {
                if ($key == 'access_token') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'platform') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'location') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'n_item') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'page') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'sort_by') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'iduser') {
                    $filter['filter']['user_connection.iduser'] = $value;
                    unset($filter['filter'][$key]);
                } else if ($key == 'name') {
                    if (!empty($value)) {
                        $filter['filter']['user.fullname'] = $value;
                    }
                    unset($filter['filter'][$key]);
                } else if ($key == 'id') {
                    $filter['filter']['user.iduser'] = $value;
                    unset($filter['filter'][$key]);
                } else if ($value == 'null') {
                    $filter['filter'][$key] = null;
                }
            }

            $block = $this->User_model->get_userblock(array('iduser_block as iduser'), array('filter' => array('user_block_user.iduser' => $iduser)));
            if ($block) {
                $user_block = array();
                foreach ($block as $b) {
                    $user_block[] = $b['iduser'];
                }
                $filter['filternotin']['iduser_friend'] = $user_block;
            }

            $filter['limit']['page'] = $this->get('page') - 1;
            $filter['limit']['n_item'] = $this->get('n_item');
            $filter['filter']['user.status'] = 'A';

            $filter['sort'] = array(
                'user.fullname' => 'ASC',
            );
            $data = $this->User_model->get_userconnection($col, $filter);
            if ($data) {

                $colcountry = array('idmd_country', 'name');
                $colcity = array('idmd_city', 'name');
                foreach ($data as $k => $v) {
                    $country = $this->Md_model->get_country($colcountry, array('filter' => array('idmd_country' => $v['idmd_country'])));
                    if ($country) {
                        $data[$k]['country'] = $country[0];
                    }
                    $city = $this->Md_model->get_city($colcity, array('filter' => array('idmd_city' => $v['idmd_city'])));
                    if ($city) {
                        $data[$k]['city'] = $city[0];
                    }
                }
            }
            // print_r($data);
            if ($data != null) {
                $res_code = REST_Controller::HTTP_OK;
                $res = $this->rscode->response('0000', $data);
                $this->set_response($res, $res_code);
            } else {
                $res_code = REST_Controller::HTTP_OK;
                $res = $this->rscode->response('0001', $data);
                $this->set_response($res, $res_code);
            }
        }

        //
        // Finishing and log it up
        //

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

    public function blocklist_get()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/user/blocklist/',
            'request_method' => 'GET',
            'request_content' => serialize($this->get()),
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
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        }

        //
        // 4. Do the things..
        //
        if ($session === true) {
            $col = array('user.iduser', 'user.fullname', 'user.pic_profile_url', 'user.idmd_country', 'user.idmd_city');

            $filter = [];
            $filter['filter'] = $this->get();
            foreach ($filter['filter'] as $key => $value) {
                if ($key == 'access_token') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'platform') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'location') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'n_item') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'page') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'sort_by') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'iduser') {
                    $filter['filter']['user_block_user.iduser'] = $value;
                    unset($filter['filter'][$key]);
                } else if ($key == 'name') {
                    if (!empty($value)) {
                        $filter['filter']['user.fullname'] = $value;
                    }
                    unset($filter['filter'][$key]);
                } else if ($key == 'id') {
                    $filter['filter']['user.iduser'] = $value;
                    unset($filter['filter'][$key]);
                } else if ($value == 'null') {
                    $filter['filter'][$key] = null;
                }
            }

            $filter['limit']['page'] = $this->get('page') - 1;
            $filter['limit']['n_item'] = $this->get('n_item');

            $filter['sort'] = array(
                'user.fullname' => 'ASC',
            );
            // print_r($filter);
            $data = $this->User_model->get_userblock($col, $filter);
            if ($data) {

                $colcountry = array('idmd_country', 'name');
                $colcity = array('idmd_city', 'name');
                foreach ($data as $k => $v) {
                    $country = $this->Md_model->get_country($colcountry, array('filter' => array('idmd_country' => $v['idmd_country'])));
                    if ($country) {
                        $data[$k]['country'] = $country[0];
                    }
                    $city = $this->Md_model->get_city($colcity, array('filter' => array('idmd_city' => $v['idmd_city'])));
                    if ($city) {
                        $data[$k]['city'] = $city[0];
                    }
                }
            }
            // print_r($data);
            if ($data != null) {
                $res_code = REST_Controller::HTTP_OK;
                $res = $this->rscode->response('0000', $data);
                $this->set_response($res, $res_code);
            } else {
                $res_code = REST_Controller::HTTP_OK;
                $res = $this->rscode->response('0001', $data);
                $this->set_response($res, $res_code);
            }
        }

        //
        // Finishing and log it up
        //

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

    public function connectionrequest_get()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/users connection list/',
            'request_method' => 'GET',
            'request_content' => serialize($this->get()),
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
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        }

        //
        // 4. Do the things..
        //
        if ($session === true) {
            $col = array('iduser_connection_request', 'user.iduser', 'user.fullname', 'user.pic_profile_url', 'user.idmd_country', 'user.idmd_city', 'request_status');

            $filter = [];
            $filter['filter'] = $this->get();
            foreach ($filter['filter'] as $key => $value) {
                if ($key == 'access_token') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'platform') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'location') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'n_item') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'page') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'sort_by') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'iduser') {
                    $filter['filter']['user_connection_request.iduser'] = $value;
                    unset($filter['filter'][$key]);
                } else if ($key == 'name') {
                    if (!empty($value)) {
                        $filter['filter']['user.fullname'] = $value;
                    }
                    unset($filter['filter'][$key]);
                } else if ($key == 'id') {
                    $filter['filter']['user.iduser_requester'] = $value;
                    unset($filter['filter'][$key]);
                } else if ($value == 'null') {
                    $filter['filter'][$key] = null;
                }
            }
            $filter['limit']['page'] = $this->get('page') - 1;
            $filter['limit']['n_item'] = $this->get('n_item');

            $filter['sort'] = array(
                'user.fullname' => 'ASC',
            );
            $filter['filter']['request_status'] = 'NEW';
            $filter['filter']['user.status'] = 'A';

            $block = $this->User_model->get_userblock(array('iduser_block as iduser'), array('filter' => array('user_block_user.iduser' => $iduser)));
            if ($block) {
                $user_block = array();
                foreach ($block as $b) {
                    $user_block[] = $b['iduser'];
                }
                $filter['filternotin']['iduser_requester'] = $user_block;
            }

            // print_r($filter);
            $dataRes = array(
                'personal_request' => null,
                'klub_request' => null,
            );

            $userRequest = $this->User_model->get_userconnectionrequest($col, $filter);
            if ($userRequest) {

                $colcountry = array('idmd_country', 'name');
                $colcity = array('idmd_city', 'name');
                foreach ($userRequest as $k => $v) {
                    $dataRes['personal_request'][$k] = $v;
                    $dataRes['personal_request'][$k]['mutual_klub'] = null;
                    $country = $this->Md_model->get_country($colcountry, array('filter' => array('idmd_country' => $v['idmd_country'])));
                    if ($country) {
                        $dataRes['personal_request'][$k]['country'] = $country[0];
                    }
                    $city = $this->Md_model->get_city($colcity, array('filter' => array('idmd_city' => $v['idmd_city'])));
                    if ($city) {
                        $dataRes['personal_request'][$k]['city'] = $city[0];
                    }

                    $colmember = array('idklub');
                    $filtermember['filter']['klub_member.iduser'] = $iduser;
                    $klubmember = $this->Klub_model->get_klubmember($colmember, $filtermember);
                    foreach ($klubmember as $vkm) {
                        $filtermemberInvitor['filter']['klub_member.idklub'] = $vkm['idklub'];
                        $filtermemberInvitor['filter']['klub_member.iduser'] = $v['iduser'];
                        $klubmemberInvitor = $this->Klub_model->get_klubmember($colmember, $filtermemberInvitor);
                        if ($klubmemberInvitor) {

                            foreach ($klubmemberInvitor as $vki) {

                                $filterklub['filter']['klub.idklub'] = $vki['idklub'];
                                $mutualklub = $this->Klub_model->get_klub_list('*', $filterklub);
                                if ($mutualklub) {
                                    $dataRes['personal_request'][$k]['mutual_klub'][] = $mutualklub[0];
                                }
                            }
                        }
                    }
                }
            }

            $colismember = array('*');
            // $filterjoinReq['filter']['idklub'] = $data['idklub'];
            $filterjoinReq['filter']['id_user'] = $iduser;
            $filterjoinReq['sort']['idklub_join_invitation'] = 'DESC';
            $isjoinReq = $this->Klub_model->get_klubjoinInvitation($colismember, $filterjoinReq);
            if ($isjoinReq) {
                foreach ($isjoinReq as $kr => $vr) {
                    $data['idklub_join_invitation'] = $vr['idklub_join_invitation'];

                    if ($vr['status'] == 'N') {
                        $vr['status'] = 'NEW';
                    } else if ($vr['status'] == 'A') {
                        $vr['status'] = 'APPROVE';
                    } else {
                        $vr['status'] = 'REJECT';
                    }
                    $dataRes['klub_request'][$kr] = $vr;
                    // $dataRes['klub_request'][$kr]['joinstatus'] = 'NEW, waiting for approval';

                }
            }

            // print_r($data);
            if ($dataRes != null) {
                $res_code = REST_Controller::HTTP_OK;
                $res = $this->rscode->response('0000', $dataRes);
                $this->set_response($res, $res_code);
            } else {
                $res_code = REST_Controller::HTTP_OK;
                $res = $this->rscode->response('0001', $dataRes);
                $this->set_response($res, $res_code);
            }
        }

        //
        // Finishing and log it up
        //

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

    public function userslist_get()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/userslist/',
            'request_method' => 'GET',
            'request_content' => serialize($this->get()),
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
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        }

        //
        // 4. Do the things..
        //
        if ($session === true) {
            $col = array('user.iduser', 'user.fullname', 'user.pic_profile_url', 'user.username', 'user.status_create_club', 'user.token_create_club');

            $filter = [];
            $filter['filter'] = $this->get();
            foreach ($filter['filter'] as $key => $value) {
                if ($key == 'access_token') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'platform') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'location') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'n_item') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'page') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'sort_by') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'iduser') {
                    $filter['filter']['user_connection.iduser'] = $value;
                    unset($filter['filter'][$key]);
                } else if ($key == 'name') {
                    // $filter['filter']['user.fullname'] = $value;
                    unset($filter['filter'][$key]);
                } else if ($value == 'null') {
                    $filter['filter'][$key] = null;
                }
            }
            // $filter['limit']['page'] = $this->get('page') - 1;
            // $filter['limit']['n_item'] = $this->get('n_item');

            $filter['sort'] = array(
                'user.fullname' => 'ASC',
            );

            $filterUser['limit']['page'] = $this->get('page') - 1;
            $filterUser['limit']['n_item'] = $this->get('n_item');

            $filterUser['sort'] = array(
                'user.fullname' => 'ASC',
            );
            $filterUser['sort'] = array(
                'user.fullname' => 'ASC',
            );
            if ($this->get('name')) {
                if (!empty($this->get('name'))) {
                    $filterUser['filterLike']['user.fullname'] = $this->get('name');
                }

                // unset($filter['filter'][$key]);
            }

            if ($iduser != '1036') {
                $filterUser['filternot'] = array('iduser !=' => '1036');
            }

            $data = $this->User_model->get_user($col, $filterUser);
            // print_r($data);
            $dataConn = $this->User_model->get_userconnection($col, $filter);
            // print_r($dataConn);
            $res = null;
            if ($data) {
                foreach ($data as $k => $v) {
                    // $insertMember = $this->Notif_model->insert_userNotificationConfig(array('iduser' => $v['iduser'], 'post' => 'YES', 'event' => 'YES', 'poll' => 'YES', 'market' => 'YES'));
                    for ($i = 164; $i <= 193; $i++) {
                        $r = $this->Klub_model->get_klubmember(array('role'), array('filter' => array('klub_member.idklub' => $i, 'klub_member.iduser' => $v['iduser'])));
                        if (!$r) {
                            $insertMember = $this->Klub_model->insert_klubMember(array('idklub' => $i, 'iduser' => $v['iduser'], 'role' => 'MEM', 'status' => 'A'));
                        }
                    }

                    $data[$k]['status'] = '';
                    if ($dataConn) {
                        foreach ($dataConn as $kc => $vc) {
                            if ($vc['iduser'] == $v['iduser']) {

                                $data[$k]['status'] = 'CONNECTED';
                            } else {
                                // print_r($vc['iduser']);
                                $userRequest = $this->User_model->get_userconnectionrequest($col, array('filter' => array('user_connection_request.iduser_requester' => $iduser, 'user_connection_request.iduser' => $v['iduser'], 'request_status' => 'NEW')));
                                if ($userRequest) {
                                    $data[$k]['status'] = 'WAIT';
                                } else {
                                    if ($data[$k]['status'] == '') {
                                        $data[$k]['status'] = 'UNCONNECTED';
                                    }

                                }
                            }
                        }
                    } else {

                        $userRequest = $this->User_model->get_userconnectionrequest($col, array('filter' => array('user_connection_request.iduser_requester' => $iduser, 'user_connection_request.iduser' => $v['iduser'], 'request_status' => 'NEW')));
                        if ($userRequest) {
                            $data[$k]['status'] = 'WAIT';
                        } else {
                            if ($data[$k]['status'] == '') {
                                $data[$k]['status'] = 'UNCONNECTED';
                            }
                        }
                    }

                    if ($v['iduser'] == $iduser) {
                        unset($data[$k]);
                    }

                }

                $res = array();
                foreach ($data as $v) {
                    $res[] = $v;
                }

            }
            // print_r($data);
            if ($res != null) {
                $res_code = REST_Controller::HTTP_OK;
                $res = $this->rscode->response('0000', $res);
                $this->set_response($res, $res_code);
            } else {
                $res_code = REST_Controller::HTTP_OK;
                $res = $this->rscode->response('0001', null);
                $this->set_response($res, $res_code);
            }
        }

        //
        // Finishing and log it up
        //

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

    // Get User QR

    public function qr_get()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/qr/',
            'request_method' => 'GET',
            'request_content' => serialize($this->get()),
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
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        }

        //
        // 4. Do the things..
        //
        if ($session === true) {
            $col = array('*');

            $filter = [];
            $filter['filter'] = $this->get();
            foreach ($filter['filter'] as $key => $value) {
                if ($key == 'access_token') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'platform') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'location') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'n_item') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'page') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'sort_by') {
                    unset($filter['filter'][$key]);
                } else if ($value == 'null') {
                    $filter['filter'][$key] = null;
                }
            }
            // {"command":"connect", "id":23, "type":"personal"}
            $content = array(
                "command" => "connect",
                "id" => $iduser,
                "type" => "personal",
            );
            $data = $this->encrypt_decrypt('encrypt', json_encode($content));
            // $data = $this->User_model->get_user($col, $filter);

            if ($data != null) {
                $res_code = REST_Controller::HTTP_OK;
                $res = $this->rscode->response('0000', $data);
                $this->set_response($res, $res_code);
            } else {
                $res_code = REST_Controller::HTTP_OK;
                $res = $this->rscode->response('0001', null);
                $this->set_response($res, $res_code);
            }
        }

        //
        // Finishing and log it up
        //

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

    // Get Klub User

    public function klub_get()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $detail = $this->get('detail');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/users klub/',
            'request_method' => 'GET',
            'request_content' => serialize($this->get()),
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
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        }

        //
        // 4. Do the things..
        //
        if ($session === true) {
            $col = array('idklub', 'klub_member.role', 'klub_member.status');
            // $col = array('*');

            $filter = [];
            $filter['filter'] = $this->get();
            foreach ($filter['filter'] as $key => $value) {
                if ($key == 'access_token') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'platform') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'location') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'n_item') {
                    unset($filter['filter'][$key]);
                    $filter['limit'][$key] = $value;
                } else if ($key == 'page') {
                    $filter['limit'][$key] = $value;
                    unset($filter['filter'][$key]);
                } else if ($key == 'sort_by') {
                    unset($filter['filter'][$key]);
                } else if ($value == 'null') {
                    $filter['filter'][$key] = null;
                } else if ($key == 'nameklub') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'detail') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'lastpost') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'iduser') {
                    $filter['filter']['klub_member.' . $key] = $value;
                    unset($filter['filter'][$key]);
                } else if ($key == 'idklub') {
                    $filter['filter']['klub_member.' . $key] = $value;
                    unset($filter['filter'][$key]);
                }
            }
            $data = array();
            // print_r($filter);
            $klub = $this->Klub_model->get_klubmember($col, $filter);
            // print_r($klub);
            if ($klub) {
                foreach ($klub as $kk => $vk) {
                    if ($vk['idklub'] == 3) {
                        // if ($vk['role'] != "SUB") {

                        //     print_r('sub/adm');
                        // } else {
                        //     print_r('mem');
                        //     unset($klub[$kk]);
                        // }
                        switch ($vk['role']) {
                            case "ADM":
                                // echo "ADM";
                                break;
                            case "SUB":
                                // echo "SUB";
                                break;
                            default:
                                // echo "MEM";
                                unset($klub[$kk]);
                        }
                    }
                    // if ($vk['idklub'] >= 164 && $vk['idklub'] <= 193) {
                    //     // if ($vk['role'] != "SUB") {

                    //     //     print_r('sub/adm');
                    //     // } else {
                    //     //     print_r('mem');
                    //     //     unset($klub[$kk]);
                    //     // }
                    //     switch ($vk['role']) {
                    //         case "ADM":
                    //             // echo "ADM";
                    //             break;
                    //         case "SUB":
                    //             // echo "SUB";
                    //             break;
                    //         default:
                    //             // echo "MEM";
                    //             unset($klub[$kk]);
                    //     }
                    // }

                }
                if ($detail == 'yes') {
                    $colKlub = array('*');
                } else {
                    $colKlub = array('idklub', 'name', 'klubid', 'description', 'pic_profile_url', 'ts_created', 'subscription_status', 'active', 'idmd_country', 'idmd_city');
                }

                $colKlubPrivacy = array('visibility_profile', 'posting');
                $colcountry = array('idmd_country', 'name');
                $colcity = array('idmd_city', 'name');
                $i = 0;
                foreach ($klub as $kk => $vk) {
                    // print_r($vk);
                    $filterKlub = array();
                    $filterKlub['filter'] = array(
                        'idklub' => $vk['idklub'],
                    );
                    if ($this->get('nameklub')) {
                        $filterKlub['filterLike']['name'] = $this->get('nameklub');
                    }
                    // print_r($filterKlub);

                    $listKlub = $this->Klub_model->get_klub($colKlub, $filterKlub);
                    // print_r($listKlub);
                    if ($listKlub) {
                        if ($listKlub[0]['active'] == 'Y') {

                            $data[$kk] = $listKlub[0];
                            $data[$kk]['role'] = $vk['role'];
                            $privacy = $this->Klub_model->get_klubPrivacy(array('*'), array('filter' => array('idklub' => $vk['idklub'])));
                            $data[$kk]['klub_type'] = $privacy[0]['join'];
                            $data[$kk]['posting_status'] = $privacy[0]['posting'];

                            $data[$kk]['privacy'] = $this->Klub_model->get_klubPrivacy($colKlubPrivacy, array('filter' => array('idklub' => $vk['idklub'])));

                            // print_r($data[$kk]['privacy']);
                            if ($data[$kk]['privacy'][0]['posting'] == 'UNMODERATED') {
                                $data[$kk]['member_can_post'] = 'YES';
                            } else {
                                $data[$kk]['member_can_post'] = 'NO';
                            }
                            $data[$kk]['badges'] = null;
                            $data[$kk]['badges'] = null;

                            $country = $this->Md_model->get_country($colcountry, array('filter' => array('idmd_country' => $listKlub[0]['idmd_country'])));
                            if ($country) {
                                // $data[$kk] = $listKlub[0];
                                $data[$kk]['country'] = $country[0];
                            }
                            $city = $this->Md_model->get_city($colcity, array('filter' => array('idmd_city' => $listKlub[0]['idmd_city'])));
                            if ($city) {
                                // $data[$kk] = $listKlub[0];
                                $data[$kk]['city'] = $city[0];
                            }

                            $filterKlubmember = array();
                            $filterKlubmember['filter'] = array(
                                'idklub' => $vk['idklub'],
                            );
                            $klubmember = $this->Klub_model->get_klubmember(array('*'), $filterKlubmember);
                            if ($klubmember) {
                                $data[$kk]['member_count'] = count($klubmember);
                            } else {
                                $data[$kk]['member_count'] = null;
                            }

                            // poll_vip_question

                            $filterVip['filter'] = array(
                                "idklub" => $vk['idklub'],
                            );
                            $vip = $this->Poll_model->get_poll_VIP(array('*'), $filterVip);
                            if ($vip) {
                                $status_request = 0;
                                $question = 1;
                                foreach ($vip as $vv) {
                                    if ($vv['status_request'] == 'APPROVE') {
                                        $status_request = 1;
                                        $question = $vv['question_value'];
                                        break;
                                    }
                                }
                                if ($status_request == 1) {
                                    $data[$kk]['poll_vip_question'] = array(
                                        'status' => true,
                                        'value' => (int) $question,
                                    );
                                } else {
                                    $data[$kk]['poll_vip_question'] = array(
                                        'status' => false,
                                        'value' => $question,
                                    );
                                }
                            } else {
                                $data[$kk]['poll_vip_question'] = array(
                                    'status' => false,
                                    'value' => 1,
                                );
                            }

                            $subcription = $this->Klub_model->get_subscription(array('*'), array('filter' => array('related_id' => $vk['idklub'], 'related_key' => 'idklub')));
                            if ($subcription) {
                                // $data[$kk] = $listKlub[0];
                                // print_r($subcription);
                                foreach ($subcription as $v) {
                                    if ($v['subcription_type'] == 'poll_vip_question') {
                                        $data[$kk]['poll_vip_question'] = array(
                                            'status' => filter_var($v['subscription_status'], FILTER_VALIDATE_BOOLEAN),
                                            'value' => (int) $v['value'],
                                        );
                                    }
                                }
                            }

                            $row = null;
                            // $row['sortdate'] = null;
                            if ($this->get('lastpost')) {
                                if ($this->get('lastpost') == true) {

                                    // $data[$kk] = $listKlub[0];
                                    $filterPost = array();
                                    $filterPost['filter'] = array(
                                        "post.idklub" => $listKlub[0]['idklub'],
                                    );
                                    $filterPost['sort'] = array(
                                        'post_comment.ts_created' => 'DESC',
                                        'post.ts_created' => 'DESC',
                                    );
                                    $filterPost['limit'] = array(
                                        'n_item' => 100,
                                        'page' => 0,
                                    );

                                    // $postKlub = $this->Post_model->get_post_detail('post.*, post_comment.idpost_comment', $filterPost);
                                    // $postKlubComment = $this->Post_model->get_post_detail_InnerComment('post.*, post_comment.idpost_comment', $filterPost);

                                    $getres_posts = $this->Timeline_model->get_timeline_posts($iduser, 0, 10, $listKlub[0]['idklub'], $platform);
                                    $getres_posts_comment = $this->Timeline_model->get_timeline_posts_comment($iduser, 0, 10, $listKlub[0]['idklub'], $platform);
                                    // print_r($getres_posts);
                                    if ($getres_posts_comment) {
                                        foreach ($getres_posts_comment as $vc) {
                                            if ($getres_posts) {
                                                foreach ($getres_posts as $k => $v) {
                                                    if ($vc['id'] == $v['id']) {
                                                        unset($getres_posts[$k]);
                                                    }
                                                }
                                            }
                                        }
                                        if ($getres_posts) {
                                            $get_res_posts_merge = array_merge($getres_posts, $getres_posts_comment);
                                        } else {
                                            $get_res_posts_merge = $getres_posts_comment;
                                        }

                                    } else {
                                        $get_res_posts_merge = $getres_posts;
                                    }

                                    if ($get_res_posts_merge) {
                                        $res_posts_sort = array();
                                        foreach ($get_res_posts_merge as $v) {
                                            $res_posts_sort[$v['ts_created']] = $v;
                                        }
                                        krsort($res_posts_sort);
                                        // print_r(json_encode($res_posts));
                                        // $res_posts_sort = array_slice($res_posts_sort, $page, $count);

                                        $postKlub = array();
                                        foreach ($res_posts_sort as $v) {
                                            $postKlub[] = $this->Post_model->get_post_detail('post.*, post_comment.idpost_comment', array('filter' => array('post.idpost' => $v['id'])))[0];
                                        }
                                    } else {
                                        $postKlub = null;
                                    }
                                    // print_r($listKlub[0]['idklub']);
                                    // print_r($postKlub);
                                    // print_r('<br>');
                                    // print_r('<br>');
                                    if ($postKlub) {
                                        $postKlub = array_slice($postKlub, 0, 1);
                                        foreach ($postKlub as $l) {
                                            $row = array();
                                            $row['idpost'] = $l['idpost'];
                                            $row['ts_created'] = $l['ts_created'];
                                            $row['iduser'] = $l['iduser'];
                                            $row['idklub'] = $l['idklub'];

                                            $action = array();
                                            $action[] = 'report';
                                            if (!empty($l['iduser'])) {
                                                $d = $this->User_model->get_user(array('*'), array("filter" => array('iduser' => $l['iduser'])));
                                                $row['user'] = (!empty($d)) ? $d[0] : $d;
                                            }
                                            if (!empty($l['idklub'])) {
                                                $k = $this->Klub_model->get_klub(array('*'), array('filter' => array('idklub' => $l['idklub'])));
                                                $row['klub'] = (!empty($k)) ? $k[0] : $k;
                                                if (!empty($row['klub'])) {
                                                    $r = $this->Klub_model->get_klubmember(array('role'), array('filter' => array('klub_member.idklub' => $l['idklub'], 'klub_member.iduser' => $iduser)));
                                                    $row['klub']['userprivilege'] = (!empty($r)) ? $r[0]['role'] : $r;
                                                    if (($row['klub']['userprivilege'] == 'ADM' or $row['klub']['userprivilege'] == 'SUB')) {
                                                        $action[] = 'delete';
                                                    }

                                                }
                                            }
                                            $filtercomment['limit'] = [];
                                            $filtercomment['limit']['n_item'] = 1;
                                            $filtercomment['limit']['page'] = 0;
                                            $filtercomment['sort'] = array(
                                                'ts_created' => 'DESC',
                                            );
                                            $filtercomment['filter'] = array(
                                                'idpost' => $l['idpost'],
                                            );
                                            $comment = $this->Post_model->get_comment(array('*'), $filtercomment);
                                            if ($comment) {
                                                $row['last_comment'] = $comment[0];
                                                $row['ts_comment'] = $comment[0]['ts_created'];
                                            } else {
                                                // $row['last_comment'] = 'null';
                                            }

                                            $row['caption'] = $l['caption'];
                                            $row['content'] = $this->Post_model->get_post_content('*', array('filter' => array('idpost' => $l['idpost'])));
                                            // Reaction Detail
                                            $getReact = $this->Post_model->get_postReaction(array('*'), array('filter' => array('idpost' => $l['idpost'])));
                                            $total = 0;
                                            $restructRes = array();
                                            // print_r($getReact);
                                            if ($getReact != null) {
                                                foreach ($getReact as $va) {
                                                    foreach ($va as $k => $v) {
                                                        if ($k != 'comment_count' || $k != 'share_count' || $k != 'report_count' || $k != 'idpost') {

                                                            $restructRes['reaction_data'][] = array(
                                                                'type' => str_replace('_count', '', $k),
                                                                'count' => $v,
                                                            );
                                                            $total = $total + $v;
                                                        }
                                                    }
                                                }
                                            }
                                            $restructRes['counttotal'] = $total;
                                            $row['reaction'] = $restructRes;
                                            //

                                            // My Reaction
                                            $getMyReaction = $this->Post_model->get_myReaction(array('*'), array('filter' => array('idpost' => $l['idpost'], 'iduser' => $iduser)));

                                            $resMyReaction = null;
                                            if ($getMyReaction != null) {
                                                $resMyReaction = array();
                                                foreach ($getMyReaction as $r) {
                                                    $resMyReaction['id'] = $r['idpost_reaction_detail'];
                                                    $resMyReaction['ts_created'] = $r['ts_created'];
                                                    $resMyReaction['reaction_type'] = $r['reaction_type'];
                                                }
                                            }
                                            $row['myreaction'] = $resMyReaction;
                                            //

                                            if ($iduser != $row['iduser']) {
                                                $action[] = 'hidepost';
                                                $action[] = 'hideuser';
                                            } else {
                                                if (empty(in_array("delete", $action))) {
                                                    $action[] = 'delete';
                                                }
                                            }
                                            $action[] = 'disablecomment';
                                            $row['action'] = $action;

                                            if ($l['allow_comment'] == 'Y') {
                                                $allowComment = true;
                                            } else {
                                                $allowComment = false;
                                            }
                                            $row['comment'] = $allowComment;
                                            $row['status'] = $l['status'];
                                            $row['shared_from'] = null;
                                            if ($l['shared_from']) {
                                                $filter['filter'] = array('post.idpost' => $l['shared_from']);
                                                $sf = $this->Post_model->get_post_detail('post.*', $filter);
                                                foreach ($sf as $sl) {
                                                    $sfrow = array();
                                                    $sfrow['idpost'] = $sl['idpost'];
                                                    $sfrow['ts_created'] = $sl['ts_created'];
                                                    $sfrow['iduser'] = $sl['iduser'];
                                                    $sfrow['idklub'] = $sl['idklub'];

                                                    $action = array();
                                                    $action[] = 'report';
                                                    if (!empty($sl['iduser'])) {
                                                        $d = $this->User_model->get_user(array('*'), array("filter" => array('iduser' => $sl['iduser'])));
                                                        $sfrow['user'] = (!empty($d)) ? $d[0] : $d;
                                                    }
                                                    if (!empty($sl['idklub'])) {
                                                        $k = $this->Klub_model->get_klub(array('*'), array('filter' => array('idklub' => $sl['idklub'])));
                                                        $sfrow['klub'] = (!empty($k)) ? $k[0] : $k;
                                                        if (!empty($sfrow['klub'])) {
                                                            $r = $this->Klub_model->get_klubmember(array('role'), array('filter' => array('klub_member.idklub' => $sl['idklub'], 'klub_member.iduser' => $iduser)));
                                                            $sfrow['klub']['userprivilege'] = (!empty($r)) ? $r[0]['role'] : $r;
                                                            if (($sfrow['klub']['userprivilege'] == 'ADM' or $sfrow['klub']['userprivilege'] == 'SUB')) {
                                                                $action[] = 'delete';
                                                            }

                                                        }
                                                    }
                                                    $sfrow['caption'] = $sl['caption'];
                                                    $sfrow['content'] = $this->Post_model->get_post_content('*', array('filter' => array('idpost' => $sl['idpost'])));

                                                    // Reaction Detail
                                                    $getReact = $this->Post_model->get_postReaction(array('*'), array('filter' => array('idpost' => $sl['idpost'])));
                                                    $total = 0;
                                                    $restructRes = array();
                                                    // print_r($getReact);
                                                    if ($getReact != null) {
                                                        foreach ($getReact as $va) {
                                                            foreach ($va as $k => $v) {
                                                                if ($k != 'comment_count' || $k != 'share_count' || $k != 'report_count' || $k != 'idpost') {

                                                                    $restructRes['reaction_data'][] = array(
                                                                        'type' => str_replace('_count', '', $k),
                                                                        'count' => $v,
                                                                    );
                                                                    $total = $total + $v;
                                                                }
                                                            }
                                                        }
                                                    }
                                                    $restructRes['counttotal'] = $total;
                                                    $sfrow['reaction'] = $restructRes;
                                                    //

                                                    // My Reaction
                                                    $getMyReaction = $this->Post_model->get_myReaction(array('*'), array('filter' => array('idpost' => $sl['idpost'], 'iduser' => $iduser)));

                                                    $resMyReaction = null;
                                                    if ($getMyReaction != null) {
                                                        $resMyReaction = array();
                                                        foreach ($getMyReaction as $r) {
                                                            $resMyReaction['id'] = $r['idpost_reaction_detail'];
                                                            $resMyReaction['ts_created'] = $r['ts_created'];
                                                            $resMyReaction['reaction_type'] = $r['reaction_type'];
                                                        }
                                                    }
                                                    $sfrow['myreaction'] = $resMyReaction;
                                                    //

                                                    if ($iduser != $sfrow['iduser']) {
                                                        $action[] = 'hidepost';
                                                        $action[] = 'hideuser';
                                                    } else {
                                                        if (empty(in_array("delete", $action))) {
                                                            $action[] = 'delete';
                                                        }
                                                    }
                                                    $action[] = 'disablecomment';
                                                    $sfrow['action'] = $action;

                                                    if ($sl['allow_comment'] == 'Y') {
                                                        $allowComment = true;
                                                    } else {
                                                        $allowComment = false;
                                                    }
                                                    $sfrow['comment'] = $allowComment;
                                                    $sfrow['status'] = $sl['status'];
                                                }
                                                $row['shared_from'] = $sfrow;
                                            }
                                            //  $row;
                                        }
                                    }
                                    $data[$kk]['lastpost'] = $row;
                                    if (isset($row['ts_comment'])) {
                                        $data[$kk]['sortdate'] = $row['ts_comment'];
                                    } else {
                                        $data[$kk]['sortdate'] = $row['ts_created'];
                                    }

                                }
                            } else {

                                // $data[$kk] = $listKlub[0];
                                $filterPost = array();
                                $filterPost['filter'] = array(
                                    "post.idklub" => $listKlub[0]['idklub'],
                                );
                                $filterPost['sort'] = array(
                                    'post_comment.ts_created' => 'DESC',
                                    'post.ts_created' => 'DESC',
                                );
                                $filterPost['limit'] = array(
                                    'n_item' => 100,
                                    'page' => 0,
                                );

                                $postKlub = $this->Post_model->get_post_detail('post.*', $filterPost);
                                if ($postKlub) {

                                    $postKlub = array_slice($postKlub, 0, 1);
                                    foreach ($postKlub as $l) {
                                        $row = array();
                                        $row['idpost'] = $l['idpost'];
                                        $row['ts_created'] = $l['ts_created'];
                                        $row['iduser'] = $l['iduser'];
                                        $row['idklub'] = $l['idklub'];

                                        $action = array();
                                        $action[] = 'report';
                                        if (!empty($l['iduser'])) {
                                            $d = $this->User_model->get_user(array('*'), array("filter" => array('iduser' => $l['iduser'])));
                                            $row['user'] = (!empty($d)) ? $d[0] : $d;
                                        }
                                        if (!empty($l['idklub'])) {
                                            $k = $this->Klub_model->get_klub(array('*'), array('filter' => array('idklub' => $l['idklub'])));
                                            $row['klub'] = (!empty($k)) ? $k[0] : $k;
                                            if (!empty($row['klub'])) {
                                                $r = $this->Klub_model->get_klubmember(array('role'), array('filter' => array('klub_member.idklub' => $l['idklub'], 'klub_member.iduser' => $iduser)));
                                                $row['klub']['userprivilege'] = (!empty($r)) ? $r[0]['role'] : $r;
                                                if (($row['klub']['userprivilege'] == 'ADM' or $row['klub']['userprivilege'] == 'SUB')) {
                                                    $action[] = 'delete';
                                                }

                                            }
                                        }

                                        $filtercomment['limit'] = [];
                                        $filtercomment['limit']['n_item'] = 1;
                                        $filtercomment['limit']['page'] = 0;
                                        $filtercomment['sort'] = array(
                                            'ts_created' => 'DESC',
                                        );
                                        $filtercomment['filter'] = array(
                                            'idpost' => $l['idpost'],
                                        );
                                        $comment = $this->Post_model->get_comment(array('*'), $filtercomment);
                                        if ($comment) {
                                            $row['ts_comment'] = $comment[0]['ts_created'];
                                        }
                                    }
                                }
                                // $data[$kk]['lastpost'] = $row;
                                if (isset($row['ts_comment'])) {
                                    $data[$kk]['sortdate'] = $row['ts_comment'];
                                } else {
                                    $data[$kk]['sortdate'] = $row['ts_created'];
                                }
                            }
                        }

                        $i++;
                    }
                }
            }
            if ($data != null) {
                // print_r($data);
                $keys = array_column($data, 'sortdate');

                array_multisort($keys, SORT_DESC, $data);

                $res_code = REST_Controller::HTTP_OK;
                $res = $this->rscode->response('0000', $data);
                $this->set_response($res, $res_code);
            } else {
                $res_code = REST_Controller::HTTP_OK;
                $res = $this->rscode->response('0001', null);
                $this->set_response($res, $res_code);
            }
        }

        //
        // Finishing and log it up
        //

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

    public function galery_get()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $idklub = $this->get('idklub');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/qrklub/',
            'request_method' => 'GET',
            'request_content' => serialize($this->get()),
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
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        }

        //
        // 4. Do the things..
        //
        if ($session === true) {
            $col = array('*');

            $filter = [];
            $filter['filter'] = $this->get();
            foreach ($filter['filter'] as $key => $value) {
                if ($key == 'access_token') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'platform') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'location') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'n_item') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'page') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'sort_by') {
                    unset($filter['filter'][$key]);
                } else if ($key == 'iduser') {
                    unset($filter['filter'][$key]);
                } else if ($value == 'null') {
                    $filter['filter'][$key] = null;
                }
            }
            $page = $this->get('_start');
            if (!isset($page)) {
                $page = 0;
            } else {
                if (is_numeric($page)) {
                    $page = intval($page) * ((int) $this->get('_limit') / 2);
                } else {
                    $page = 0;
                }
            }
            $count = $this->get('_limit');
            if (!isset($count)) {
                $count = 10;
            } else {
                if (is_numeric($count)) {
                    $count = intval($count) / 2;
                } else {
                    $count = 5;
                }
            }
            $limit = array(
                'limit' => array(
                    'page' => $page,
                    'n_item' => $count,
                ),
            );

            $content = $this->Post_model->get_postcontent(array('idpost_content', 'post.idpost', 'media_type', 'media_url', 'thumb'), array('filter' => array('post.iduser' => $iduser, 'post.status' => "P"), 'sort' => array('idpost' => 'DESC'), 'limit' => array(
                'page' => $page,
                'n_item' => $count,
            )));

            $colmember = array('idklub');
            $filtermember['filter']['klub_member.iduser'] = $iduser;
            $klubmember = $this->Klub_model->get_klubmember($colmember, $filtermember);

            if ($content) {
                $klubid = array();
                if ($klubmember) {
                    foreach ($klubmember as $id) {
                        $cekStatusKlub = $this->Klub_model->get_klubPrivacy(array('join'), array('filter' => array('idklub' => $id['idklub'])));
                        // if ($cekStatusKlub[0]['join'] == 'PUB') {
                        $klubid[] = $id['idklub'];
                        // }

                    }
                    // print_r($klubid);
                    $contentKlub = $this->Post_model->get_postcontent(array('idpost_content', 'post.idpost', 'media_type', 'media_url', 'thumb'), array('filterin' => array('post.idklub' => $klubid), 'sort' => array('idpost' => 'DESC'), 'limit' => array(
                        'page' => $page,
                        'n_item' => $count,
                    )));

                    if ($contentKlub) {
                        // print_r($contentKlub);
                        $sameID = array();
                        foreach ($content as $c) {
                            foreach ($contentKlub as $kk => $ck) {
                                if ($c['idpost_content'] == $ck['idpost_content']) {
                                    $sameID[] = $c['idpost'];
                                    unset($contentKlub[$kk]);
                                }
                            }
                        }
                        $content = array_merge($content, $contentKlub);
                    }
                }
                // print_r($sameID);

                // foreach ($content as $k => $c) {
                //     if ($c['media_url'] == '') {
                //         unset($content[$k]);
                //     }
                // }
                $data = $content;
            } else {
                $content = array();
                $klubid = array();
                if ($klubmember) {
                    foreach ($klubmember as $id) {
                        $cekStatusKlub = $this->Klub_model->get_klubPrivacy(array('join'), array('filter' => array('idklub' => $id['idklub'])));
                        // if ($cekStatusKlub[0]['join'] == 'PUB') {
                        $klubid[] = $id['idklub'];
                        // }

                    }
                    // print_r($klubid);
                    $contentKlub = $this->Post_model->get_postcontent(array('idpost_content', 'post.idpost', 'media_type', 'media_url', 'thumb'), array('filterin' => array('post.idklub' => $klubid), 'sort' => array('idpost' => 'DESC'), 'limit' => array(
                        'page' => $page,
                        'n_item' => $count,
                    )));

                    if ($contentKlub) {
                        // print_r($contentKlub);
                        $sameID = array();
                        foreach ($content as $c) {
                            foreach ($contentKlub as $kk => $ck) {
                                if ($c['idpost_content'] == $ck['idpost_content']) {
                                    $sameID[] = $c['idpost'];
                                    unset($contentKlub[$kk]);
                                }
                            }
                        }
                        $content = array_merge($content, $contentKlub);
                    }
                }
                // print_r($sameID);

                // foreach ($content as $k => $c) {
                //     if ($c['media_url'] == '') {
                //         unset($content[$k]);
                //     }
                // }
                $data = $content;
            }

            if ($data != null) {
                $res_code = REST_Controller::HTTP_OK;
                $res = $this->rscode->response('0000', $data);
                $this->set_response($res, $res_code);
            } else {
                $res_code = REST_Controller::HTTP_OK;
                $res = $this->rscode->response('0001', null);
                $this->set_response($res, $res_code);
            }
        }

        //
        // Finishing and log it up
        //

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

    /*

    POST function

     */

    public function pictureBannerProfile_post()
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
        $data_json = ($data);
        //var_dump($data);
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => 'user/pictureBannerProfile/',
            'request_method' => 'post',
            'request_content' => serialize($this->get()),
        );

        //
        // 2. Log the service call
        //
        $request_id =
        $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res = false;
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {
            // Upload File (image/video/docs)
            // $idklub = $this->post('idklub');
            if (isset($_FILES)) {

                if (!file_exists('./assets/' . $iduser)) {
                    mkdir('./assets/' . $iduser, 0777, true);
                }
                if (!file_exists('./assets/' . $iduser . '/pp')) {
                    mkdir('./assets/' . $iduser . '/pp', 0777, true);
                }
                // print_r($_FILES);

                $config = array();
                $config['pic_profile'] = array(
                    'upload_path' => './assets/' . $iduser . '/pp/',
                    'allowed_types' => 'jpg|gif|png|jpeg',
                    'overwrite' => 1,
                );

                $config['pic_banner'] = array(
                    'upload_path' => './assets/' . $iduser . '/pp/',
                    'allowed_types' => 'jpg|gif|png|jpeg',
                    'overwrite' => 1,
                );

                $ft = array();
                $ft['pic_profile'] = array(
                    'upload_path' => './assets/' . $iduser . '/pp/',
                    'allowed_types' => 'jpg|gif|png|jpeg',
                    'overwrite' => 1,
                );

                $ft['pic_banner'] = array(
                    'upload_path' => './assets/' . $iduser . '/pp/',
                    'allowed_types' => 'jpg|gif|png|jpeg',
                    'overwrite' => 1,
                );

                $images = array();
                // print_r(($_FILES));
                $url_transfer_script = '';
                $postContent = array();
                $order = 1;

                foreach ($ft as $k => $c) {
                    $_FILES[$k . '[]'] = array();
                    if (isset($_FILES[$k])) {
                        // echo $k;
                        // }
                        foreach ($_FILES[$k]['name'] as $key => $image) {
                            $_FILES[$k . '[]']['name'] = $_FILES[$k]['name'][$key];
                            $_FILES[$k . '[]']['type'] = $_FILES[$k]['type'][$key];
                            $_FILES[$k . '[]']['tmp_name'] = $_FILES[$k]['tmp_name'][$key];
                            $_FILES[$k . '[]']['error'] = $_FILES[$k]['error'][$key];
                            $_FILES[$k . '[]']['size'] = $_FILES[$k]['size'][$key];

                            $file_ext = pathinfo($_FILES[$k . "[]"]["name"], PATHINFO_EXTENSION);

                            // if (!file_exists($c['upload_path'])) {
                            //     mkdir($c['upload_path'], 0777, true);
                            // }

                            $newfilename = substr(md5(uniqid(mt_rand(), true)), 0, 16) . '.' . $file_ext;
                            $config[$k]['file_name'] = $newfilename;

                            // print_r($c);
                            $imDoc = array();
                            foreach ($config as $kc => $vc) {
                                // print_r(json_encode($_FILES));
                                if ($kc == $k) {
                                    $this->load->library('upload', $config[$kc]);
                                    $this->upload->initialize($config[$kc]);
                                    if ($this->upload->do_upload($kc . '[]')) {
                                        $this->upload->data();
                                        $url_transfer_script = base_url() . str_replace('./', '', $vc['upload_path']) . $newfilename;
                                        // print_r($kc);
                                        if ($kc == 'pic_profile') {
                                            $imDoc = array(
                                                'pic_profile_url' => $url_transfer_script,
                                            );
                                            $updateKlub = $this->User_model->update_user($imDoc, array('iduser' => $iduser));
                                        } elseif ($kc == 'pic_banner') {
                                            $imDoc = array(
                                                'pic_banner_url' => $url_transfer_script,
                                            );
                                            $updateKlub = $this->User_model->update_user($imDoc, array('iduser' => $iduser));
                                        }
                                        // print_r($updateKlub);
                                    } else {
                                        // print_r($vc['upload_path']);
                                        // print_r($this->upload->display_errors());
                                    }
                                }
                            }
                        }
                    }
                }
                $res = true;
            }

            $res = $this->rscode->response('0000', $res);
            $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
        }
        //
        // Finishing and log it up
        //

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

    /*

    Custom function

     */

    public function sendEmail($email, $subject, $message, $file, $bcc)
    {

        $config = array(
            'protocol' => 'smtp',
            'smtp_host' => 'ssl://smtp.googlemail.com',
            'smtp_port' => 465,
            'smtp_user' => 'hayuniamansejahtera.idn@gmail.com',
            'smtp_pass' => 'hayuni2019',
            'mailtype' => 'html',
            'charset' => 'iso-8859-1',
            'wordwrap' => true,
        );

        $this->load->library('email', $config);
        $this->email->set_newline("\r\n");
        $this->email->from('hayuniamansejahtera.idn@gmail.com');
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

    // DELETE

    public function termofcondition_delete()
    {
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = $this->delete('toc_detail_id');
        $data_json = $data;
        // var_dump($data);
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/TOC Del/',
            'request_method' => 'del',
            'request_content' => serialize($this->get()),
        );
        //
        // 2. Log the service call
        //
        $request_id =
        $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res;
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {

            $filter['filter'] = array(
                'toc_detail_id' => $data_json,
            );

            $res = $this->User_model->delete_toc($filter);

            $res = $this->rscode->response('0000', $res);
            $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
        }

        $exec_time = microtime(true) - $this->_exec_time_start;

        $data = array(
            'iduser_access_log_request' => $request_id,
            'execution_time' => $exec_time,
            'response_code' => $res['errorcode'],
            'response_content' => serialize($res),
        );
        $this->User_model->insert_user_access_log_response($data);
    }

    public function exist_get()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = $this->get();
        $data_json = $data;

        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/user/exist',
            'request_method' => 'GET',
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
        if (@$data_json['msisdn']) {
            $filter['filter']['msisdn'] = $data_json['msisdn'];
        }
        if (@$data_json['email']) {
            $filter['filter']['email'] = $data_json['email'];
        }

        $res = $this->User_model->get_user('*', $filter);

        if (empty($res)) {
            $res = $this->rscode->response('0001', false);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {
            //check last send otp
            $res_code = REST_Controller::HTTP_OK;
            $res = $this->rscode->response('0000', true);
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

    public function connectrequest_post()
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
        $data_json = ($data);
        //var_dump($data);
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => 'user/connectrequest/',
            'request_method' => 'post',
            'request_content' => serialize($this->post()),
        );

        //
        // 2. Log the service call
        //
        $request_id =
        $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res = true;
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {

            if (@$data_json['connectto']) {

                $filter['filter'] = array(
                    'iduser_requester' => $iduser,
                    'user_connection_request.iduser' => $data_json['connectto'],

                    'request_status' => 'NEW',
                    // 'user_connection_request.iduser_requester' => $iduser,
                );

                $dataReq = $this->User_model->get_userconnectionrequest(array('*'), $filter);
                if ($dataReq) {

                    $res = $this->rscode->response('0300', false);
                } else {
                    $paramPost = array(
                        'iduser' => $data_json['connectto'],
                        'iduser_requester' => $iduser,
                        'user_connection_requestcol' => @$data_json['user_connection_requestcol'],
                    );
                    $insertPost = $this->User_model->insert_user_connection_request($paramPost);
                    $res = $this->rscode->response('0000', true);

                    // Notification user connect request
                    // 1. get POST

                    $gcm = new GCM();
                    $modul = 'USER';
                    $type = 'USER_CONNECT_REQUEST';
                    $body = 'User Connection Request';

                    // 2. cek fcmid and send notif

                    $fcmMember = array();
                    $fcmMember[] = $data_json['connectto'];

                    // 3. insert notif

                    foreach ($fcmMember as $vk) {
                        $postNotif = array(
                            'modul' => $modul,
                            'topic' => $vk,
                            'type' => $type,
                            'iduser' => $vk,
                            'is_viewed' => 'N',
                            'is_action' => 'N',
                            'text' => 'User Connection request',
                            // 'confirm_status' => $data_json['status'],
                            'related_id' => $iduser,
                            'related_key' => 'iduser_sender',
                        );
                        $insertnotification = $gcm->notificationPost($postNotif);
                        $postNotif['idnotification'] = $insertnotification;

                        // 4. cek summary notif user, insert or update it
                        $filterGetNotif = array('filter' => array(
                            'iduser' => $vk,
                        ));
                        $notificationuserget = $gcm->notificationUserget(array('*'), $filterGetNotif);

                        if ($notificationuserget) {
                            if ($modul == 'POST') {
                                $dataNotifUserUpdate['post'] = (int) $notificationuserget[0]['post'] + 1;
                            } elseif ($modul == 'EVENT') {
                                $dataNotifUserUpdate['event'] = (int) $notificationuserget[0]['event'] + 1;
                            } elseif ($modul == 'POLL') {
                                $dataNotifUserUpdate['poll'] = (int) $notificationuserget[0]['poll'] + 1;
                            } elseif ($modul == 'MARKET') {
                                $dataNotifUserUpdate['market'] = (int) $notificationuserget[0]['market'] + 1;
                            } elseif ($modul == 'KLUB') {
                                $dataNotifUserUpdate['klub'] = (int) $notificationuserget[0]['klub'] + 1;
                            }

                            // $gcm->notificationuserupdate($dataNotifUserUpdate, array('idnotification_user' => $notificationuserget[0]['idnotification_user']));

                            // counter Bell

                            $dataNotifUserBellUpdate['bell'] = (int) $notificationuserget[0]['bell'] + 1;
                            $gcm->notificationuserupdate($dataNotifUserBellUpdate, array('idnotification_user' => $notificationuserget[0]['idnotification_user']));

                        } else {
                            $postInsertnotifUser = array(
                                'iduser' => $vk,
                            );

                            if ($modul == 'POST') {
                                $postInsertnotifUser['post'] = 1;
                            } elseif ($modul == 'EVENT') {
                                $postInsertnotifUser['event'] = 1;
                            } elseif ($modul == 'POLL') {
                                $postInsertnotifUser['poll'] = 1;
                            } elseif ($modul == 'MARKET') {
                                $postInsertnotifUser['market'] = 1;
                            } elseif ($modul == 'KLUB') {
                                $postInsertnotifUser['klub'] = 1;
                            }
                            // counter Bell
                            $postInsertnotifUser['bell'] = 1;

                            $gcm->notificationUserPost($postInsertnotifUser);
                        }
                    }

                    $filter['filter'] = array('status' => 0, 'fcm_id !=' => '');
                    $filter['filterIn'] = array('iduser' => $fcmMember);

                    $datafcmid = $this->User_model->get_agent_login_session('fcm_id', $filter);

                    $fcmid = array();
                    if ($datafcmid) {

                        // 5. get user info
                        // $dataklub = $this->Klub_model->get_klub($col, array('filter' => array('idklub' => $klub)));
                        $cekuser = $this->User_model->get_user(array('*'), array("filter" => array('iduser' => $iduser)));
                        // 6. send notif and Update user summary/counter

                        foreach ($datafcmid as $c) {

                            array_push($fcmid, $c['fcm_id']);
                        }
                        $msgNotif = array("notification" => array("body" => 'you have new Connection Request from ' . $cekuser[0]['fullname'], "title" => $body, "click_action" => "FLUTTER_NOTIFICATION_CLICK"));
                        // $msgNotif = '';
                        $resultFCM = $gcm->send_notification($fcmid, $msgNotif, array('modul' => $modul, 'type' => $type, 'iduser_connection_request' => $insertPost));
                        // print_r($resultFCM);
                    }
                }

                $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
            }
        }
        //
        // Finishing and log it up
        //

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

    public function connectApprove_post()
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
        $data_json = ($data);
        //var_dump($data);
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => 'user/connectApprove/',
            'request_method' => 'post',
            'request_content' => serialize($this->post()),
        );

        //
        // 2. Log the service call
        //
        $request_id =
        $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res = false;
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {
            if ($data_json['iduser_connection_request']) {
                $col = array('*');
                $filter['filter'] = array(
                    'iduser_connection_request' => $data_json['iduser_connection_request'],
                    // 'iduser' => $iduser,
                );

                $dataReq = $this->User_model->get_userconnectionrequest($col, $filter);
                // print_r($dataReq);
                if ($dataReq) {
// insert 1
                    $filtercek['filter'] = array(
                        'iduser' => $dataReq[0]['iduser'],
                        'iduser_friend' => $dataReq[0]['iduser_requester'],
                    );
                    $dataCek = $this->User_model->get_userconnection($col, $filtercek);

                    if ($dataCek) {

                        $res = false;
                        $res = $this->rscode->response('0099', $res);

                    } else {

                        $paramPost1 = array(
                            'iduser' => $dataReq[0]['iduser'],
                            'iduser_friend' => $dataReq[0]['iduser_requester'],
                        );
                        $insertPost1 = $this->User_model->insert_user_connection($paramPost1);

// insert 2
                        $paramPost2 = array(
                            'iduser' => $dataReq[0]['iduser_requester'],
                            'iduser_friend' => $dataReq[0]['iduser'],
                        );
                        $insertPost2 = $this->User_model->insert_user_connection($paramPost2);

// Update connection Request
                        $paramUpdate = array(
                            'request_status' => $data_json['status'],
                        );
                        $updateRequest = $this->User_model->update_userConnectionRequest($paramUpdate, array('iduser_connection_request' => $data_json['iduser_connection_request']));

                        $res = true;
                        $res = $this->rscode->response('0000', $res);

                        // Notification user connect request
                        // 1. get POST

                        $gcm = new GCM();
                        $modul = 'USER';
                        $type = 'USER_CONNECT_REQUEST_CONFIRMATION';
                        $body = 'User Connection Request Confirmation';

                        // 2. cek fcmid and send notif

                        $fcmMember = array();
                        $fcmMember[] = $dataReq[0]['iduser_requester'];

                        // 3. insert notif
                        // print_r()
                        foreach ($fcmMember as $vk) {
                            $postNotif = array(
                                'modul' => $modul,
                                'topic' => $vk,
                                'type' => $type,
                                'iduser' => $vk,
                                'is_viewed' => 'N',
                                'is_action' => 'N',
                                'text' => 'User Connection Request Confirmation',
                                'confirm_status' => $data_json['status'],
                                'related_id' => $iduser,
                                'related_key' => 'iduser_sender',
                            );
                            $insertnotification = $gcm->notificationPost($postNotif);
                            $postNotif['idnotification'] = $insertnotification;

                            // 4. cek summary notif user, insert or update it
                            $filterGetNotif = array('filter' => array(
                                'iduser' => $vk,
                            ));
                            $notificationuserget = $gcm->notificationUserget(array('*'), $filterGetNotif);

                            if ($notificationuserget) {
                                if ($modul == 'POST') {
                                    $dataNotifUserUpdate['post'] = (int) $notificationuserget[0]['post'] + 1;
                                } elseif ($modul == 'EVENT') {
                                    $dataNotifUserUpdate['event'] = (int) $notificationuserget[0]['event'] + 1;
                                } elseif ($modul == 'POLL') {
                                    $dataNotifUserUpdate['poll'] = (int) $notificationuserget[0]['poll'] + 1;
                                } elseif ($modul == 'MARKET') {
                                    $dataNotifUserUpdate['market'] = (int) $notificationuserget[0]['market'] + 1;
                                } elseif ($modul == 'KLUB') {
                                    $dataNotifUserUpdate['klub'] = (int) $notificationuserget[0]['klub'] + 1;
                                }

                                // $gcm->notificationuserupdate($dataNotifUserUpdate, array('idnotification_user' => $notificationuserget[0]['idnotification_user']));

                                // counter Bell

                                $dataNotifUserBellUpdate['bell'] = (int) $notificationuserget[0]['bell'] + 1;
                                $gcm->notificationuserupdate($dataNotifUserBellUpdate, array('idnotification_user' => $notificationuserget[0]['idnotification_user']));

                            } else {
                                $postInsertnotifUser = array(
                                    'iduser' => $vk,
                                );

                                if ($modul == 'POST') {
                                    $postInsertnotifUser['post'] = 1;
                                } elseif ($modul == 'EVENT') {
                                    $postInsertnotifUser['event'] = 1;
                                } elseif ($modul == 'POLL') {
                                    $postInsertnotifUser['poll'] = 1;
                                } elseif ($modul == 'MARKET') {
                                    $postInsertnotifUser['market'] = 1;
                                } elseif ($modul == 'KLUB') {
                                    $postInsertnotifUser['klub'] = 1;
                                }
                                // counter Bell
                                $postInsertnotifUser['bell'] = 1;

                                $gcm->notificationUserPost($postInsertnotifUser);
                            }
                        }

                        $filter['filter'] = array('status' => 0, 'fcm_id !=' => '');
                        $filter['filterIn'] = array('iduser' => $fcmMember);

                        $datafcmid = $this->User_model->get_agent_login_session('fcm_id', $filter);

                        $fcmid = array();
                        if ($datafcmid) {

                            // 5. get klub info
                            // $dataklub = $this->Klub_model->get_klub($col, array('filter' => array('idklub' => $klub)));
                            $cekuser = $this->User_model->get_user(array('*'), array("filter" => array('iduser' => $iduser)));
                            // 6. send notif and Update user summary/counter

                            foreach ($datafcmid as $c) {

                                array_push($fcmid, $c['fcm_id']);
                            }
                            $msgNotif = array("notification" => array("body" => $cekuser[0]['fullname'] . ' has ' . $data_json['status'] . ' your request', "title" => $body, "click_action" => "FLUTTER_NOTIFICATION_CLICK"));
                            // $msgNotif = '';
                            $resultFCM = $gcm->send_notification($fcmid, $msgNotif, array('modul' => $modul, 'type' => $type, 'iduser_connection_request' => $data_json['iduser_connection_request']));
                            // print_r($resultFCM);
                        }
                    }
                }
                $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
            }
        }
        //
        // Finishing and log it up
        //

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

    public function connectInvitation_post()
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
        $data_json = ($data);
        //var_dump($data);
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => 'user/connectApprove/',
            'request_method' => 'post',
            'request_content' => serialize($this->post()),
        );

        //
        // 2. Log the service call
        //
        $request_id =
        $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res = false;
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {
            if ($data_json['iduser_connection_request']) {
                $col = array('*', 'user_connection_request.iduser as iduser');
                $filter['filter'] = array(
                    'iduser_connection_request' => $data_json['iduser_connection_request'],
                    // 'user_connection_request.iduser_requester' => $iduser,
                );

                $dataReq = $this->User_model->get_userconnectionrequest($col, $filter);
                // print_r($dataReq);

                if ($dataReq) {
                    if ($this->post('status')) {
                        // print_r($this->post('status'));
                        // if ($this->post('status') == 'APPROVE') {
                        switch ($this->post('status')) {
                            case 'APPROVE':
                                // echo 'approve';
                                // insert 1
                                $paramPost1 = array(
                                    'iduser' => $dataReq[0]['iduser'],
                                    'iduser_friend' => $dataReq[0]['iduser_requester'],
                                );
                                // print_r($paramPost1);
                                $insertPost1 = $this->User_model->insert_user_connection($paramPost1);

// insert 2
                                if ($insertPost1) {
                                    $paramPost2 = array(
                                        'iduser_friend' => $dataReq[0]['iduser'],
                                        'iduser' => $dataReq[0]['iduser_requester'],
                                    );
                                    // print_r($paramPost2);
                                    $insertPost2 = $this->User_model->insert_user_connection($paramPost2);
                                }

// Update connection Request
                                $paramUpdate = array(
                                    'request_status' => 'APPROVE',
                                );
                                $updateRequest = $this->User_model->update_userConnectionRequest($paramUpdate, array('iduser_connection_request' => $data_json['iduser_connection_request']));

                            // } else if ($this->get('status') == 'REJECT') {
                            case 'REJECT':

                                // echo 'reject';
                                // Update connection Request
                                $paramUpdate = array(
                                    'request_status' => 'REJECT',
                                );
                                $updateRequest = $this->User_model->update_userConnectionRequest($paramUpdate, array('iduser_connection_request' => $data_json['iduser_connection_request']));

                        }
                    } else {

// insert 1
                        $paramPost1 = array(
                            'iduser' => $dataReq[0]['iduser'],
                            'iduser_friend' => $dataReq[0]['iduser_requester'],
                        );
                        $insertPost1 = $this->User_model->insert_user_connection($paramPost1);

// insert 2
                        $paramPost2 = array(
                            'iduser_friend' => $dataReq[0]['iduser'],
                            'iduser' => $dataReq[0]['iduser_requester'],
                        );
                        $insertPost2 = $this->User_model->insert_user_connection($paramPost2);

// Update connection Request
                        $paramUpdate = array(
                            'request_status' => 'APPROVE',
                        );
                        $updateRequest = $this->User_model->update_userConnectionRequest($paramUpdate, array('iduser_connection_request' => $data_json['iduser_connection_request']));

                    }

                    $dataReqFin = $this->User_model->get_userconnectionrequest($col, $filter);
                    $res = true;
                    $res = $this->rscode->response('0000', $res);
                    // $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code

                } else {

                    $res = $this->rscode->response('0001', $res);
                }
                $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
            }
        }
        //
        // Finishing and log it up
        //

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

    public function connectInvitationCancel_post()
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
        $data_json = ($data);
        //var_dump($data);
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => 'user/connectInvitationCancel/',
            'request_method' => 'post',
            'request_content' => serialize($this->post()),
        );

        //
        // 2. Log the service call
        //
        $request_id =
        $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res = false;
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {
            if ($data_json['iduser_connection_request']) {

                $filter = array(
                    'iduser_connection_request' => $data_json['iduser_connection_request'],
                    // 'user_connection_request.iduser_requester' => $iduser,
                );

                $dataReq = $this->User_model->delete_user_connection_request($filter);
                // print_r($dataReq);

                $res = true;
                $res = $this->rscode->response('0000', $res);
                $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
            }
        }
        //
        // Finishing and log it up
        //

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

    public function userblock_post()
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
        $data_json = ($data);
        //var_dump($data);
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => 'user/userblock/',
            'request_method' => 'post',
            'request_content' => serialize($this->post()),
        );

        //
        // 2. Log the service call
        //
        $request_id =
        $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res = true;
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {
            if ($data_json['status'] == 'BLOCK') {
                $paramPost = array(
                    'iduser' => $iduser,
                    'iduser_block' => $data_json['iduser_block'],
                );
                $insertPost = $this->User_model->insert_user_block_user($paramPost, $data_json['expired']);

            } else if ($data_json['status'] == 'UNBLOCK') {

                $paramPost = array(
                    'iduser' => $iduser,
                    'iduser_block' => $data_json['iduser_block'],
                );
                $insertPost = $this->User_model->delete_user_block_user($paramPost);
            }

            $res = $this->rscode->response('0000', $res);
            $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
        }
        //
        // Finishing and log it up
        //

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

    public function userunconnect_post()
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
        $data_json = ($data);
        //var_dump($data);
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => 'user/userunconnect/',
            'request_method' => 'post',
            'request_content' => serialize($this->post()),
        );

        //
        // 2. Log the service call
        //
        $request_id =
        $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res = true;
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {

            $paramPost = array(
                'iduser' => $data_json['iduser'],
                'iduser_friend' => $data_json['iduser_friend'],
            );
            $insertPost = $this->User_model->delete_user_connection($paramPost);

            $paramPost2 = array(
                'iduser_friend' => $data_json['iduser'],
                'iduser' => $data_json['iduser_friend'],
            );
            $insertPost = $this->User_model->delete_user_connection($paramPost2);

            $res = $this->rscode->response('0000', $res);
            $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
        }
        //
        // Finishing and log it up
        //

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

    public function userreport_post()
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
        $data_json = ($data);
        //var_dump($data);
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => 'user/userreport/',
            'request_method' => 'post',
            'request_content' => serialize($this->post()),
        );

        //
        // 2. Log the service call
        //
        $request_id =
        $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res = true;
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {

            $paramPost = array(
                'iduser' => $iduser,
                'iduser_report' => $data_json['iduser_report'],
                'report_reason' => $data_json['reason'],
            );
            $insertPost = $this->User_model->insert_user_report($paramPost);

            $res = $this->rscode->response('0000', $res);
            $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
        }
        //
        // Finishing and log it up
        //

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

    public function profileReminder_get()
    {
        $col = array('*');

        $filter = [];
        // print_r($filter);
        $filter['filter']['email is NULL'] = null;
        $filter['filter']['password is NULL'] = null;
        $data = $this->User_model->get_user($col, $filter);
        if ($data) {

            $gcm = new GCM();
            $modul = 'USER';
            $type = 'USER_PROFILE_REMINDER';
            $body = 'User Profile reminder';

            // 2. cek fcmid and send notif

            $fcmMember = array();
            foreach ($data as $v) {
                $fcmMember[] = $v['iduser'];
            }

            // 3. insert notif

            $filter['filter'] = array('status' => 0, 'fcm_id !=' => '');
            $filter['filterIn'] = array('iduser' => $fcmMember);
            // $filter['group'] = 'iduser';
            $datafcmid = $this->User_model->get_agent_login_session(array('fcm_id', 'iduser'), $filter);

            $fcmid = array();
            if ($datafcmid) {

                foreach ($datafcmid as $c) {
                    // echo json_encode($c) . ' --|';
                    array_push($fcmid, $c['fcm_id']);
                }
                $msgNotif = array("notification" => array("body" => "you haven't completed your klubstory profile, complete it now to get more amazing experience in klubstory.", "title" => $body, "click_action" => "FLUTTER_NOTIFICATION_CLICK"));
                // $msgNotif = '';
                $resultFCM = $gcm->send_notification($fcmid, $msgNotif, array('modul' => $modul, 'type' => $type));
                print_r($resultFCM);
            }

        } else {
            echo 'no user found';
        }

    }

    public function userAdvRequest_post()
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
        $data_json = ($data);
        //var_dump($data);
        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => 'user/userAdvRequest/',
            'request_method' => 'post',
            'request_content' => serialize($this->get()),
        );

        //
        // 2. Log the service call
        //
        $request_id =
        $this->User_model->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res = true;
        $session = true;
        $r = $this->User_model->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res = $this->rscode->response('0102', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {
            // foreach ($data_json['user_invite'] as $v) {
            $postData['iduser'] = $iduser;
            $postData['status_request'] = 'NEW';
            $postData['value'] = json_encode($data_json);
            $insertPost = $this->User_model->insert_userAdvRequest($postData);
            if ($insertPost) {
// send mail

                $k = $this->User_model->get_user(array('*'), array('filter' => array('iduser' => $iduser)));
                $parEmail = array(
                    'email' => 'klubstory@gmail.com',
                    'message' => 'Dear klubstory, Klub ' . $k[0]['fullname'] . ' has sent Advertisement Request',
                    'subject' => 'Advertisement Request',
                );
                $res = $this->_send_email($parEmail);

                $res = true;
            } else {
                $res = false;
            }
            // }

            $res = $this->rscode->response('0000', $res);
            $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
        }
        //
        // Finishing and log it up
        //

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
    public function _postCURL($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $res = curl_exec($ch);
        return $res;

    }
    public function assign_rand_value($num)
    {

        // accepts 1 - 36
        switch ($num) {
            case "1":$rand_value = "a";
                break;
            case "2":$rand_value = "b";
                break;
            case "3":$rand_value = "c";
                break;
            case "4":$rand_value = "d";
                break;
            case "5":$rand_value = "e";
                break;
            case "6":$rand_value = "f";
                break;
            case "7":$rand_value = "g";
                break;
            case "8":$rand_value = "h";
                break;
            case "9":$rand_value = "i";
                break;
            case "10":$rand_value = "j";
                break;
            case "11":$rand_value = "k";
                break;
            case "12":$rand_value = "l";
                break;
            case "13":$rand_value = "m";
                break;
            case "14":$rand_value = "n";
                break;
            case "15":$rand_value = "o";
                break;
            case "16":$rand_value = "p";
                break;
            case "17":$rand_value = "q";
                break;
            case "18":$rand_value = "r";
                break;
            case "19":$rand_value = "s";
                break;
            case "20":$rand_value = "t";
                break;
            case "21":$rand_value = "u";
                break;
            case "22":$rand_value = "v";
                break;
            case "23":$rand_value = "w";
                break;
            case "24":$rand_value = "x";
                break;
            case "25":$rand_value = "y";
                break;
            case "26":$rand_value = "z";
                break;
            case "27":$rand_value = "0";
                break;
            case "28":$rand_value = "1";
                break;
            case "29":$rand_value = "2";
                break;
            case "30":$rand_value = "3";
                break;
            case "31":$rand_value = "4";
                break;
            case "32":$rand_value = "5";
                break;
            case "33":$rand_value = "6";
                break;
            case "34":$rand_value = "7";
                break;
            case "35":$rand_value = "8";
                break;
            case "36":$rand_value = "9";
                break;
        }
        return $rand_value;
    }

    public function get_rand_alphanumeric($length)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $string = '';
        $max = strlen($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, $max)];
        }
        return $string;
    }

    private function datatable($role){
        $this->validate_session([
            'u' => ["label"=>"User", "rules"=>"required",],
            'token' => ["label"=>"Access Token", "rules"=>"required",],
            'limit' => ["label"=>"Pagination", "rules"=>"required",],
        ]);

        $fields = ["u.fullname as company", "p.name", "p.birthdate", "p.gender", "p.phone",];
        $filters = [
            "limit" => $this->request->getGet("limit"),
            "order" => $this->request->getGet("order"),
            "search" => $this->request->getGet("search"),
            "role" => $role,
            "searchable" => $fields,
        ];
        $fields[] = "u.iduser";
        $data = $this->User_model->data($fields, $filters);
        
        return $this->respond(
            tempResponse(
                '00000',
                [
                    'page' => $filters["limit"]["page"],
                    'per_page' => $filters["limit"]["n_item"],
                    'total' => $data->total,
                    'total_pages' => $data->total_pages,
                    'records' => $data->data,
                ]
            )
        );
    }

    public function sampler(){
        return $this->datatable("sampler");
    }

    public function company(){
        return $this->datatable("company");
    }
}
