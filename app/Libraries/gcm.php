<?php

class GCM
{

    private $GOOGLE_API_KEY = "AAAAcPXCAnw:APA91bFFdUT2xZaNJzUqLXyEuMO6Gh2UUZsPkE9XXd3yJHVAfeMYdb4Gj_5IGk44h8zLOOK_7lESat5CLxOFAn7CA06AGrZRpd0j8GjaoLrivVeZhbC00xWxeRQ9YPKasRXoKwAZhSw7";

    //put your code here
    // constructor
    public function __construct()
    {

        $this->_CI = &get_instance();
        $this->_CI->load->model("User_model");
        $this->_CI->load->model("Klub_model");
        $this->_CI->load->model("Otp_model");
        $this->_CI->load->model("Md_model");
        $this->_CI->load->model("Post_model");
        $this->_CI->load->model("Notif_model");
    }

    /**
     * Sending Push Notification Jamaah
     */
    public function send_notification($registration_ids, $notif, $data)
    {
        // print_r($registration_ids);

        // Set POST variables
        $url = 'https://fcm.googleapis.com/fcm/send';
        if (!empty($notif)) {
            $fields = $notif;
            $fields['data'] = $data;
            $fields["registration_ids"] = $registration_ids;
            // "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            $fields['data']['click_action'] = "FLUTTER_NOTIFICATION_CLICK";
            $fields['notification']['android_channel_id'] = 'flutter_notif_id';

        } else {
            $fields = array(

                //"notification" => array( "title" => $message['title'], "body" => $message['body'] ),
                // $notif,
                'data' => $data,
                "registration_ids" => $registration_ids,
            );
        }

        // print_r(json_encode($fields));
        $headers = array(
            'Authorization: key=' . $this->GOOGLE_API_KEY,
            'Content-Type: application/json',
        );

        //print_r(json_encode($fields));
        //print_r($headers);
        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        // Execute post
        $result = curl_exec($ch);
        if ($result === false) {
            die('Curl failed: ' . curl_error($ch));
        }

        // Close connection
        curl_close($ch);
        return $result;
    }

    public function notificationUserget($colomn, $filter)
    {
        $res = $this->_CI->Notif_model->get_notificationUser($colomn, $filter);
        return $res;
    }

    public function notificationget($data, $filter)
    {
        $res = $this->_CI->Notif_model->get_notificationUser($data, $filter);
        return $res;
    }

    public function notificationuserupdate($data, $filter)
    {
        $res = $this->_CI->Notif_model->update_notificationUser($data, $filter);
        return $res;
    }

    public function notificationupdate($colomn, $filter)
    {
        $res = $this->_CI->Notif_model->update_notification($colomn, $filter);
        return $res;
    }

    public function notificationPost($data)
    {
        $insertPost = $this->_CI->Notif_model->insert_notification($data);
        return $insertPost;
    }

    public function notificationUserPost($data)
    {
        $insertPost = $this->_CI->Notif_model->insert_notificationuser($data);
        return $insertPost;
    }

}
