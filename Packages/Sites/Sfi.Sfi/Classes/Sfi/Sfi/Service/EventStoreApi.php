<?php
namespace Sfi\Sfi\Service;

class EventStoreApi {
    public function getPending($type) {
        return json_decode($this->callAPI('GET', 'http://eventstore:2113/projection/pendingEmails-' . $type . '/result'), true) ?? [];
    }

    public function registerEmailSent($reason, $type, $email) {
        $data = [[
            'eventId' => $this->createGUID(),
            'eventType' => 'EmailSent',
            'data' => [
                'reason' => $reason,
                'type' => $type,
                'email' => $email
            ]
        ]];
        return $this->callAPI('POST', 'http://eventstore:2113/streams/data', $data);
    }

    public function registerUnsubscribe($hash) {
        $data = [[
            'eventId' => $this->createGUID(),
            'eventType' => 'SubscriberUnsubscribed',
            'data' => [
                'hash' => $hash
            ]
        ]];
        return $this->callAPI('POST', 'http://eventstore:2113/streams/data', $data);
    }

    protected function createGUID()
    {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    protected function callAPI($method, $url, $data = false)
    {
        $curl = curl_init();


        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                
                if ($data) {
                    $dataString = json_encode($data);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $dataString);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array(                                                                          
                        'Content-Type: application/vnd.eventstore.events+json',                                                                                
                        'Content-Length: ' . strlen($dataString))                                                                       
                    );
                }
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data) {
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }
}


