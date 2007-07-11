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
 * Extensions to Vpopmail_Main for Bill's ToasterAdmin
 *
 * This class extends Vpopmaild with some ToasterAdmin specific functions
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package ToasterAdmin
 * @version 1.0
 *
 */
class Framework_User_ToasterAdmin extends Vpopmail_Main {

    /**
     * ezmlmOpts 
     * 
     * This will be an array of the default ezmlm command 
     * line options
     * 
     * @var mixed
     * @access public
     */
    public $ezmlmOpts = null;

    /**
     * __construct 
     * 
     * @access protected
     * @return void
     */
    function __construct() {
        // Manual log stuff, since we're skipping Framework_User::__construct()
        $this->debug = (int)Framework::$site->config->debug;
        $this->address = gethostbyname((string)Framework::$site->config->vpopmaildHost);
        $this->port = (string)Framework::$site->config->vpopmaildPort;
        $this->logFile = (string)Framework::$site->config->logFile;
        $this->vpopmail_robot_program = (string)Framework::$site->config->vpopmail_robot_program;
        $this->vpopmail_robot_time = (int)Framework::$site->config->vpopmail_robot_time;
        $this->vpopmail_robot_number = (int)Framework::$site->config->vpopmail_robot_number;
        parent::__construct();
        $in = $this->sockRead();
        if (!$this->statusOk($in)) {
            throw new Framework_Exception("Error: initial status: $in");
        }

    }


    /**
     * authenticate 
     * 
     * Authenticate user based on email and password
     * 
     * @param mixed $email 
     * @param mixed $password 
     * @access public
     * @return mixed true on success, PEAR_Error on failure
     */
    public function authenticate($email, $password)
    {
        $result = $this->clogin($email, $password);
        if (PEAR::isError($result)) return $result;
        // Easy way to access domain
        $email_array = explode('@', $email);
        $this->loginUser['domain'] = $email_array[1];
        return true;
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
        $plainPass =  Framework_User_passEncryption::decryptPass($encryptedPass, (string)Framework::$site->config->mcryptKey);
        if (!PEAR::isError($this->authenticate($session->email, $plainPass))) {
            $session->lastActionTime = $time;
            return false;
        } else {
            return true;
        }
    }

    /**
     *
     * Extensions to Vpopmaild for Bill's ToasterAdmin
     *
     * This class extends Vpopmaild with some ToasterAdmin specific functions
     *
     * @author Bill Shupp <hostmaster@shupp.org>
     * @version 1.0
     *
     */
    function isSysAdmin($acct_info = '') {
        if ($acct_info == '') {
            $acct_info = $this->loginUser;
        }
        return $this->getGidBit($acct_info['gidflags'], 'system_admin_privileges');
    }
    /**
     * Is Domain Admin
     *
     * Determin if this is a domain administrator for this domain
     *
     * @author Bill Shupp <hostmaster@shupp.org>
     *
     */

    function isDomainAdmin($domain, $acct_info = '') {
        if ($acct_info == '') {
            $acct_info = $this->loginUser;
        }
        if ($this->isSysAdmin()) return true;
        if ($this->getGidBit($acct_info['gidflags'], 'domain_admin_privileges')) return true;
        if (($acct_info['user'] == 'postmaster') && $domain == $acct_info['domain']) return true;
        return false;
    }

    /**
     * Is User Admin
     *
     * Determin if this user have privileges on this account
     *
     * @author Bill Shupp <hostmaster@shupp.org>
     *
     */
    function isUserAdmin($account, $domain) {
        if ($this->isDomainAdmin($domain)) return true;
        if (($this->loginUser['name'] == $account) && ($this->loginUser['domain'] == $domain))
            return true;
        return false;
    }

    /**
     * getQuota 
     * 
     * @param mixed $quota 
     * @access public
     * @return string
     */
    function getQuota($quota) {
        if (ereg('S$', $quota)) {
            $quota = ereg_replace('S$', '', $quota);
            $quota = $quota/1024;
            $quota = $quota/1024;
            $quota = $quota.'MB';
        }
        return $quota;
    }
    /**
     * Parse Home dot-qmail
     *
     * Evaluate contents of a .qmail file in a user's home directory.
     * Looking for routing types standard, delete, or forward, with optional
     * saving of messages, as well as vacation messages.
     *
     * @author Bill Shupp <hostmaster@shupp.org>
     * @param mixed $contents 
     * @param mixed $account_info 
     * @access public
     * @return array $defaults
     */
    function parseHomeDotqmail($contents, $account_info) {
        $is_standard = false;
        $is_deleted = false;
        $is_forwarded = false;
        // Set default template settings
        $defaults['comment'] = $account_info['comment'];
        $defaults['forward'] = '';
        $defaults['save_a_copy_checked'] = '';
        $defaults['vacation'] = '';
        $defaults['vacation_subject'] = '';
        $defaults['vacation_body'] = '';
        if (empty($contents)) $is_standard = true;
        if ((is_array($contents) && count($contents) == 1 && $contents[0] == '# delete')) $is_deleted = true;
        if ($is_standard) {
            $defaults['routing'] = 'routing_standard';
        } else if ($is_deleted) {
            $defaults['routing'] = 'routing_deleted';
        } else {
            // now let's parse it
            while (list($key, $val) = each($contents)) {
                if ($val == $account_info['user_dir'].'/Maildir/' || $val == './Maildir/') {
                    $defaults['save_a_copy_checked'] = ' checked';
                    continue;
                }
                if (ereg($this->vpopmail_robot_program, $val)) {
                    $vacation_array = $this->getVacation($val, $account_info);
                    while (list($vacKey, $vacVal) = each($vacation_array)) {
                        $defaults[$vacKey] = $vacVal;
                    }
                    continue;
                } else {
                    if ($this->validEmailAddress(ereg_replace('^&', '', $val))) {
                        $is_forwarded = true;
                        $defaults['routing'] = 'routing_forwarded';
                        $defaults['forward'] = ereg_replace('^&', '', $val);
                    }
                }
            }
            // See if default routing select applies
            if (!$is_standard && !$is_deleted && !$is_forwarded) {
                $defaults['routing'] = 'routing_standard';
            }
        }
        return $defaults;
    }
    /**
     * Get Vacaation Message Contents
     *
     * Parse use .qmail line contents to get message subject and meessage body
     *
     * @author Bill Shupp <hostmaster@shupp.org>
     * @param string $line 
     * @param mixed $user_info 
     * @access public
     * @return void
     */
    function getVacation($line = '', $user_info) {
        if ($line == '') {
            $path = $user_info['user_dir'].'/vacation/message';
        } else {
            $line = ereg_replace('^[|][ ]*', '', $line);
            $array = explode(' ', $line);
            $path = $array[3];
        }
        $contents = $this->readFile($path);
        if (PEAR::isError($contents)) return $contents;
        array_shift($contents); #   Eat From: address
        $subject = substr(array_shift($contents), 9);
        array_shift($contents); #  eat blank line
        return array(   'vacation_subject' => $subject,
                        'vacation_body' => implode("\n", $contents),
                        'vacation' => ' checked');
    }
    /**
     * setupVacation
     *
     * @param mixed $user
     * @param mixed $domain
     * @param mixed $subject
     * @param mixed $message
     * @param string $acct_info
     * @access public
     * @return void
     */
    function setupVacation($user, $domain, $subject, $message, $acct_info = '')
    {
        if ($acct_info == '') {
            global $user_info;
            $acct_info = $user_info;
        }
        $vacation_dir = $user_info['user_dir'].'/vacation';
        $message_file = $dir.'/message';
        $contents = "From: $user@$domain\n";
        $contents.= "Subject: $subject\n\n";
        $contents.= "$message\n";
        $this->rmDir($domain, $user, $vacation_dir);
        $this->mkDir($vacation_dir);
        $this->writeFile($contents, $domain, $user, $message_file);
    }

    /**
     * getAliasContents 
     * 
     * @param mixed $contentsArray 
     * @access public
     * @return string
     */
    function getAliasContents($contentsArray) {
        $count = 0;
        $string = '';
        while (list($key, $val) = each($contentsArray)) {
            if ($count > 0) $string.= ', ';
            $string.= ereg_replace('^&', '', $val);
            $count++;
        }
        return $string;
    }

    /**
     * aliasesToArray 
     * 
     * take raw ListAlias output, and format into 
     * associative arrays
     * 
     * @param mixed $aliasArray 
     * @access public
     * @return void
     */
    function aliasesToArray($aliasArray) {
        // generate unique list of aliases
        $aliasList = array();
        while (list($key, $val) = each($aliasArray)) {
            $alias = ereg_replace('(^[^ ]+) .*$', '\1', $val);
            if (!in_array($alias, $aliasList)) 
                array_push($aliasList, $alias);
        }
        // Now create content arrays
        $contentArray = array();
        reset($aliasList);
        while (list($key, $val) = each($aliasList)) {
            reset($aliasArray);
            $count = 0;
            while (list($lkey, $lval) = each($aliasArray)) {
                if (ereg("^$val ", $lval)) {
                    $aliasLine = ereg_replace('^[^ ]+ (.*$)', '\1', $lval);
                    $contentArray[$val][$count] = $aliasLine;
                    $count++;
                }
            }
        }
        return $contentArray;
    }

    /**
     * validEmailAddress 
     * 
     * Simple wrapper for Mail_RFC822::parseAddressList() that returns
     * true or false
     * 
     * @param mixed $email 
     * @access public
     * @return void
     */
    public static function validEmailAddress($email) {
        $result = Mail_RFC822::parseAddressList($email, '');
        if (PEAR::isError($result)) return false;
        return true;
    }
    /**
     * displayForwardLine
     *
     * @param mixed $line
     * @access public
     * @return mixed null on failure, string on success
     */
    function displayForwardLine($line) {
        if (ereg('^&', $line)) return ereg_replace('^&', '', $line);
    }


    /**
     * parseAliases 
     * 
     * Return correct type of aliases - forwards, responders, or lists (ezmlm)
     * 
     * @param mixed $in_array 
     * @param mixed $type 
     * @access public
     * @return array of parsed aliases
     */
    function parseAliases($in_array, $type) {
        $out_array = array();
        $raw_array = $this->aliasesToArray($in_array);
        foreach ($raw_array as $parentkey => $parentval) {
            $is_type = 'forwards';
            foreach ($parentval as $key => $val) {
                if (ereg('[|].*' . $this->vpopmail_robot_program, $val)) {
                    $is_type = 'responders';
                    break;
                }
                if (ereg('[|].*ezmlm-', $val)) {
                    $is_type = 'lists';
                    break;
                }
            }
            if ($type == $is_type)
                $out_array[$parentkey] = $parentval;
        }
        return $out_array;
    }

    /**
     * paginateArray 
     * 
     * A simple function to paginate an array.  Could probably be better.
     * 
     * @param mixed $array 
     * @param mixed $page 
     * @param mixed $limit 
     * @access public
     * @return array
     */
    function paginateArray($array, $page, $limit) {
        $page_count = 1;
        $limit_count = 1;
        $out_array = array();
        while ((list($key, $val) = each($array)) && $page_count <= $page) {
            if ($page_count == $page) {
                $out_array[$key] = $val;
            }
            $limit_count++;
            if ($limit_count > $limit) {
                $limit_count = 1;
                $page_count++;
            }
        }
        return $out_array;
    }

    /**
     * setDefaultEzmlmOpts 
     * 
     * This just assigns default options to $this->ezmlmOpts
     * 
     * @access public
     * @return void
     */
    public function setDefaultEzmlmOpts() {
        /* for the options below, use 1 for "on" or "yes" */
        $this->ezmlmOpts['a'] = 1; /* Archive */
        $this->ezmlmOpts['b'] = 1; /* Moderator-only access to archive */
        $this->ezmlmOpts['c'] = 0; /* ignored */
        $this->ezmlmOpts['d'] = 0; /* Digest */
        $this->ezmlmOpts['e'] = 0; /* ignored */
        $this->ezmlmOpts['f'] = 1; /* Prefix */
        $this->ezmlmOpts['g'] = 1; /* Guard Archive */
        $this->ezmlmOpts['h'] = 0; /* Subscribe doesn't require conf */
        $this->ezmlmOpts['i'] = 0; /* Indexed */
        $this->ezmlmOpts['j'] = 0; /* Unsubscribe doesn't require conf */
        $this->ezmlmOpts['k'] = 0; /* Create a blocked sender list */
        $this->ezmlmOpts['l'] = 0; /* Remote admins can access subscriber list */
        $this->ezmlmOpts['m'] = 0; /* Moderated */
        $this->ezmlmOpts['n'] = 0; /* Remote admins can edit text files */
        $this->ezmlmOpts['o'] = 0; /* Others rejected (for Moderated lists only */
        $this->ezmlmOpts['p'] = 1; /* Public */
        $this->ezmlmOpts['q'] = 1; /* Service listname-request */
        $this->ezmlmOpts['r'] = 0; /* Remote Administration */
        $this->ezmlmOpts['s'] = 0; /* Subscriptions are moderated */
        $this->ezmlmOpts['t'] = 0; /* Add Trailer to outgoing messages */
        $this->ezmlmOpts['u'] = 1; /* Only subscribers can post */
        $this->ezmlmOpts['v'] = 0; /* ignored */
        $this->ezmlmOpts['w'] = 0; /* special ezmlm-warn handling (ignored) */
        $this->ezmlmOpts['x'] = 0; /* enable some extras (ignored) */
        $this->ezmlmOpts['y'] = 0; /* ignored */
        $this->ezmlmOpts['z'] = 0; /* ignored */
    }

}
?>