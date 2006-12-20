<?php
/**
 * Framework_User_toasterAdmin 
 * 
 * @uses Framework
 * @uses _User_vpopmail
 * @package 
 * @version $id$
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
 * @category ToasterAdminCat
 * @version 1.0
 *
 */
class Framework_User_toasterAdmin extends Framework_User_vpopmail {

    /**
     * __construct 
     * 
     * @access protected
     * @return void
     */
    function __construct() {
 
        $this->session = & Framework_Session::singleton();
 
        if (!function_exists('Socket_create')) { #  No Sockets
            die("VPopMaild.pobj requires you ./configure php with enable-Sockets. ");
        }
        $address = gethostbyname((string)Framework::$site->config->vpopmaildHost);
        $port = (integer)Framework::$site->config->vpopmaildPort;
        $this->Socket = Socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->Socket < 0) {
            $this->Error = "Socket_create() failed - reason: ".Socket_strError($this->Socket);
            return;
        }
        $result = Socket_connect($this->Socket, $address, $port);
        if (!$result) {
            $ErrorCode = Socket_last_Error();
            print_r(socket_strerror($ErrorCode));exit;
            $this->Error = "Socket_connect() failed - reason: ($Result) ".Socket_strError($ErrorCode) ."\n"."Is the daemon running?";
            return;
        }
        $in = $this->SockRead();
        #  Read the first response after connect...
        if (!$this->StatusOk($in)) {
            $this->Error = "Error send at initial connect - $in";
            Socket_shutdown($this->Socket, 2);
            Socket_close($this->Socket);
            unset($this->Socket);
            return;
        }
    }


    function authenticate($Email, $Password)
    {
        $Compact = true;
        $Compact = ($Compact) ? ' compact' : '';
        $out = "login $Email $Password $Compact";
        if ($this->ShowCmd) echo "Login string: $out\n";
        $this->SockWrite($out);
        $in = $this->SockRead();
        if (!$this->StatusOk($in)) {
            $this->Error = "Login failed - $in";
            Socket_shutdown($this->Socket, 2);
            Socket_close($this->Socket);
            unset($this->Socket);
            return false;
        }
        $this->LoginUser = $this->ReadUserInfo();
        return true;
    }

    
    /**
     *
     * Extensions to Vpopmaild for Bill's ToasterAdmin
     *
     * This class extends Vpopmaild with some ToasterAdmin specific functions
     *
     * @author Bill Shupp <hostmaster@shupp.org>
     * @package ToasterAdmin
     * @version 1.0
     *
     */
    function isSysAdmin($acct_info = '') {
        if ($acct_info == '') {
            $acct_info = $this->GetLoginUser();
        }
        return $this->GetGidBit($acct_info['gidflags'], 'system_admin_privileges');
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
            $acct_info = $this->GetLoginUser();
        }
        if ($this->isSysAdmin()) return TRUE;
        if ($this->GetGidBit($acct_info['gidflags'], 'domain_admin_privileges')) return TRUE;
        if (($acct_info['user'] == 'postmaster') && $domain == $acct_info['domain']) return TRUE;
        return FALSE;
    }
    /**
     * Has User Privs
     *
     * Determin if this user have privileges on this account
     *
     * @author Bill Shupp <hostmaster@shupp.org>
     *
     */
    function has_user_privs($account, $domain) {
        if ($this->has_domain_privs($domain)) return TRUE;
        if (($_SESSION['user'] == $account) && $domain == $_SESSION['domain']) return TRUE;
        return FALSE;
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
     *
     */
    function parse_home_dotqmail($contents, $account_info = '') {
        global $tpl, $autorespond;
        if ($account_info == '') {
            global $uesr_info;
            $account_info = $user_info;
        }
        $is_standard = FALSE;
        $is_deleted = FALSE;
        $is_forwarded = FALSE;
        // Set default template settings
        $tpl->assign('routing_standard_checked', '');
        $tpl->assign('routing_deleted_checked', '');
        $tpl->assign('routing_forwarded_checked', '');
        $tpl->assign('forward', '');
        $tpl->assign('save_a_copy_checked', '');
        $tpl->assign('vacation_checked', '');
        $tpl->assign('vacation_subject', '');
        $tpl->assign('vacation_body', '');
        if (empty($contents)) $is_standard = TRUE;
        if ((is_array($contents) && count($contents) == 1 && $contents[0] == '# delete')) $is_deleted = TRUE;
        if ($is_standard) {
            $tpl->assign('routing_standard_checked', ' checked');
        } else if ($is_deleted) {
            $tpl->assign('routing_deleted_checked', ' checked');
        } else {
            // now let's parse it
            while (list($key, $val) = each($contents)) {
                if ($val == $account_info['user_dir'].'/Maildir/' || $val == './Maildir/') {
                    $tpl->assign('save_a_copy_checked', ' checked');
                    continue;
                }
                if (ereg($autorespond, $val)) {
                    $tpl->assign('vacation_checked', ' checked');
                    $this->get_vacation($val);
                    continue;
                } else if (checkEmailFormat(ereg_replace('^&', '', $val))) {
                    $is_forwarded = TRUE;
                    $tpl->assign('routing_forwarded_checked', ' checked');
                    $tpl->assign('forward', ereg_replace('^&', '', $val));
                }
            }
            // See if default routing select applies
            if (!$is_standard && !$is_deleted && !$is_forwarded) {
                $tpl->assign('routing_standard_checked', ' checked');
            }
        }
    }
    /**
     * Get Vacaation Message Contents
     *
     * Parse use .qmail line contents to get message subject and meessage body
     *
     * @author Bill Shupp <hostmaster@shupp.org>
     *
     */
    function get_vacation($line = '') {
        global $tpl;
        if ($line == '') {
            global $user_info;
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
        $tpl->assign('vacation_subject', $subject);
        $tpl->assign('vacation_body', $body);
        $tpl->assign('vacation_checked', ' checked');
        // Example:
        // | /usr/local/bin/autorespond 86400 3 /home/vpopmail/domains/shupp.org/test/vacation/message /home/vpopmail/domains/shupp.org/test/vacation
        
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
                $new_array[$count] = $val;
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
    /**
     * GetAliasContents
     *
     * @param mixed $file
     * @param mixed $domain
     * @access public
     * @return void
     */
    function GetAliasContents($file, $domain) {
        $array = $this->ReadFile($domain, '', $file);
        $count = 0;
        $string = '';
        while (list($key, $val) = each($array)) {
            if ($count > 0) $string.= ', ';
            $string.= ereg_replace('^&', '', $val);
            $count++;
        }
        return $string;
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

}
?>