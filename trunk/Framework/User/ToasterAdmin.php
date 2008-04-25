<?php
/**
 * Framework_User_ToasterAdmin 
 * 
 * PHP Version 5.2.0+
 * 
 * @uses      Net_Vpopmaild
 * @category  Mail
 * @package   ToasterAdmin
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      {http://trac.merchbox.com/trac/toasteradmin
 */
/**
 * Framework_User_ToasterAdmin 
 * 
 * Provides access to vpopmail, as well as authentication for Framework
 * 
 * @uses      Net_Vpopmaild
 * @category  Mail
 * @package   ToasterAdmin
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      {http://trac.merchbox.com/trac/toasteradmin
 */
class Framework_User_ToasterAdmin extends Net_Vpopmaild
{
    /**
     * __construct 
     * 
     * @access public
     * @throws  Framework_Exception
     * @return void
     */
    public function __construct()
    {
        $this->userID = null;
        parent::__construct();
        $this->acceptLog(Framework::$log);
        $this->setDebug((bool)Framework::$site->config->debug);
        $this->vpopmailRobotProgram = (string)Framework::$site->config->vpopmailRobotProgram;
        $this->vpopmailRobotRime = (int)Framework::$site->config->vpopmailRobotTime;
        $this->vpopmailRobotNumber = (int)Framework::$site->config->vpopmailRobotNumber;
        $address = gethostbyname((string)Framework::$site->config->vpopmaildHost);
        $port = (string)Framework::$site->config->vpopmaildPort;
        try {
            $this->connect($address, $port);
        } catch (Net_Vpopmaild_FatalException $e) {
            throw new Framework_Exception("Error: " . $e->getMessage());
        }
    }

    /**
     * isDefault 
     * 
     * @access public
     * @return bool   false if the user is authenticated, true if not (default user)
     */
    public function isDefault()
    {
        $session =& Framework_Session::singleton();
        if (is_null($session->email)) {
            return true;
        }
        // Check timeout
        $time           = time();
        $lastActionTime = $session->lastActionTime;
        $timeLimit      = (int)Framework::$site->config->inactiveTimeout;
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
