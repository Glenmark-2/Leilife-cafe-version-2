<?php
class NotificationHelper {
    public static function sendPush($token, $title, $body, $data = []) {
        if (empty($token)) return;

        $payload = [
            "to" => $token,
            "title" => $title,
            "body" => $body,
            "data" => $data,
            "sound" => "default"
        ];

        $ch = curl_init('https://exp.host/--/api/v2/push/send');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}