<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
require __DIR__ . '/vendor/autoload.php';
include __DIR__ . '/websocket_config.php';

/**
 * Send any incoming messages to all connected clients (except sender)
 */
class Chat implements MessageComponentInterface {
    protected SplObjectStorage $clients;
    protected array $candidates = [];
    protected array $votes = [];
    protected int $time = 0;

    public function __construct() {
        $this->clients = new SplObjectStorage;
        $this->populate();
    }

    public function populate(){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => webAddress.'/api/v1/votes',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'X-Requested-With: XMLHttpRequest'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $resp = json_decode($response, true);
        $this->candidates = $resp['candidates'];
        $this->votes = $resp['votes'];
        $this->time = time();
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        $conn->send(json_encode(['votes'=>$this->votes, 'time'=>$this->time, 'request'=>'request']));
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $msg_arr = json_decode($msg, true);

        if(array_key_exists('token', $msg_arr)){
            $token = $msg_arr['token'];
            $message = $msg_arr['message'];
            if($message == 'candidates'){
                $from->send(json_encode(['candidates'=>$this->candidates, 'time'=>$this->time, 'request'=>'request']));
                return;
            }

            if($message == 'votes'){
                $from->send(json_encode(['votes'=>$this->votes, 'time'=>$this->time, 'request'=>'request']));
                return;
            }

            $resp = $this->vote($message, $token);
            $this->time = time();
            $ids = json_decode($resp, true)['votes'];
            $votes = [];

            foreach ($ids as $id){
                $this->votes[$id] = ++$this->candidates[$id]['votes'];
                $votes[$id] = $this->votes[$id];
            }
            $from->send($resp);
            $vote_str = json_encode(['votes'=>$votes, 'time'=>$this->time, 'request'=>'live']);

            foreach ($this->clients as $client) {
                if ($from != $client) {
                    $client->send($vote_str);
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, Exception $e) {
        $conn->close();
    }

    public function vote(array $vote, string $token): string
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => webAddress.'/api/v1/votes',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('vote' => json_encode($vote)),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$token
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
}

// Run the server application through the WebSocket protocol on port 8080
$app = new Ratchet\App(address, 8080, address);
$app->route('/vote', new Chat, array('*'));
$app->route('/echo', new Ratchet\Server\EchoServer, array('*'));
$app->run();
