<?php
/**
 * Vpopmail_Base 
 * 
 * @uses Framework_User
 * @package ToasterAdmin
 * @version $id$
 * @copyright 2006-2007 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @author Rick Widmer
 * @license PHP 3.01  {@link http://www.php.net/license/3_01.txt}
 */

/**
 * Vpopmail_Base 
 * 
 * @uses Framework_User
 * @package ToasterAdmin
 * @version $id$
 * @copyright 2006-2007 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @author Rick Widmer
 * @license PHP 3.01  {@link http://www.php.net/license/3_01.txt}
 */
class Vpopmail_Base  extends Framework_User {
    /**
     * address 
     * 
     * Address of vpopmaild host
     * 
     * @var mixed
     * @access public
     */
    public $address = 'localhost';
    /**
     * port 
     * 
     * port of vpopmaild host (deaults to 89)
     * 
     * @var mixed
     * @access public
     */
    public $port = 89;
    /**
     * Socket 
     * 
     * Actual socket from Net_Socket
     * 
     * @var mixed
     * @access public
     */
    public $socket = null;
    /**
     * debug 
     * 
     * Set to 1 to enable logging
     * 
     * @var mixed
     * @access public
     */
    public $debug = 0;
    /**
     * loginUser 
     * 
     * @var mixed
     * @access public
     */
    public $loginUser = null;
    /**
     * log 
     * 
     * @var mixed
     * @access public
     */
    public $log = null;
    /**
     * logFile
     * 
     * @var mixed
     * @access public
     */
    public $logFile = '/tmp/vpopmaild.log';
    /**
     * gidFlagValues 
     * 
     * @var array
     * @access public
     */
    public $gidFlagValues = array(
        'no_password_change'        => 0x01, 
        'no_pop'                    => 0x02, 
        'no_webmail'                => 0x04, 
        'no_imap'                   => 0x08, 
        'bounce_mail'               => 0x10, 
        'no_relay'                  => 0x20, 
        'no_dialup'                 => 0x40, 
        'user_flag_0'               => 0x080, 
        'user_flag_1'               => 0x100, 
        'user_flag_2'               => 0x200, 
        'user_flag_3'               => 0x400, 
        'no_smtp'                   => 0x800, 
        'domain_admin_privileges'   => 0x1000, 
        'override_domain_limits'    => 0x2000, 
        'no_spamassassin'           => 0x4000, 
        'delete_spam'               => 0x8000, 
        'system_admin_privileges'   => 0x10000, 
        'no_maildrop'               => 0x40000);

    /**
     * __construct 
     * 
     * Turn on logger if debug is 1.  Create socket.
     * 
     * @access protected
     * @return void
     */
    protected function  __construct()
    {
        if ($this->debug > 0 && is_null($this->log)) {
            $this->log = Log::factory('file', $this->logFile);
            if(is_null($this->log)) throw new Framework_Exception("Error creating Log object");
        }
        $this->socket = new Net_Socket();
        $result = $this->socket->connect($this->address, $this->port, null, 30);
        if(PEAR::isError($result)) throw new Framework_Exception($result->getMessage());
    }

    /**
     * recordio 
     * 
     * Log i/o to Log instance
     * 
     * @param string $data 
     * @access public
     * @return void
     */
    public function recordio($data)
    {
        if($this->debug > 0)
            $this->log->log($data);
    }

    /**
     * statusOk 
     * 
     *  $data contains +OK
     * 
     * @param string $data 
     * @access public
     * @return bool
     */
    public function statusOk($data)
    {
        if (ereg('^[+]OK', $data)) return true;
        return false;
    }

    /**
     * statusOkMore 
     * 
     *  $data is is exactly +OK+
     *  (more to come)
     * 
     * @param string $data 
     * @access public
     * @return bool
     */
    public function statusOkMore($data)
    {
        if (ereg('^[+]OK[+]$', $data)) return true;
        return false;
    }

    /**
     * statusOkNoMore 
     * 
     * $data is exactly +OK
     * 
     * @param string $data 
     * @access public
     * @return bool
     */
    public function statusOkNoMore($data)
    {
        if (ereg('^[+]OK$', $data)) return true;
        return false;
    }

    /**
     * statusErr 
     * 
     * $data starts with "-ERR "
     * 
     * @param string $data 
     * @access public
     * @return bool
     */
    public function statusErr($data)
    {
        if (ereg('^[-]ERR ', $data)) return true;
        return false;
    }

    /**
     * dotOnly 
     * 
     * $data is exactly "."
     * 
     * @param string $data 
     * @access public
     * @return bool
     */
    public function dotOnly($data)
    {
        if (ereg('^[.]$', $data)) return true;
        return false;
    }

    /**
     * sockWrite 
     * 
     * Write $data to socket
     * 
     * @param mixed $data 
     * @access public
     * @return mixed
     */
    public function sockWrite($data)
    {
        $this->recordio("sockWrite send: $data");
        $result = $this->socket->writeLine($data);
        if(PEAR::isError($result)) return $result;
        return true;
    }

    /**
     * sockRead 
     * 
     * Read line from socket
     * 
     * @access public
     * @return mixed
     */
    public function sockRead()
    {
        $in = '';
        while ('' == $in) {
            $in = $this->socket->readLine();
            if(PEAR::isError($in)) return $in;
            $in = trim($in);
            $this->recordio("sockRead Read: $in");
        }
        return $in;
    }

    /**
     * rawSockRead 
     * 
     * @param int $maxLen 
     * @access public
     * @return mixed
     */
    public function rawSockRead($maxLen = 2048)
    {
        $in = $this->socket->read($maxLen);
        if(PEAR::isError($in)) return $in;
        $this->recordio("rawSockRead Read: $in");
        return $in = trim($in);
    }

    /**
     * quit 
     * 
     * send quit command to vpopmaild
     * 
     * @access protected
     * @return void
     */
    protected function quit()
    {
        $this->sockWrite("quit\n");
    }

    /**
     * __destruct 
     * 
     * Close socket, logger
     * 
     * @access protected
     * @return void
     */
    protected function __destruct()
    {
        if($this->socket instanceof Net_Socket) {
            $this->quit();
            $this->socket->disconnect();
        }
    }

}

?>
