<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../helpers/EnvLoader.php';

class RealtimeService
{
    private static $pusher = null;

    private static function init()
    {
        if (self::$pusher !== null) {
            return;
        }

        EnvLoader::load(__DIR__ . '/../../.env');

        $options = [
            'cluster' => getenv('PUSHER_CLUSTER') ?: 'ap1',
            'useTLS' => true
        ];

        self::$pusher = new Pusher\Pusher(
            getenv('PUSHER_KEY'),
            getenv('PUSHER_SECRET'),
            getenv('PUSHER_APP_ID'),
            $options
        );
    }

    /**
     * Trigger a real-time event
     * 
     * @param string $channel The channel name (e.g., 'orders', 'user-1')
     * @param string $event The event name (e.g., 'status-updated')
     * @param array $data The data to send
     */
    public static function trigger($channel, $event, $data)
    {
        try {
            self::init();
            
            // Check if credentials exist before triggering
            if (empty(getenv('PUSHER_KEY'))) {
                error_log("Pusher Error: PUSHER_KEY is not set in .env");
                return false;
            }

            return self::$pusher->trigger($channel, $event, $data);
        } catch (Exception $e) {
            error_log("Pusher Trigger Error: " . $e->getMessage());
            return false;
        }
    }
}
