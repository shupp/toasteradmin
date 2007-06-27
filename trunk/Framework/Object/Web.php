<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Framework_Object_Web
 *
 * @author      Joe Stump <joe@joestump.net>
 * @copyright   Joe Stump <joe@joestump.net>
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @package     Framework
 * @filesource
 */

/**
 * Framework_Object_Web
 *
 * This is the base class for web applications extended from Framework_Module, 
 * which includes all module classes. Sets up a current user and session.
 *
 * @author      Joe Stump <joe@joestump.net>
 * @package     Framework
 */
abstract class Framework_Object_Web extends Framework_Object
{
    /**
     * $user
     *
     * This is the current user. If the user is not logged in then the
     * information defaults to the special anonymous user (userID = 0).
     *
     * @access      protected
     * @var         mixed $user Instnace of Framework_User of current user
     */
    protected $user = null;

    /**
     * $session
     *
     * A simple wrapper class around PHP's $_SESSION variable.
     *
     * @access      protected
     * @var         mixed $session Instance of Framework_Session
     */
    protected $session = null;

    /**
     * __construct
     *
     * @access      public
     * @return      void
     */

    public function __construct()
    {
        parent::__construct();
        $neg =& new Framework_User_Lang;
        $this->user = Framework_User_toasterAdmin::singleton();
        $this->session = Framework_Session::singleton();
    }

    public function paginate($total) {
        $this->setData('total', $total);
        $this->setData('limit', (integer)Framework::$site->config->maxPerPage);
        if(isset($_REQUEST['start']) && !ereg('[^0-9]', $_REQUEST['start'])) {
            if($_REQUEST['start'] == 0) {
                $start = 1;
            } else {
                $start = $_REQUEST['start'];
            }
        }
        if(!isset($start)) $start = 1;
        $this->setData('start', $start);
        $this->setData('currentPage', ceil($this->data['start'] / $this->data['limit']));
        $this->setData('totalPages', ceil($this->data['total'] / $this->data['limit']));
    }
}

?>
