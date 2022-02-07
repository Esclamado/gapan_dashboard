<?php
namespace Lns\Sb\Lib\PushNotification;

class PushNotification {

	public function sendNotification($to, $message, $content, $item_id, $pushAction){
        $API_KEY = "AAAAseUCJDM:APA91bFXoLRwn9FXuCcicd7njHwBy9tR0ioGK1UVQjiIHYiy08nvNItTxYDeTm5hn23vX4lgMyaTdSyKkWsqC0DItIMVUbFllRdQeVqdkHcS8tshk4o1fsykR8NA4h22KEeyw2BfBNG3";
	
        if($pushAction == 'points'){
            $title = 'Congratiolation!';
        }
        elseif ($pushAction == 'survey') {
            $title = 'Match Found!';
        }elseif ($pushAction == 'take'){
            $title = 'Take the Survey';
        }
        else{
            $title = 'Research App 2018';
        }

        // replace API
        define( 'API_ACCESS_KEY', $API_KEY); 
        
        $registrationIds = array($to);
        $msg = array
        (
            // 'message' => $message,
            // 'title' => $title,
            // 'vibrate' => 1,
            'sound' => 'default',
            'body' => $message,
            'title' => $title,
            'click_action' => "FCM_PLUGIN_ACTIVITY",
            'icon' => 'fcm_push_icon',

            // you can also add images, additionalData

        );
        $fields = array
        (
            "notification" => $msg,
            "data" => array('title' => 'Research App', 'body' => $message, 'content' => $content, 'item_id' => $item_id, 'push_action' => $pushAction),
            "registration_ids" => $registrationIds,
            "priority" => "high",
            "restricted_package_name" => "",
        );
        $headers = array
        (
            'Authorization: key=' . $API_KEY,
			'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        curl_close( $ch );
        return $result;
	}
	public function sendNotif($to, $title, $message, $content, $api_key, $pushAction) {
        if (!defined('API_ACCESS_KEY')) {
            define('API_ACCESS_KEY', $api_key);
        }
        /* $registrationIds = array($to); */
        /* $msg = array
        (
            'sound' => 'default',
            'body' => $message,
            'title' => $title,
            'click_action' => "FCM_PLUGIN_ACTIVITY",
            'icon' => 'fcm_push_icon',
            // you can also add images, additionalData
        ); */
        $fields = array(
            'notification' => array(
                'title' => $title,
                'body' => $message,
                'sound' => 'default',
                'click_action' => 'FCM_PLUGIN_ACTIVITY',
                'icon' => 'fcm_push_icon'
            ),
            'data' => array(
                'title' => 'Gapan',
                'body' => $message,
                'content' => $content,
                'item_id' => 1,
                'push_action' => $pushAction
            ),
            'to' => $to,
            'priority' => 'high'
        );
        /* $fields = array
        (
            "notification" => $msg,
            "data" => array('title' => 'Research App', 'body' => $message, 'content' => $content, 'item_id' => 1, 'push_action' => $pushAction),
            "registration_ids" => $registrationIds,
            "priority" => "high",
            "restricted_package_name" => "",
        ); */
        $headers = array
        (
            'Authorization: key=' . $api_key,
			'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        curl_close( $ch );
        return $result;
    }
}
