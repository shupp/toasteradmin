<?php
/**
 * Framework_User_toasterAdmin 
 * 
 * @package ToasterAdmin
 * @copyright 2005-2006 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */

/**
 *
 * Extensions to Framework_User_vpopmail for Bill's ToasterAdmin
 *
 * This class extends Vpopmaild with some ToasterAdmin specific functions
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package ToasterAdmin
 * @version 1.0
 *
 */
class Framework_User_toasterAdmin extends Vpopmail_Main {

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
        // Define VPOPMAIL_ROBOT_PROGRAM
        define('VPOPMAIL_ROBOT_PROGRAM', (string)Framework::$site->config->autorespond);
        parent::__construct();
        $in = $this->sockRead();
    }


    function authenticate($email, $password)
    {
        $out = "clogin $email $password";
        $return = $this->sockWrite($out);
        if(PEAR::isError($return)) return $return;
        $in = $this->SockRead();
        if(PEAR::isError($in)) return $in;
        if (!$this->StatusOk($in)) {
            return PEAR::raiseError("Login failed - " . $in);
        }
        $this->loginUser = $this->readUserInfo();
        if(PEAR::isError($this->loginUser)) return $this->loginUser;
        $email_array = explode('@', $Email);
        $this->loginUser['domain'] = $email_array[1];
        return true;
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
            $acct_info = $this->getLoginUser();
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
            $acct_info = $this->getLoginUser();
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
     * Get Quota
     *
     * Make Quota Human Readable
     *
     * @author Bill Shupp <hostmaster@shupp.org>
     *
     */
    function get_quota($quota) {
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
     * @return void
     */
    function parse_home_dotqmail($contents, $account_info) {
        $autorespond = (string)Framework::$site->config->autorespond;
        $is_standard = false;
        $is_deleted = false;
        $is_forwarded = false;
        // Set default template settings
        $defaults['comment'] = $account_info['comment'];
        $defaults['forward'] = '';
        $defaults['save_a_copy_checked'] = '';
        $defaults['vacation_checked'] = '';
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
                if (ereg($autorespond, $val)) {
                    $defaults['vacation_checked'] = ' checked';
                    $vacation_array = $this->get_vacation($val);
                    while(list($vacKey, $vacVal) = each($vacation_array)) {
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
    function get_vacation($line = '', $user_info) {
        if ($line == '') {
            $path = $user_info['user_dir'].'/vacation/message';
        } else {
            $line = ereg_replace('^[|][ ]*', '', $line);
            $array = explode(' ', $line);
            $path = $array[3];
        }
        $contents = $this->ReadFile($path);
        /// pre($contents);
        if ($this->Error) {
            $tpl->set_msg_err(_('Error: ') .$this->Error);
            $tpl->wrap_exit();
        }
        $subject = '';
        $body = '';
        while (list($key, $val) = each($contents)) {
            if ($key == '1') {
                $subject = ereg_replace('^Subject: *', '', $val);
                continue;
            }
            if ($key < 3) continue;
            if (strlen($val) == 0) {
                $body.= "\n";
            } else {
                $body.= $val;
                // $body .= $val . "\n";
                
            }
        }
        return array(   'vacation_subject' => $subject,
                        'vacation_body' => $body,
                        'vacation_checked' => ' checked');
    }
    /**
     * setup_vacation
     *
     * @param mixed $user
     * @param mixed $domain
     * @param mixed $subject
     * @param mixed $message
     * @param string $acct_info
     * @access public
     * @return void
     */
    function setup_vacation($user, $domain, $subject, $message, $acct_info = '') {
        global $vp;
        if ($acct_info == '') {
            global $user_info;
            $acct_info = $user_info;
        }
        $vacation_dir = $user_info['user_dir'].'/vacation';
        $message_file = $dir.'/message';
        $contents = "From: $user@$domain\n";
        $contents.= "Subject: $subject\n\n";
        $contents.= "$message\n";
        $vp->RmDir($domain, $user, $vacation_dir);
        $vp->MkDir($vacation_dir);
        $vp->WriteFile($contents, $domain, $user, $message_file);
    }
    /**
     * ListAliases
     *
     * @param mixed $array
     * @param mixed $page
     * @param mixed $max_per_page
     * @access public
     * @return void
     */
    function ListAliases($array, $page, $max_per_page) {
        $new_array = array();
        $count = 1;
        $page_count = 1;
        $item_count = 1;
        while (list($key, $val) = each($array)) {
            if ($page_count == $page) {
                $new_array[$key] = $val;
            }
            if ($item_count == $max_per_page) {
                $page_count++;
                $item_count = 1;
            } else {
                $item_count++;
            }
            $count++;
        }
        return $new_array;
    }
    function GetAliasContents($contentsArray) {
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
        while(list($key, $val) = each($aliasArray)) {
            $alias = ereg_replace('(^[^ ]+) .*$', '\1', $val);
            if(!in_array($alias, $aliasList)) 
                array_push($aliasList, $alias);
        }
        // Now create content arrays
        $contentArray = array();
        reset($aliasList);
        while(list($key, $val) = each($aliasList)) {
            reset($aliasArray);
            $count = 0;
            while(list($lkey, $lval) = each($aliasArray)) {
                if(ereg("^$val ", $lval)) {
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
        if(PEAR::isError($result)) return false;
        return true;
    }
    /**
     * display_forward_line
     *
     * @param mixed $line
     * @access public
     * @return void
     */
    function display_forward_line($line) {
        if (ereg('^&', $line)) return ereg_replace('^&', '', $line);
    }


    /**
     * parseAliases 
     * 
     * Return correct type of aliases - forwards or responders
     * 
     * @param mixed $in_array 
     * @param mixed $type 
     * @access public
     * @return void
     */
    function parseAliases($in_array, $type) {
        $out_array = array();
        $raw_array = $this->aliasesToArray($in_array);
        foreach ($raw_array as $parentkey => $parentval) {
            $is_type = 'forwards';
            foreach ($parentval as $key => $val) {
                if(ereg('[|].*autorespond', $val)) {
                    $is_type = 'responders';
                    break;
                }
            }
            if($type == $is_type)
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
        while((list($key, $val) = each($array)) && $page_count <= $page) {
            if($page_count == $page) {
                $out_array[$key] = $val;
            }
            $limit_count++;
            if($limit_count > $limit) {
                $limit_count = 1;
                $page_count++;
            }
        }
        return $out_array;
    }

}
?>
