<?php

namespace Broda\Component\Network\Transport\Stream;

/**
 * Classe StreamContext
 *
 */
class StreamContext
{

    private function __construct()
    {
    }

    public static function create(array $options)
    {
        return stream_context_create(
            $options,
            array(
                "notification" => array(self, 'streamNotificationCallback')
            )
        );
    }

    public static function streamNotificationCallback($notification_code, $severity,
            $message, $message_code, $bytes_transferred, $bytes_max)
    {

        switch ($notification_code) {
            case STREAM_NOTIFY_RESOLVE:
            case STREAM_NOTIFY_AUTH_REQUIRED:
            case STREAM_NOTIFY_COMPLETED:
            case STREAM_NOTIFY_FAILURE:
            case STREAM_NOTIFY_AUTH_RESULT:
                var_dump($notification_code, $severity, $message, $message_code,
                        $bytes_transferred, $bytes_max);
                /* Ignore */
                break;

            case STREAM_NOTIFY_REDIRECTED:
                echo "Being redirected to: ", $message;
                break;

            case STREAM_NOTIFY_CONNECT:
                echo "Connected...";
                break;

            case STREAM_NOTIFY_FILE_SIZE_IS:
                echo "Got the filesize: ", $bytes_max;
                break;

            case STREAM_NOTIFY_MIME_TYPE_IS:
                echo "Found the mime-type: ", $message;
                break;

            case STREAM_NOTIFY_PROGRESS:
                echo "Made some progress, downloaded ", $bytes_transferred, " so far";
                break;
        }
        echo "\n";
    }

}
