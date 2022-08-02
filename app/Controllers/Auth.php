<?php 

namespace App\Controllers;
 
 use CodeIgniter\RESTful\ResourceController;
 use CodeIgniter\API\ResponseTrait;
 use App\Models\AuthModel;
 use App\Models\SamplersModel;
  

class Auth extends ResourceController
{
    private $_exec_time_start;

    public function __construct()
    {
        $this->request = \Config\Services::request();

        $this->authModel  = new AuthModel();
        $this->samplersModel  = new SamplersModel();

        helper(['custom', 'rsCode']);
        
        $this->_exec_time_start = microtime(true);
        setlocale(LC_MONETARY, 'en_GB');
        date_default_timezone_set('Asia/Jakarta');
    }

    function test(){
        // echo 'asdsad';
        // echo uniqINT();
        echo encrypt_decrypt_login('encrypt', '123456');
    }

    public function login()
    {
        //
        // 1. Get the parameters
        //
        // (type, msisdn, otp-code, platform, fcm_id)

        $post_data = $this->request->getPost();
        // print_r($post_data);

        $platform = $post_data['platform'];
        $fcmid = $post_data['fcm_id'];
        $location = $post_data['location'];
        $role = '';

        $data = $this->request->getPost();
        $data_json = $data;
        $data = array(
            'platform' => $platform,
            'location' => $location,
            'service' => '/auth/login',
            'request_method' => 'POST',
            'request_content' => serialize($data),
        );

        //
        // 2. Log the service call
        //

        $request_id = $this->authModel->insert_user_access_log_request($data);
            if($post_data['login_type'] == 'SSO'){
                $filterUser = array(
                    'email' => $this->request->getPost('email'),
                    // 'related_key' => $this->request->getPost('role'),
                    
                    // 'password' => encrypt_decrypt_login('encrypt', $this->request->getPost('password')),
                );
            }else{
                $filterUser = array(
                    'email' => $this->request->getPost('email'),
                    'password' => encrypt_decrypt_login('encrypt', $this->request->getPost('password')),
                    // 'related_key' => $this->request->getPost('role'),
                );
            }
               
            $res = $this->authModel->login($filterUser, $platform, $fcmid, $role);
            if ($res) {
                $res = tempResponse('00000', $res);
                
                $session = false;
                // print_r($res);
                return $this->respond($res); // CREATED (201) being the HTTP response code
            } else {
                $res = tempResponse('00001', null);
                $session = false;
                return $this->respond($res); // CREATED (201) being the HTTP response code
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
        $this->authModel->insert_user_access_log_response($data);

    }

    public function logout()
    {
        //
        // 1. Get the parameters
        //
        $post_data = ($this->request->getPost());

        $iduser = $post_data['iduser'];
        $access_token = $post_data['access_token'];
        $platform = $post_data['platform'];

        $res = $this->authModel->logout($iduser, $access_token);

        if ($res > 0) {

            $res =tempResponse('00000', true);
            $session = false;
            return $this->respond($res); // CREATED (201) being the HTTP response code
        } else {

            $res =tempResponse('0099', false);
            $session = false;
            return $this->respond($res); // CREATED (201) being the HTTP response code
        }
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
            'service' => '/auth/fcmUpdate',
            'request_method' => 'put',
            'request_content' => serialize($this->request->getPost()),
        );

        //
        // 2. Log the service call
        //
        $request_id =
        $this->authModel->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res;
        $session = true;
        $r = $this->authModel->update_user_access_login_session($iduser, $access_token, $platform, $data_json);
        // print_r($r);
        if ($r == 0) {
            // $res =tempResponse('00102', null);
            $res =tempResponse('0102', false);

            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {

            $res =tempResponse('00000', true);
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
        $this->authModel->insert_user_access_log_response($data);
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
            'service' => '/auths/recoverpassword/',
            'request_method' => 'put',
            'request_content' => serialize($this->get()),
        );

        //
        // 2. Log the service call
        //
        $request_id =
        $this->authModel->insert_user_access_log_request($data);

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
            $res = $this->authModel->update_user($dataUpdate, $filter);

            // print_r($res);
            if ($res == '1') {

                // Update OTP Claimed
                $updateOtp = $this->Otp_model->update_login_otp($dataUpdateOtp, $filterupdate);

                $res =tempResponse('00000', true);
                $session = false;
                $res_code = REST_Controller::HTTP_UNAUTHORIZED;
                $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
            } else if ($res == '0') {

                $res =tempResponse('0111', null);
                $session = false;
                $res_code = REST_Controller::HTTP_UNAUTHORIZED;
                $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
            } else if ($res == '-1') {

                $res =tempResponse('0108', null);
                $session = false;
                $res_code = REST_Controller::HTTP_UNAUTHORIZED;
                $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
            }

            // }
        } else {
            $res =tempResponse('0107', null);
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
        $this->authModel->insert_user_access_log_response($data);
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
        $this->authModel->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res;
        $session = true;
        $r = $this->authModel->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res =tempResponse('0102', null);
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

            $res = $this->authModel->update_user($datapass, $filter);
            if ($res == 1) {
                $res = true;
            } else {
                $res = false;
            }
            //        $data = $this->request->getPost('report_image');
            //        $data = urldecode($data);
            //        $data_json = json_decode($data, true);
            //        $status = "";

            //
            //        if ($res['errorcode'] == '00000' && $res2['errorcode'] == '00000') {
            //            $this->set_response($res, REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code
            //        }

            $res =tempResponse('00000', $res);
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
        $this->authModel->insert_user_access_log_response($data);
    }

    public function register_post()
    {
        $errorcode = 0;

        //
        // 1. Get the parameters
        //
        $iduser = $this->get('iduser');
        $access_token = $this->get('access_token');
        $platform = $this->get('platform');
        $location = $this->get('location');
        $data = $this->request->getPost();
//        $data_json = json_decode($data, true);
        $data_json = $data;

        $data = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'location' => $location,
            'service' => '/users/',
            'request_method' => 'POST',
            'request_content' => serialize($data_json),
        );
        //
        // 2. Log the service call
        //
        $request_id =
        $this->authModel->insert_user_access_log_request($data);

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

        $is_exists = $this->authModel->get_user('*', $filter);

//        $r = $this->authModel->update_user_access_login_session($iduser, $access_token, $platform);
        if ($is_exists) {
            $res =tempResponse('0103', null);
            $session = false;
            $res_code = REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($res, $res_code); // CREATED (201) being the HTTP response code
        } else {
            if (!isset($data_json['register_type']) || $this->request->getPost('register_type') == 'NONSSO') {
                // 4. check otp-code /otp/signup/check/
                // print_r($data_json);
                $url = $url = config_item('url_lokal');
                $url .= '/otp/signup_check';
                $res = json_decode($this->_postCURL($url, array('email' => $data_json['email'], 'otp_code' => $data_json['otp_code'])), true);
                // print_r($res);
                if ($res['errorcode'] == '00000') {

                    // 5. insert data user
                    $data = array(
                        'username' => $this->get_rand_alphanumeric(7),
                        'fullname' => $data_json['fullname'],
                        'email' => $data_json['email'],
                        'password' => $this->encrypt_decrypt_login('encrypt', $this->request->getPost('password')),
                        'register_type' => 'NONSSO',
                        'user_privacy' => 'PUBLIC',
                        'idmd_country' => $data_json['country'],
                        'qrcode_path' => '',
                        'pic_profile_url' => 'http://api.klubstory.com/assets/1/pp/avatar_Male circle.png',
                    );
                    $iduser = $this->authModel->insert_user($data);

                    // Generate QR
                    // $dataUpdate = array('qrcode_path' => $this->qrcodeGenerate->generateQr('profile', $iduser));

                    // $filterUpdate['iduser'] = $iduser;
                    // $updateUser = $this->authModel->update_user($dataUpdate, $filterUpdate);

                    // create_session($userid, $platform, $fcmid)   6. post fcm
                    if ($iduser) {
                        $access_token = $this->authModel->create_session($iduser, $data_json['platform'], $data_json['fcm_id']);

                        // insert klubstoday

                        $insertMember = $this->Klub_model->insert_klubMember(array('idklub' => 3, 'iduser' => $iduser, 'role' => 'MEM', 'status' => 'A'));
                        for ($i = 164; $i <= 193; $i++) {
                            $insertMember = $this->Klub_model->insert_klubMember(array('idklub' => $i, 'iduser' => $iduser, 'role' => 'MEM', 'status' => 'A'));
                        }
                        $insertuserNotificationConfig = $this->Notif_model->insert_userNotificationConfig(array('iduser' => $iduser, 'post' => 'YES', 'event' => 'YES', 'poll' => 'YES', 'market' => 'YES'));

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
                            $opr[$vd] = array('start' => '08:00:00', 'end' => '17:00:00');
                            // unset($_POST[$vd]);
                        }
                        $postStore['operational'] = json_encode($opr);

                        // $store = $this->Market_model->get_store($store = '');
                        // // print_r($store);
                        // if ($store) {
                        //     $idstore = $store[0]['idstore'] + 1;
                        // } else {
                        $idstore = $iduser;
                        // }
                        $postStore['idstore'] = $idstore;
                        $postStore['iduser'] = $idstore;
                        $postStore['name'] = $data_json['fullname'];
                        $postStore['idmd_country'] = $data_json['country'];
                        // $postStore['idmd_city'] = $idstore;

                        $insertStore = $this->Market_model->insert_store($postStore);

                        if (!file_exists('./assets/' . $iduser)) {
                            mkdir('./assets/' . $iduser, 0777, true);
                        }
                        if (!file_exists('./assets/' . $iduser . '/pp')) {
                            mkdir('./assets/' . $iduser . '/pp', 0777, true);
                        }
                        if (!file_exists('./assets/' . $iduser . '/post')) {
                            mkdir('./assets/' . $iduser . '/post', 0777, true);
                        }
                    }

                    if ($access_token) {

                        $filter['filter']['iduser'] = $iduser;
                        $is_exists = $this->authModel->get_user('*', $filter);

                        $data = array(
                            'access_token' => $access_token,
                            'user_profile' => $is_exists,
                        );
                        $res_code = REST_Controller::HTTP_OK;
                        $res =tempResponse('00000', $data);
                        $this->set_response($res, $res_code);
                    } else {
                        $res_code = REST_Controller::HTTP_OK;
                        $res =tempResponse('0002', null);
                        $this->set_response($res, $res_code);
                    }

                } else { ////verification code not valid
                    $res_code = REST_Controller::HTTP_OK;
                    $res =tempResponse('0171', null);
                    $this->set_response($res, $res_code);
                }
            } elseif ($data_json['register_type'] == 'SSO') {

                // 5. insert data user
                $data = array(
                    'username' => $this->get_rand_alphanumeric(7),
                    'fullname' => $data_json['fullname'],
                    'email' => $data_json['email'],
                    'user_privacy' => 'PUBLIC',
                    'idmd_country' => $data_json['country'],
                    'register_type' => $data_json['register_type'],
                    'qrcode_path' => '',
                    'pic_profile_url' => 'http://api.klubstory.com/assets/1/pp/avatar_Male circle.png',
                );
                $iduser = $this->authModel->insert_user($data);

                // Generate QR
                // $dataUpdate = array('qrcode_path' => $this->qrcodeGenerate->generateQr('profile', $iduser));

                // $filterUpdate['iduser'] = $iduser;
                // $updateUser = $this->authModel->update_user($dataUpdate, $filterUpdate);

                // create_session($userid, $platform, $fcmid)   6. post fcm
                if ($iduser) {
                    $access_token = $this->authModel->create_session($iduser, $data_json['platform'], $data_json['fcm_id']);

                    // insert klubstoday

                    $insertMember = $this->Klub_model->insert_klubMember(array('idklub' => 3, 'iduser' => $iduser, 'role' => 'MEM', 'status' => 'A'));
                    $insertuserNotificationConfig = $this->Notif_model->insert_userNotificationConfig(array('iduser' => $iduser, 'post' => 'YES', 'event' => 'YES', 'poll' => 'YES', 'market' => 'YES'));
                    for ($i = 164; $i <= 193; $i++) {
                        $insertMember = $this->Klub_model->insert_klubMember(array('idklub' => $i, 'iduser' => $iduser, 'role' => 'MEM', 'status' => 'A'));
                    }
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
                        $opr[$vd] = array('start' => '08:00:00', 'end' => '17:00:00');
                        // unset($_POST[$vd]);
                    }
                    $postStore['operational'] = json_encode($opr);

                    // $store = $this->Market_model->get_store($store = '');
                    // // print_r($store);
                    // if ($store) {
                    //     $idstore = $store[0]['idstore'] + 1;
                    // } else {
                    $idstore = $iduser;
                    // }
                    $postStore['idstore'] = $idstore;
                    $postStore['iduser'] = $idstore;
                    $postStore['name'] = $data_json['fullname'];
                    // $postStore['idmd_country'] = $idstore;
                    // $postStore['idmd_city'] = $idstore;

                    $insertStore = $this->Market_model->insert_store($postStore);

                    if (!file_exists('./assets/' . $iduser)) {
                        mkdir('./assets/' . $iduser, 0777, true);
                    }
                    if (!file_exists('./assets/' . $iduser . '/pp')) {
                        mkdir('./assets/' . $iduser . '/pp', 0777, true);
                    }
                    if (!file_exists('./assets/' . $iduser . '/post')) {
                        mkdir('./assets/' . $iduser . '/post', 0777, true);
                    }
                }

                if ($access_token) {

                    $filter['filter']['iduser'] = $iduser;
                    $is_exists = $this->authModel->get_user('*', $filter);

                    $data = array(
                        'access_token' => $access_token,
                        'user_profile' => $is_exists,
                    );
                    $res_code = REST_Controller::HTTP_OK;
                    $res =tempResponse('00000', $data);
                    $this->set_response($res, $res_code);
                } else {
                    $res_code = REST_Controller::HTTP_OK;
                    $res =tempResponse('0002', null);
                    $this->set_response($res, $res_code);
                }
            }
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
        $this->authModel->insert_user_access_log_response($data);
    }
    /*

    // GET Function

     */

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
        $request_id = $this->authModel->insert_user_access_log_request($data);

        //
        // 3. Check access token
        //
        $res_code;
        $res;
        $session = true;
        $r = $this->authModel->update_user_access_login_session($iduser, $access_token, $platform);
        if ($r == 0) {
            $res =tempResponse('0102', null);
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
            // $data = $this->authModel->get_user($col, $filter);

            if ($data != null) {
                $res_code = REST_Controller::HTTP_OK;
                $res =tempResponse('00000', $data);
                $this->set_response($res, $res_code);
            } else {
                $res_code = REST_Controller::HTTP_OK;
                $res =tempResponse('0001', null);
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
        $this->authModel->insert_user_access_log_response($data);
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
}
