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
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 5 seconds to connect
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);        // 10 seconds total execution
        
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public static function getTokensByRole($role, $db) {
        if ($role === 'admin') {
            $query = "SELECT push_token FROM admins WHERE push_token IS NOT NULL AND push_token != ''";
        } elseif ($role === 'driver') {
            $query = "SELECT push_token FROM drivers WHERE push_token IS NOT NULL AND push_token != ''";
        } else {
            $query = "SELECT push_token FROM users WHERE role = 'customer' AND push_token IS NOT NULL AND push_token != ''";
        }
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function sendPushToMany($tokens, $title, $body, $data = []) {
        $results = [];
        foreach ($tokens as $token) {
            $results[] = self::sendPush($token, $title, $body, $data);
        }
        return $results;
    }
}
