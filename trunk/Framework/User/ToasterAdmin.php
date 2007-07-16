<?php
/**
 * Framework_User_ToasterAdmin 
 * 
 * @package ToasterAdmin
 * @copyright 2005-2006 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */

/**
 *
 * Extension of Net_Vpopmaild
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package ToasterAdmin
 * @version 1.0
 *
 */
class Framework_User_ToasterAdmin extends Net_Vpopmaild {

    /**
     * __construct 
     * 
     * @access protected
     * @return void
     */
    function __construct() {
        parent::__construct();
        $this->accept(Framework::$log);
        $this->setDebug((int)Framework::$site->config->debug);
        $this->address = gethostbyname((string)Framework::$site->config->vpopmaildHost);
        $this->port = (string)Framework::$site->config->vpopmaildPort;
        $this->vpopmail_robot_program = (string)Framework::$site->config->vpopmail_robot_program;
        $this->vpopmail_robot_time = (int)Framework::$site->config->vpopmail_robot_time;
        $this->vpopmail_robot_number = (int)Framework::$site->config->vpopmail_robot_number;
        try {
            $this->connect();
        } catch (Net_Vpopmaild_Exception $e) {
            throw new Framework_Exception("Error: " . $e->getMessage());
        }

    }


    public function isDefault() {

        $session =& Framework_Session::singleton();
        if (is_null($session->email)) {
            return true;
        }
        // Check timeout
        $time = time();
        $lastActionTime = $session->lastActionTime;
        $timeLimit = (int)Framework::$site->config->inactiveTimeout;
        $this->recordio("timeout info: time: $time, lastActionTime: $lastActionTime, timeLimit: $timeLimit");
        if (($time - $lastActionTime) > $timeLimit) {
            header('Location: ./?module=Login&event=logoutInactive');
            return false;
        }

        // Authenticate
        $encryptedPass = $session->password;
        $crypt = new Crypt_Blowfish((string)Framework::$site->config->mcryptKey);
        $plainPass =  $crypt->decrypt($encryptedPass);
        if ($this->authenticate($session->email, $plainPass)) {
            $session->lastActionTime = $time;
            return false;
        }
        return true;
    }
}
?>
