<?php

defined('BASEPATH') or exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions

require APPPATH . '/libraries/REST_Controller.php';

class Notification extends REST_Controller
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
        //print_r($this->methods);
        $this->load->model("User_model");
        $this->load->model("Klub_model");
        $this->load->model("Notif_model");
        $this->load->model("Post_model");
        $this->load->model("Event_model");
        $this->load->model("Poll_model");
        $this->load->model("Md_model");
        $this->load->library('Rscode');

        $this->_exec_time_start = microtime(true);
        setlocale(LC_MONETARY, 'en_GB');
    }

//get post detail
    public function notification_get()
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
            'service' => '/notification/notification',
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
                } else if ($key == 'name') {
                    unset($filter['filter'][$key]);
                } else if ($value == 'null') {
                    $filter['filter'][$key] = null;
                }
            }
            // $filter['sort']['idnotification'] = 'DESC';
            $getData = $this->Notif_model->get_notification($col, array('filter' => array('iduser' => $iduser), 'sort' => array('idnotification' => 'DESC')));
            // print_r($getData);

            if ($getData != null) {
                $data = array();
                if ($this->get('detail') == 'yes') {
                    foreach ($getData as $k => $v) {

                        $userreceiver = $v['iduser'];
                        if ($v['modul'] == 'POST') {

                            $col = array('*');
                            $filter['filter'] = array(
                                "idpost" => $v['idpost'],
                            );

                            $dataModul = $this->Post_model->get_post($col, $filter);
                            if ($v['type'] == 'POST_COMMENT' || $v['type'] == 'POST_COMMENT_REPLY') {
                                if (!empty($v['related_id'])) {
                                    $usersender = $v['related_id'];
                                } else {
                                    $usersender = $dataModul[0]['iduser'];
                                }
                            } else {
                                $usersender = $dataModul[0]['iduser'];
                            }

                        } elseif ($v['modul'] == 'EVENT') {

                            $col = array('*');
                            $filterOr['filter'] = array(
                                "idevent" => $v['idevent'],
                            );

                            $dataModul = $this->Event_model->get_event($col, $filterOr);
                            $usersender = $dataModul[0]['iduser_creator'];
                        } elseif ($v['modul'] == 'POLL') {

                            $col = array('*');
                            $filterOr['filter'] = array(
                                "idpoll" => $v['idpoll'],
                            );

                            $dataModul = $this->Poll_model->get_poll_detail($col, $filterOr);
                            // $usersender = $dataModul[0]['iduser_creator'];

                            $usersender = $v['related_id'];

                        } elseif ($v['modul'] == 'MARKET') {
                            $usersender = $v['related_id'];
                            // $postInsertnotifUser['market'] = (int) $notificationuserget[0]['market'] + 1;
                        } else {
                            if ($v['type'] == 'USER_GRANT_CREATE_KLUB') {
                                $related = explode(',', $v['related_id']);
                                $usersender = $related[0];
                                $userreceiver = $related[1];
                            } else {
                                $usersender = $v['related_id'];
                                // $userreceiver = $v['iduser'];
                            }

                        }

                        $sender = $this->User_model->get_user(array('*'), array("filter" => array('iduser' => $usersender)));
                        if ($sender) {
                            $v['sender'] = $sender[0];
                        }

                        $receiver = $this->User_model->get_user(array('*'), array("filter" => array('iduser' => $userreceiver)));
                        if ($receiver) {
                            $v['receiver'] = $receiver[0];
                        }

                        if ($v['idklub'] != 0 || $v['idklub'] != '') {
                            $dataklub = $this->Klub_model->get_klub($col, array('filter' => array('idklub' => $v['idklub'])));
                            if ($dataklub) {

                                $v['idklub'] = $dataklub[0];
                            }
                        }

                        // array_push($data['list_notif'][], $v);
                        $data['list_notif'][] = $v;
                    }
                }
                $counter = $this->Notif_model->get_notificationuser($col, array('filter' => array('iduser' => $iduser)));
                if ($counter) {
                    foreach ($counter as $k => $vc) {
                        foreach ($vc as $kcc => $vv) {
                            $counter[$k][$kcc] = $vv > 0 ? (int) $vv : 0;
                        }
                    }
                }
                $data['counter'] = $counter;
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

    public function notificationViewBulk_post()
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
            'service' => 'notif/notificationViewBulk/',
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
            $filterUserGetNotif = array('filter' => array(
                'iduser' => $iduser,
            ));
            $notificationuserget = $this->Notif_model->get_notificationuser(array('*'), $filterUserGetNotif);
            // print_r($notificationuserget);
            if ($notificationuserget) {

                $filterGetNotif = array('filter' => array(
                    'iduser' => $iduser,
                    'is_viewed' => 'N',
                    'is_action' => 'N',
                ));

                if ($_POST['modul'] == 'POST') {
                    $filterGetNotif['filter']['modul'] = $_POST['modul'];
                } elseif ($_POST['modul'] == 'EVENT') {
                    $filterGetNotif['filter']['modul'] = $_POST['modul'];
                } elseif ($_POST['modul'] == 'POLL') {
                    $filterGetNotif['filter']['modul'] = $_POST['modul'];
                } elseif ($_POST['modul'] == 'MARKET') {
                    $filterGetNotif['filter']['modul'] = $_POST['modul'];
                } elseif ($_POST['modul'] == 'KLUB') {
                    $filterGetNotif['filter']['modul'] = $_POST['modul'];
                } elseif ($_POST['modul'] == 'BELL') {
                    $filterGetNotif['filternotin']['modul'] = array('POST_TO_KLUB', 'POST_SHARE_TO_KLUB');
                }
                $postNOtif = (int) $notificationuserget[0]['post'];
                $eventNOtif = (int) $notificationuserget[0]['event'];
                $pollNOtif = (int) $notificationuserget[0]['poll'];
                $marketNOtif = (int) $notificationuserget[0]['market'];
                $klubNOtif = (int) $notificationuserget[0]['klub'];
                $bellNOtif = (int) $notificationuserget[0]['bell'];
                // post position : bell/post/poll/event/klub/market

                $notificationget = $this->Notif_model->get_notification(array('*'), $filterGetNotif);
                if ($notificationget) {
                    // print_r($notificationget);
                    foreach ($notificationget as $v) {

                        $paramPost2 = array(
                            'is_viewed' => 'Y',
                            'iduser' => $iduser,
                        );

                        $insertPost = $this->Notif_model->update_notification($paramPost2, array('idnotification' => $v['idnotification']));

                        if ($v['modul'] == 'POST') {
                            $postNOtif = $postNOtif - 1;
                            $bellNOtif = $bellNOtif - 1;
                            $dataNotifUserUpdate['post'] = (int) $postNOtif;
                            $dataNotifUserUpdate['bell'] = (int) $bellNOtif;
                        } elseif ($v['modul'] == 'EVENT') {
                            $eventNOtif = $eventNOtif - 1;
                            $bellNOtif = $bellNOtif - 1;
                            $dataNotifUserUpdate['event'] = (int) $eventNOtif;
                            $dataNotifUserUpdate['bell'] = (int) $bellNOtif;
                        } elseif ($v['modul'] == 'POLL') {
                            $pollNOtif = $pollNOtif - 1;
                            $bellNOtif = $bellNOtif - 1;
                            $dataNotifUserUpdate['poll'] = (int) $pollNOtif;
                            $dataNotifUserUpdate['bell'] = (int) $bellNOtif;
                        } elseif ($v['modul'] == 'MARKET') {
                            $merketNOtif = $merketNOtif - 1;
                            $bellNOtif = $bellNOtif - 1;
                            $dataNotifUserUpdate['market'] = (int) $merketNOtif;
                            $dataNotifUserUpdate['bell'] = (int) $bellNOtif;
                        } elseif ($v['modul'] == 'KLUB') {
                            $klubNOtif = $klubNOtif - 1;
                            $bellNOtif = $bellNOtif - 1;
                            $dataNotifUserUpdate['klub'] = (int) $klubNOtif;
                            $dataNotifUserUpdate['bell'] = (int) $bellNOtif;
                        } elseif ($v['modul'] == 'BELL') {
                            $postNOtif = $postNOtif - 1;
                            $eventNOtif = $eventNOtif - 1;
                            $bellNOtif = $bellNOtif - 1;
                            $pollNOtif = $pollNOtif - 1;
                            $merketNOtif = $merketNOtif - 1;
                            $klubNOtif = $klubNOtif - 1;

                            $dataNotifUserUpdate['post'] = (int) $postNOtif;
                            $dataNotifUserUpdate['event'] = (int) $eventNOtif;
                            $dataNotifUserUpdate['poll'] = (int) $pollNOtif;
                            $dataNotifUserUpdate['market'] = (int) $merketNOtif;
                            $dataNotifUserUpdate['klub'] = (int) $klubNOtif;
                            $dataNotifUserUpdate['bell'] = (int) $bellNOtif;
                        }

                        if ($dataNotifUserUpdate) {
                            foreach ($dataNotifUserUpdate as $k => $v) {
                                if ($v < 0) {
                                    $dataNotifUserUpdate[$k] = 0;
                                }
                            }
                            $insertPost = $this->Notif_model->update_notificationUser($dataNotifUserUpdate, array('iduser' => $iduser));

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

    public function notificationActionBulk_post()
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
            'service' => 'notif/notificationActionBulk/',
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
            $filterUserGetNotif = array('filter' => array(
                'iduser' => $iduser,
            ));
            $notificationuserget = $this->Notif_model->get_notificationuser(array('*'), $filterUserGetNotif);

            if ($notificationuserget) {

                $filterGetNotif = array('filter' => array(
                    'iduser' => $iduser,
                    // 'is_view' => 'N',
                    'is_action' => 'N',
                ));

                if ($_POST['modul'] == 'POST') {
                    $filterGetNotif['filter']['modul'] = $_POST['modul'];
                } elseif ($_POST['modul'] == 'EVENT') {
                    $filterGetNotif['filter']['modul'] = $_POST['modul'];
                } elseif ($_POST['modul'] == 'POLL') {
                    $filterGetNotif['filter']['modul'] = $_POST['modul'];
                } elseif ($_POST['modul'] == 'MARKET') {
                    $filterGetNotif['filter']['modul'] = $_POST['modul'];
                } elseif ($_POST['modul'] == 'KLUB') {
                    $filterGetNotif['filter']['modul'] = $_POST['modul'];
                } elseif ($_POST['modul'] == 'BELL') {
                    $filterGetNotif['filternotin']['modul'] = array('POST_TO_KLUB', 'POST_SHARE_TO_KLUB');
                }

                $postNOtif = (int) $notificationuserget[0]['post'];
                $eventNOtif = (int) $notificationuserget[0]['event'];
                $pollNOtif = (int) $notificationuserget[0]['poll'];
                $marketNOtif = (int) $notificationuserget[0]['market'];
                $klubNOtif = (int) $notificationuserget[0]['klub'];
                $bellNOtif = (int) $notificationuserget[0]['bell'];
                // post position : bell/post/poll/event/klub/market

                $notificationget = $this->Notif_model->get_notification(array('*'), $filterGetNotif);
                if ($notificationget) {
                    foreach ($notificationget as $v) {

                        $paramPost2 = array(
                            'is_viewed' => 'Y',
                            'is_action' => 'Y',
                            'iduser' => $iduser,
                        );

                        $insertPost = $this->Notif_model->update_notification($paramPost2, array('idnotification' => $v['idnotification']));

                        if ($v['modul'] == 'POST') {
                            $postNOtif = $postNOtif - 1;
                            $bellNOtif = $bellNOtif - 1;
                            $dataNotifUserUpdate['post'] = (int) $postNOtif;
                            $dataNotifUserUpdate['bell'] = (int) $bellNOtif;
                        } elseif ($v['modul'] == 'EVENT') {
                            $eventNOtif = $eventNOtif - 1;
                            $bellNOtif = $bellNOtif - 1;
                            $dataNotifUserUpdate['event'] = (int) $eventNOtif;
                            $dataNotifUserUpdate['bell'] = (int) $bellNOtif;
                        } elseif ($v['modul'] == 'POLL') {
                            $pollNOtif = $pollNOtif - 1;
                            $bellNOtif = $bellNOtif - 1;
                            $dataNotifUserUpdate['poll'] = (int) $pollNOtif;
                            $dataNotifUserUpdate['bell'] = (int) $bellNOtif;
                        } elseif ($v['modul'] == 'MARKET') {
                            $merketNOtif = $merketNOtif - 1;
                            $bellNOtif = $bellNOtif - 1;
                            $dataNotifUserUpdate['market'] = (int) $merketNOtif;
                            $dataNotifUserUpdate['bell'] = (int) $bellNOtif;
                        } elseif ($v['modul'] == 'KLUB') {
                            $klubNOtif = $klubNOtif - 1;
                            $bellNOtif = $bellNOtif - 1;
                            $dataNotifUserUpdate['klub'] = (int) $klubNOtif;
                            $dataNotifUserUpdate['bell'] = (int) $bellNOtif;
                        }

                        if ($dataNotifUserUpdate) {
                            if ($_POST['modul'] != 'BELL' & $v['is_viewed'] == 'N') {
                                $insertPost = $this->Notif_model->update_notificationUser($dataNotifUserUpdate, array('iduser' => $iduser));

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
    public function notificationAction_post()
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
            'service' => 'notif/notificationAction/',
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

            $filterGetNotif = array('filter' => array(
                'idnotification' => $_POST['idnotification'],
            ));

            $notificationget = $this->Notif_model->get_notification(array('*'), $filterGetNotif);
            if ($notificationget) {
                foreach ($notificationget as $v) {

                    $paramPost2 = array(
                        'is_viewed' => 'Y',
                        'is_action' => 'Y',
                        'iduser' => $iduser,
                    );

                    $insertPost = $this->Notif_model->update_notification($paramPost2, array('idnotification' => $v['idnotification']));

                    // if ($v['modul'] == 'POST') {
                    //     $dataNotifUserUpdate['post'] = (int) $notificationuserget[0]['post'] - 1;
                    // } elseif ($v['modul'] == 'EVENT') {
                    //     $dataNotifUserUpdate['event'] = (int) $notificationuserget[0]['event'] - 1;
                    // } elseif ($v['modul'] == 'POLL') {
                    //     $dataNotifUserUpdate['poll'] = (int) $notificationuserget[0]['poll'] - 1;
                    // } elseif ($v['modul'] == 'MARKET') {
                    //     $dataNotifUserUpdate['market'] = (int) $notificationuserget[0]['market'] - 1;
                    // } elseif ($v['modul'] == 'KLUB') {
                    //     $dataNotifUserUpdate['klub'] = (int) $notificationuserget[0]['klub'] + 1;
                    // } elseif ($v['modul'] == 'BELL') {
                    //     $dataNotifUserUpdate['post'] = (int) $notificationuserget[0]['post'] - 1;
                    //     $dataNotifUserUpdate['event'] = (int) $notificationuserget[0]['event'] - 1;
                    //     $dataNotifUserUpdate['poll'] = (int) $notificationuserget[0]['poll'] - 1;
                    //     $dataNotifUserUpdate['market'] = (int) $notificationuserget[0]['market'] - 1;
                    //     $dataNotifUserUpdate['klub'] = (int) $notificationuserget[0]['klub'] - 1;
                    //     $dataNotifUserUpdate['bell'] = (int) $notificationuserget[0]['bell'] - 1;
                    // }

                    // $insertPost = $this->Notif_model->get_notificationUser($paramPost2, array('iduser' => $iduser));

                }
            }
            $res = true;

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

    public function Create_tree()
    {

        // $data = $this->Admin_tree_model->get_data('all', null);

        $itemsByReference = array();

        // Build array of item references:
        foreach ($data as $key => &$item) {
            $itemsByReference[$item['id']] = &$item;
            // Children array:
            $itemsByReference[$item['id']]['children'] = array();
            // Empty data class (so that json_encode adds "data: {}" )
            //      $itemsByReference[$item['t_datagroup_ID']]['data'] = new StdClass();
        }

        // Set items as children of the relevant parent item.
        foreach ($data as $key => &$item) {
            if ($item['parent_ID'] && isset($itemsByReference[$item['parent_ID']])) {
                $itemsByReference[$item['parent_ID']]['children'][] = &$item;
            }
        }

        // Remove items that were added to parents elsewhere:
        foreach ($data as $key => &$item) {
            if ($item['parent_ID'] && isset($itemsByReference[$item['parent_ID']])) {
                unset($data[$key]);
            }

        }

        // Encode:
        $json = json_encode($data);
        print_r($json);
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
