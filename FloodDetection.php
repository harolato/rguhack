<?php
class FloodDetection {
    const HOST = "localhost";
    const PORT = 11211;
    private $memcache;
    private $ipAddress;

    private $timeLimitUser = array (
            "DEFAULT" => 2,
            "CHAT" => 3,
            "LOGIN" => 4 
    );
    private $timeLimitProcess = array (
            "DEFAULT" => 0.1,
            "CHAT" => 1.5,
            "LOGIN" => 0.1 
    );

    function __construct() {
        $this->memcache = new Memcache ();
        $this->memcache->connect ( self::HOST, self::PORT );
    }

    function addUserlimit($key, $time) {
        $this->timeLimitUser [$key] = $time;
    }

    function addProcesslimit($key, $time) {
        $this->timeLimitProcess [$key] = $time;
    }

    public function quickIP() {
        return (empty ( $_SERVER ['HTTP_CLIENT_IP'] ) ? (empty ( $_SERVER ['HTTP_X_FORWARDED_FOR'] ) ? $_SERVER ['REMOTE_ADDR'] : $_SERVER ['HTTP_X_FORWARDED_FOR']) : $_SERVER ['HTTP_CLIENT_IP']);
    }

    public function check($action = "DEFAULT") {
        $ip = $this->quickIP ();
        $ipKey = "flood" . $action . sha1 ( $ip );

        $runtime = $this->memcache->get ( 'floodControl' );
        $iptime = $this->memcache->get ( $ipKey );

        $limitUser = isset ( $this->timeLimitUser [$action] ) ? $this->timeLimitUser [$action] : $this->timeLimitUser ['DEFAULT'];
        $limitProcess = isset ( $this->timeLimitProcess [$action] ) ? $this->timeLimitProcess [$action] : $this->timeLimitProcess ['DEFAULT'];

        if ((microtime ( true ) - $iptime) < $limitUser) {
            print ("Die! Die! Die! $ip") ;
            exit ();
        }

        // Limit All request
        if ((microtime ( true ) - $runtime) < $limitProcess) {
            print ("All of you Die! Die! Die! $ip") ;
            exit ();
        }

        $this->memcache->set ( "floodControl", microtime ( true ) );
        $this->memcache->set ( $ipKey, microtime ( true ) );
    }

}
?>