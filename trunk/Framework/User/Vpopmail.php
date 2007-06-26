<?php
/**
 * Framework_User_Vpopmail 
 * 
 * @package ToasterAdmin
 */
/**
 *
 * Vpopmail.php (originally vpopmaild.pobj)
 *
 * This class makes vpopmaild functions available.  Requires sockets.
 *
 * @author Rick Widmer
 * @author Bill Shupp
 * @package ToasterAdmin
 * @version 1.0
 *
 */
class Framework_User_Vpopmail extends Framework_User {
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
     * @var mixed
     * @access public
     */
    public $debug = false;
    /**
     * LoginUser 
     * 
     * @var mixed
     * @access public
     */
    public $loginUser = null;
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

    function  __construct() {
        #parent::__construct();
        // Manual log stuff, since we're skipping Framework_User::__construct()
        if ($this->debug && is_null($this->log)) {
            $logFile = (string)Framework::$site->config->logFile;
            $this->log = Log::factory('file', $logFile);
        }

        $this->address = gethostbyname((string)Framework::$site->config->vpopmaildHost);
        $this->port = (string)Framework::$site->config->vpopmaildPort;
        $this->socket = new Net_Socket();
        $result = $this->socket->connect($this->address, $this->port);
        if(PEAR::isError($result)) return $result;
    }

    function recordio($data) {
        if($this->debug)
            $this->log->log($data);
    }

    // Status/Return messages
    function statusOk($data) {
        if (ereg('^[+]OK', $data)) return true;
        return false;
    }
    function statusOkMore($data) {
        if (ereg('^[+]OK[+]$', $data)) return true;
        return false;
    }
    function statusOkNoMore($data) {
        if (ereg('^[+]OK$', $data)) return true;
        return false;
    }
    function statusErr($data) {
        if (ereg('^[-]ERR ', $data)) return true;
        return false;
    }
    function dotOnly($data) {
        if (ereg('^[.]$', $data)) return true;
        return false;
    }


    function getGidBit($bitmap, $bit, $flip = false) {
        if (!isset($this->gidFlagValues[$bit])) {
            return PEAR::raiseError("Error - unknown GID Bit value specified. $bit");
        }
        $bitValue = $this->gidFlagValues[$bit];
        if ($flip) return ($bitmap&$bitValue) ? false : true;
        return ($bitmap&$bitValue) ? true : false;
    }
    function setGidBit(&$bitmap, $bit, $value, $flip = false) {
        if (!isset($this->gidFlagValues[$bit])) 
            return PEAR::raiseError("Unknown GID Bit value specified. $bit");
        $bitValue = $this->gidFlagValues[$bit];
        if ($flip) {
            $value = ('t' == $value{0}) ? 0 : $bitValue;
        } else {
            $value = ('t' == $value{0}) ? $bitValue : 0;
        }
        $bitmap = (int)$value|(~(int)$bitValue&(int)$bitmap);
    }
    function getIPMap($ip) {
        if ($status = $this->SockWrite("get_ip_map $ip")) {
            return PEAR::raiseError("Error - write to socket failed! $status");
        }
        $status = $this->SockRead();
        if ($this->statusErr($status)) {
            return PEAR::raiseError("command failed - $Status");
        }
        $lists = array();
        $in = $this->SockRead();
        while (!$this->statusErr($in) && !$this->statusOk($in) && !$this->dotOnly($in)) {
            $lists[] = $in;
            $in = $this->SockRead();
        }
        $exploded = explode(" ", $lists[0]);
        return $exploded[1];
    }
    function addIPMap($domain, $ip) {
        if ($Status = $this->SockWrite("add_ip_map $ip $Domain")) {
            return PEAR::raiseError("Error - write to Socket failed! $status");
        }
        $status = $this->SockRead();
        if (!$this->statusOk($status)) {
            return PEAR::raiseError($this->Error = "command failed - $Status");
        }
        return false;
    }
    function delIPMap($domain, $ip) {
        if ($status = $this->SockWrite("del_ip_map $ip $domain")) {
            return PEAR::raiseError("Error - write to Socket failed! $status");
        }
        $status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            return PEAR::raiseError("command failed - $status");
        }
        return false;
    }
    function showIPMap() {
        if ($status = $this->SockWrite("show_ip_map")) {
            return PEAR::raiseError("Error - write to Socket failed! $Status");
        }
        $status = $this->SockRead();
        if (!$this->statusOk($status)) {
            return PEAR::raiseError("command failed - $Status");
        }
        $lists = array();
        $in = $this->SockRead();
        while (!$this->dotOnly($in) && !$this->statusOk($in) && !$this->statusError($in)) {
            list($ip, $domain) = explode(' ', $in);
            if (!empty($lists[$ip])) {
                $lists[$ip].= ", ".$domain;
            } else { #  Not duplicate
                $lists[$ip] = $domain;
            }
            $in = $this->SockRead();
        }
        ksort($lists);
        return $lists;
    }
    ################################################################
    #
    #  f u n c t i o n      S o c k W r i t e
    #
    function SockWrite($data) {
        $this->recordio("SockWrite Send: $data");
        $result = $this->socket->writeLine($data);
        if(PEAR::isError($result)) return $result;
        return false;
    }
    ################################################################
    #
    #  f u n c t i o n      S o c k R e a d
    #
    function SockRead() {
        $in = '';
        while ('' == $in) {
            $in = $this->socket->readLine();
            if(PEAR::isError($in)) return $in;
            $in = trim($in);
            $this->recordio("SockRead Read: $in");
        }
        return $in;
    }
    ################################################################
    #
    #  f u n c t i o n      R a w S o c k R e a d
    #
    function RawSockRead($MaxLen = 2048) {
        $in = '';
        $in = trim(Socket_read($this->Socket, $MaxLen, PHP_NORMAL_READ));
        if ($this->ShowRecv) {
            echo "SockRead Returned: $in";
        }
        return $in;
    }
    ################################################################
    #
    #  f u n c t i o n       D o t Q m a i l S p l i t
    #
    function DotQmailSplit($FileContents) {
        $Result = array('Comment' => array(), 'Program' => array(), 'Delivery' => array(), 'Forward' => array(),);
        if (!is_array($FileContents)) {
            return $Result;
        }
        reset($FileContents);
        while (list(, $Line) = each($FileContents)) {
            switch ($Line{0}) {
                case '#':
                    $Result['Comment'][] = $Line;
                break;
                case '|':
                    $Result['Program'][] = $Line;
                break;
                case '/':
                    $Result['Delivery'][] = $Line;
                break;
                case '&':
                default:
                    $Result['Forward'][] = $Line;
                break;
            }
        } #   while each $FileContents
        return $Result;
    }
    ################################################################
    #
    #  f u n c t i o n       R o b o t D e l
    #
    function RobotDel($Domain, $User) {
        $this->Error = '';
        $this->RobotGet($Domain, $User);
        if ('' != $this->Error) {
            $this->Error = 'ERR - Not a mail robot';
            return true;
        }
        $RobotDir = strtoupper($User);
        $DotQmailName = ".qmail-$User";
        $RobotPath = $this->LoginUser['vpopmail_dir']."/domains/$Domain/$RobotDir";
        $DeleteDirFailed = false;
        $DeleteDotQmailFileFailed = false;
        $this->RmDir($RobotPath);
        if ('' != $this->Error) {
            $DeleteDirFailed = true;
        }
        $this->RmFile($Domain, '', $DotQmailName);
        if ('' != $this->Error) {
            $DeleteDotQmailFileFailed = false;
        }
        #  All the rest is just to create the Error message...
        $ErrorMessage = '';
        if ($DeleteDirFailed) {
            $ErrorMessage.= 'Directory ';
        }
        if ($DeleteDirFailed AND $DeleteDotQmailFileFailed) {
            $ErrorMessage.= "and ";
        }
        if ($DeleteDotQmailFileFailed) {
            $ErrorMessage.= 'DotQmail File ';
        }
        if ('' != $ErrorMessage) {
            $ErrorMessage = "ERR - $ErrorMessage failed";
            return true;
        }
        return false;
    }
    ################################################################
    #
    #  f u n c t i o n       R o b o t S e t
    #
    function RobotSet($Domain, $User, $Subject, $Message, $Forward, $Time = VPOPMAIL_ROBOT_TIME, $Number = VPOPMAIL_ROBOT_NUMBER) {
        $this->Error = '';
        $RobotDir = strtoupper($User);
        $DotQmailName = ".qmail-$User";
        $RobotPath = $this->LoginUser['vpopmail_dir']."/domains/$Domain/$RobotDir";
        $MessagePath = "$RobotPath/message";
        $Program = VPOPMAIL_ROBOT_PROGRAM;
        #  Build the dot qmail file
        $DotQmail = array("|$Program $Time $Number $MessagePath $RobotPath",);
        if (is_array($Forward)) {
            array_merge($DotQmail, $Forward);
        } elseif (is_string($Forward)) {
            $DotQmail[] = $Forward;
        }
        #echo "DotQmail file: "; print_r( $DotQmail );
        $this->WriteFile($DotQmail, $Domain, '', $DotQmailName);
        $this->MkDir($Domain, '', $RobotDir);
        if ('' != $this->Error) { #  OK if it already exists
            #   echo "Robot already exists " . $this->Error . "\n";
            $this->Error = '';
        }
        #  NOTE:  You have to add them backwards!
        array_unshift($Message, "");
        array_unshift($Message, "Subject: $Subject");
        array_unshift($Message, "From: $User@$Domain");
        $this->WriteFile($Message, $MessagePath);
        return false;
    }
    ################################################################
    #
    #  f u n c t i o n       R o b o t G e t
    #
    function RobotGet($Domain, $User) {
        $this->Error = '';
        $RobotPath = strtoupper($User) .'/';
        $DotQmailName = ".qmail-$User";
        $DotQmail = $this->ReadFile($Domain, '', $DotQmailName);
        if ('' != $this->Error) {
            $this->Error = "ERR - Unable to find dotqmail file";
            return $Result;
        }
        #echo "DotQmail: "; print_r( $DotQmail ); echo "\n";
        $DotQmail = $this->DotQmailSplit($DotQmail);
        #echo "DotQmaili Split: "; print_r( $DotQmail ); echo "\n";
        if (count($DotQmail['Program']) > 1) { #  Too many programs
            $this->Error = 'ERR - too many programs in Robot dotqmail file';
            return true;
        }
        if (!ereg(VPOPMAIL_ROBOT_PROGRAM, $DotQmail['Program'][0])) {
            $this->Error = 'ERR - Mail Robot program not found';
            return true;
        }
        list($Program, $Time, $Number, $MessageFile, $RobotPath) = explode(' ', $DotQmail['Program'][0]);
        $Message = $this->ReadFile($MessageFile);
        if ('' != $this->Error) {
            $this->Error = "ERR - Unable to find message file";
            return $Result;
        }
        $Result = array();
        $Result['Time'] = $Time;
        $Result['Number'] = $Number;
        array_shift($Message); #   Eat From: address
        $Result['Subject'] = substr(array_shift($Message), 9);
        array_shift($Message); #  eat blank line
        if (0 == count($DotQmail['Forward'])) { #  Empty
            $Result['Forward'] = '';
        } elseif (count($DotQmail['Forward']) > 1) { #  array
            $Result['Forward'] = $DotQmail['Forward'];
        } else { #  Single entry
            $Result['Forward'] = $DotQmail['Forward'][0];
        }
        $Result['Message'] = $Message;
        #echo "Result: "; print_r( $Result ); echo "\n";
        return $Result;
    }
    ################################################################
    #
    #  f u n c t i o n       L i s t L i s t s
    #
    function ListLists($Domain, $User = '') {
        $this->Error = '';
        $BasePath = "$Domain";
        if (!empty($User)) $BasePath = "$User@$BasePath";
        if ($Status = $this->SockWrite("list_lists $BasePath")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status";
            return;
        }
        $Lists = array();
        $in = $this->SockRead();
        while (!$this->dotOnly($in) AND !$this->statusOk($in) AND !$this->statusErr($in)) {
            $Lists[] = $in;
            $in = $this->SockRead();
        }
        return $Lists;
    }
    ################################################################
    #
    #  f u n c t i o n       L i s t A l i a s
    #
    function ListAlias($Domain, $User = '') {
        $this->Error = '';
        $BasePath = "$Domain";
        if (!empty($User)) $BasePath = "$User@$BasePath";
        if ($Status = $this->SockWrite("list_alias $BasePath")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status";
            return;
        }
        $Alii = array();
        $in = $this->SockRead();
        while (!$this->dotOnly($in) AND !$this->statusOk($in) AND !$this->statusErr($in)) {
            $Alii[] = $in;
            $in = $this->SockRead();
        }
        return $Alii;
    }
    ################################################################
    #
    #  f u n c t i o n       R m F i l e
    #
    function RmFile($Domain, $User = '', $Path = '') {
        $this->Error = '';
        $BasePath = "$Domain";
        if (!empty($User)) $BasePath = "$User@$BasePath";
        if (!empty($Path)) $BasePath.= "/".$Path;
        $BasePath = ereg_replace('//', '/', $BasePath);
        if ($Status = $this->SockWrite("rm_file $BasePath")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status";
            return true;
        }
        return false;
    }
    ################################################################
    #
    #  f u n c t i o n      W r i t e F i l e
    #
    function WriteFile($Contents, $Domain, $User = '', $Path = '') {
        $this->Error = '';
        $BasePath = "$Domain";
        if (!empty($User)) $BasePath = "$User@$BasePath";
        if (!empty($Path)) $BasePath.= "/".$Path;
        $BasePath = ereg_replace('//', '/', $BasePath);
        if ($Status = $this->SockWrite("write_file $BasePath")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        reset($Contents);
        while (list(, $Line) = each($Contents)) {
            if ($Status = $this->SockWrite($Line)) {
                $this->Error = "Error - write to Socket failed! $Status";
                return;
            }
        }
        if ($Status = $this->SockWrite(".")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status";
            return true;
        }
        return false;
    }
    ################################################################
    #
    #  f u n c t i o n      R m D i r
    #
    function RmDir($Domain, $User = '', $Path = '') {
        $this->Error = '';
        $BasePath = "$Domain";
        if (!empty($User)) $BasePath = "$User@$BasePath";
        if (!empty($Path)) $BasePath.= "/".$Path;
        $BasePath.= '/';
        $BasePath = ereg_replace('//', '/', $BasePath);
        if ($Status = $this->SockWrite("rm_dir $BasePath")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status";
            return true;
        }
        return false;
    }
    ################################################################
    #
    #  f u n c t i o n      M k D i r
    #
    function MkDir($Domain, $User = '', $Path = '') {
        $this->Error = '';
        $BasePath = "$Domain";
        if (!empty($User)) $BasePath = "$User@$BasePath";
        if (!empty($Path)) $BasePath.= "/".$Path;
        $BasePath.= '/';
        #echo "MkDir BasePath: $BasePath\n";
        $BasePath = ereg_replace('//', '/', $BasePath);
        if ($Status = $this->SockWrite("mk_dir $BasePath")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status";
            return true;
        }
        return false;
    }
    ################################################################
    #
    #  f u n c t i o n       S e t L i m i t s
    #
    function SetLimits($Domain, $Limits) {
        $StringParms = array('max_popaccounts', 'max_aliases', 'max_forwards', 'max_autoresponders', 'max_mailinglists', 'disk_quota', 'max_msgcount', 'default_quota', 'default_maxmsgcount',);
        $FlagParms = array('disable_pop', 'disable_imap', 'disable_dialup', 'disable_password_changing', 'disable_webmail', 'disable_external_relay', 'disable_smtp', 'disable_spamassassin', 'delete_spam', 'perm_account', 'perm_alias', 'perm_forward', 'perm_autoresponder', 'perm_maillist', 'perm_maillist_users', 'perm_maillist_moderators', 'perm_quota', 'perm_defaultquota',);
        if ($Status = $this->SockWrite("set_limits $Domain")) {
            $this->Error = "Error - write to Socket failed sending command! $Status";
            return;
        }
        while (list(, $Name) = each($StringParms)) {
            if (!empty($Limits[$Name])) {
                $Value = $Limits[$Name];
                if ($Status = $this->SockWrite("$Name $Value")) {
                    $this->Error = "Error - write to Socket failed sending string! $Status";
                    return;
                }
            }
        }
        while (list(, $Name) = each($FlagParms)) {
            if (!empty($Limits[$Name])) {
                $Value = $Limits[$Name];
                if ($Status = $this->SockWrite("$Name $Value")) {
                    $this->Error = "Error - write to Socket failed sending flag! $Status";
                    return;
                }
            }
        }
        if ($Status = $this->SockWrite(".")) {
            $this->Error = "Error - write to Socket failed sending end! $Status";
            return;
        }
        #$in = $this->SockRead();
        #$Warnings = array();
        #
        #while( '.' != $in{0} AND '+' != $in{0} AND '-' != $in{0} ) {
        #   if( '' != $in ) {
        #      $Warnings[] = $in;
        #      }
        #
        #   $in = $this->SockRead();
        #   }
        #
        #if( $this->ShowData ) {
        #   echo "\nWarnings collected: ";
        #   print_r( $Warnings );
        #   }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status";
            return true;
        }
        #if( !empty( $Warnings )) {
        #   return "Warning:\n   " .
        #          implode( "\n   ", $Warnings ) .
        #          "\n\n";
        #   }
        return false;
    }
    ################################################################
    #
    #  f u n c t i o n       D e l L i m i t s
    #
    function DelLimits($Domain) {
        if ($Status = $this->SockWrite("del_limits $Domain")) {
            $this->Error = "Error - write to Socket failed sending command! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status";
            return true;
        }
        return false;
    }
    ################################################################
    #
    #  f u n c t i o n       M o d U s e r
    #
    function ModUser($Domain, $User, $UserInfo) {
        #  NOTE:  If you want your users to be able to change passwords
        #         from ModUser, you must un-comment the name below.
        $StringParms = array('quota', 'comment', 'clear_text_password',);
        $FlagParms = array('no_password_change', 'no_pop', 'no_webmail', 'no_imap', 'no_smtp', 'bounce_mail', 'no_relay', 'no_dialup', 'user_flag_0', 'user_flag_1', 'user_flag_2', 'user_flag_3', 'system_admin_privileges', 'system_expert_privileges', 'domain_admin_privileges', 'override_domain_limits', 'no_spamassassin', 'delete_spam',);
        if ($Status = $this->SockWrite("mod_user $User@$Domain")) {
            $this->Error = "Error - write to Socket failed sending command! $Status";
            return;
        }
        while (list(, $Name) = each($StringParms)) {
            if (!empty($UserInfo[$Name])) {
                $Value = $UserInfo[$Name];
                if ($Status = $this->SockWrite("$Name $Value")) {
                    $this->Error = "Error - write to Socket failed sending string! $Status";
                    return;
                }
            }
        }
        while (list(, $Name) = each($FlagParms)) {
            #   $Flip = ( 'no_' == substr( $Name, 0, 3 ));
            $Flip = false;
            $Value = $this->GetGidBit($UserInfo['gidflags'], $Name, $Flip);
            $Value = ($Value) ? '1' : '0';
            if ($Status = $this->SockWrite("$Name $Value")) {
                $this->Error = "Error - write to Socket failed sending flag! $Status";
                return;
            }
        }
        if ($Status = $this->SockWrite(".")) {
            $this->Error = "Error - write to Socket failed sending end! $Status";
            return;
        }
        #  The following deleted because mod_user no longer returns warnings
        #$in = $this->SockRead();
        #$Warnings = array();
        #
        #while( '.' != $in{0} AND '+' != $in{0} AND '-' != $in{0} ) {
        #   if( '' != $in ) {
        #      $Warnings[] = $in;
        #      }
        #
        #   $in = $this->SockRead();
        #   }
        #
        #if( $this->ShowData ) {
        #   echo "\nWarnings collected: ";
        #   print_r( $Warnings );
        #   }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status";
            return true;
        }
        if (!empty($Warnings)) {
            return "Warning:\n   ".implode("\n   ", $Warnings) ."\n\n";
        }
        return false;
    }
    ################################################################
    #
    #  f u n c t i o n       A d d D o m a i n
    #
    function AddDomain($Domain, $Password) {
        $this->Error = '';
        if ($Status = $this->SockWrite("add_domain $Domain $Password")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status";
            return true;
        }
        return false;
    }
    ################################################################
    #
    #  f u n c t i o n       A d d A l i a s D o m a i n
    #
    function AddAliasDomain($Domain, $Alias) {
        $this->Error = '';
        if ($Status = $this->SockWrite("add_alias_domain $Domain $Alias")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status";
            return true;
        }
        return false;
    }
    ################################################################
    #
    #  f u n c t i o n       D e l D o m a i n
    #
    function DelDomain($Domain) {
        $this->Error = '';
        if ($Status = $this->SockWrite("del_domain $Domain")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status";
            return true;
        }
        return false;
    }
    ################################################################
    #
    #  f u n c t i o n      D o m a i n I n f o
    #
    function DomainInfo($Domain) {
        $this->Error = '';
        if ($Status = $this->SockWrite("dom_info $Domain")) {
            $this->Error = "Error - write to Socket failed! $Status\n";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status\n";
            return;
        }
        $UserInfo = $this->ReadDomainInfo();
        return $UserInfo;
    }
    ################################################################
    #
    #  f u n c t i o n       F i n d D o m a i n
    #
    function FindDomain($Domain, $PerPage) {
        $this->Error = '';
        if ($Status = $this->SockWrite("find_domain $Domain $PerPage")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status";
            return true;
        }
        while (!$this->dotOnly($in) AND !$this->statusOk($in) AND !$this->statusErr($in)) {
            #   echo "read: $in<BR>\n";
            list(, $Count) = explode(' ', $in, 2);
            $in = $this->SockRead();
        }
        return $Count;
        return false;
    }
    ################################################################
    #
    #  f u n c t i o n       A d d U s e r
    #
    function AddUser($Domain, $User, $Password, $Gecos) {
        $this->Error = '';
        if ($Status = $this->SockWrite("add_user $User@$Domain $Password $Gecos")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status";
            return true;
        }
        return false;
    }
    ################################################################
    #
    #  f u n c t i o n       D e l U s e r
    #
    function DelUser($Domain, $User) {
        $this->Error = '';
        if ($Status = $this->SockWrite("del_user $User@$Domain")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status";
            return true;
        }
        return false;
    }
    ################################################################
    #
    #  f u n c t i o n       G e t L a s t A u t h I P
    #
    function GetLastAuthIP($Domain, $User) {
        $this->Error = '';
        if ($Status = $this->SockWrite("get_lastauthip $User@$Domain")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status";
            return;
        }
        $in = $this->SockRead();
        return $in;
    }
    ################################################################
    #
    #  f u n c t i o n       G e t L a s t A u t h
    #
    function GetLastAuth($Domain, $User) {
        $this->Error = '';
        if ($Status = $this->SockWrite("get_lastauth $User@$Domain")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status";
            return;
        }
        $in = $this->SockRead();
        return $in;
    }
    ################################################################
    #
    #  f u n c t i o n       G e t L i m i t s
    #
    function GetLimits($Domain) {
        $this->Error = '';
        if ($Status = $this->SockWrite("get_limits $Domain")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status";
            return;
        }
        $Limits = $this->ReadLimits();
        return $Limits;
    }
    ################################################################
    #
    #  f u n c t i o n      R e a d F i l e
    #
    function ReadFile($Domain, $User = '', $Path = '') {
        $this->Error = '';
        $BasePath = "$Domain";
        if (!empty($User)) $BasePath = "$User@$BasePath";
        if (!empty($Path)) $BasePath.= "/".$Path;
        // if ($Status = $this->SockWrite("read_file $BasePath")) {
        $Status = $this->SockWrite("read_file $BasePath");
        if(PEAR::isError($Status)) return $Status;
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status";
            return;
        }
        $FileContents = array();
        $in = $this->SockRead();
        while (!$this->dotOnly($in) AND !$this->statusOk($in) AND !$this->statusErr($in)) {
            $FileContents[] = $in;
            $in = $this->RawSockRead();
        }
        return $FileContents;
    }
    ################################################################
    #
    #  f u n c t i o n      L i s t D i r
    #
    function ListDir($Domain, $User = '', $Path = '') {
        $this->Error = '';
        $BasePath = "$Domain";
        if (!empty($User)) $BasePath = "$User@$BasePath";
        if (!empty($Path)) $BasePath.= "/".$Path;
        $BasePath.= '/';
        #echo "ListDir BasePath: $BasePath\n";
        if ($Status = $this->SockWrite("list_dir $BasePath")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status";
            return;
        }
        $DirectoryContents = array();
        $in = $this->SockRead();
        while (!$this->dotOnly($in) AND !$this->statusOk($in) AND !$this->statusErr($in)) {
            list($DirName, $Type) = explode(' ', $in);
            $DirectoryContents[$DirName] = $Type;
            $in = $this->SockRead();
        }
        ksort($DirectoryContents);
        return $DirectoryContents;
    }
    ################################################################
    #
    #  f u n c t i o n      U s e r I n f o
    #
    function UserInfo($Domain, $User) {
        $this->Error = '';
        if ($Status = $this->SockWrite("user_info $User@$Domain")) {
            $this->Error = "Error - write to Socket failed! $Status\n";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status\n";
            return;
        }
        $UserInfo = $this->ReadUserInfo();
        return $UserInfo;
    }
    ################################################################
    #
    #  f u n c t i o n      L i s t U s e r s
    #
    function ListUsers($Domain, $Page = 0, $PerPage = 0) {
        $this->Error = '';
        if ($Status = $this->SockWrite("list_users $Domain $Page $PerPage")) {
            $this->Error = "Error - write to Socket failed! $Status\n";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $Status\n";
            return;
        }
        $I = 0;
        $CurrentName = '';
        $List = array();
        if ($this->ShowData) echo "<<--  Start collecting user data  -->>";
        $in = $this->SockRead();
        while (!$this->dotOnly($in) AND !$this->statusOk($in) AND !$this->statusErr($in) AND $I < 10) {
            list($Name, $Value) = explode(' ', $in, 2);
            #   echo "Name: $Name  Value: $Value\n";
            if ('name' == $Name) { #  Have name
                if (!empty($CurrentName)) { #   Save old name
                    #         echo "Save info for $CurrentName\n";
                    #         $I++;
                    $List[$CurrentName] = $User;
                } #   Save old name
                else { #  No old name
                    #         echo "Start first entry $Name\n";
                    
                } #   No old name
                $CurrentName = $Value;
                $User = array();
            } #  Have New Name
            else { #   Not name
                $User[$Name] = trim($Value);
            }
            $in = $this->SockRead();
        }
        if (!empty($CurrentName)) { #   Save old name
            #   echo "Save info for $CurrentName\n";
            #   $I++;
            $List[$CurrentName] = $User;
        } #   Save old name
        if ($this->ShowData) echo "<<--  Stop collecting user data  -->>";
        #ksort( $List );
        return $List;
    }
    #  The old way to parse users
    #
    #   $Exploded = explode( ':', $in );
    #   $User = array_shift( $Exploded );
    #   $List[ $User ][ 'passwd' ]   = $Exploded[ 0 ];
    #   $List[ $User ][ 'uid' ]      = $Exploded[ 1 ];
    #   $List[ $User ][ 'gid' ]      = $Exploded[ 2 ];
    #   $List[ $User ][ 'flags' ]    = $Exploded[ 3 ];
    #   $List[ $User ][ 'gecos' ]    = $Exploded[ 4 ];
    #   $List[ $User ][ 'dir' ]      = $Exploded[ 5 ];
    #   $List[ $User ][ 'shell' ]    = $Exploded[ 6 ];
    #   $List[ $User ][ 'clear_pw' ] = $Exploded[ 7 ];
    ################################################################
    #
    #  f u n c t i o n      L i s t D o m a i n s
    #
    function ListDomains($Page = 0, $PerPage = 0) {
        $this->Error = '';
        if ($Status = $this->SockWrite("list_domains $Page $PerPage")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $in";
            return;
        }
        $Domains = array();
        $List = array();
        $in = $this->SockRead();
        while (!$this->dotOnly($in) AND !$this->statusOk($in) AND !$this->statusErr($in)) {
            #   echo "read: $in<BR>\n";
            list($Parent, $Domain) = explode(' ', $in, 2);
            $Domains[$Domain] = $Parent;
            $in = $this->SockRead();
        }
        return $Domains;
    }
    ################################################################
    #
    #  f u n c t i o n      D o m a i n C o u n t
    #
    function DomainCount() {
        $this->Error = '';
        if ($Status = $this->SockWrite("domain_count")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $in";
            return;
        }
        $Domains = array();
        $List = array();
        $in = $this->SockRead();
        while (!$this->dotOnly($in) AND !$this->statusOk($in) AND !$this->statusErr($in)) {
            #   echo "read: $in<BR>\n";
            list(, $Count) = explode(' ', $in, 2);
            $in = $this->SockRead();
        }
        return $Count;
    }
    ################################################################
    #
    #  f u n c t i o n      U s e r C o u n t
    #
    function UserCount($Domain) {
        $this->Error = '';
        if ($Status = $this->SockWrite("user_count $Domain")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->statusOk($Status)) {
            $this->Error = "command failed - $in";
            return;
        }
        $in = $this->SockRead();
        while (!$this->dotOnly($in) AND !$this->statusOk($in) AND !$this->statusErr($in)) {
            #   echo "read: $in<BR>\n";
            list(, $Count) = explode(' ', $in, 2);
            $in = $this->SockRead();
        }
        return $Count;
    }
    ################################################################
    #
    #  f u n c t i o n      G e t L o g i n U s e r
    #
    function GetLoginUser() {
        $this->Error = '';
        return $this->LoginUser;
    }
    ################################################################
    #
    #  f u n c t i o n      E r r o r M e s s a g e
    #
    function ErrorMessage() {
        if (empty($this->Error)) {
            return false;
        } else {
            return $this->Error."\n";
        }
    }
    function quit() {
        $this->sockWrite("quit\n");
        $this->socket->disconnect();
    }
    ################################################################
    #
    #  f u n c t i o n      R e a d U s e r I n f o
    #
    function ReadUserInfo() {
        if ($this->ShowRecv) echo "<<--  Start ReadUserInfo  -->>\n";
        $UserArray = array();
        $in = $this->SockRead();
        while (!$this->dotOnly($in) AND !$this->statusOk($in) AND !$this->statusErr($in)) {
            if ('' != $in) {
                unset($Value);
                list($Name, $Value) = explode(' ', $in, 2);
                $Value = trim($Value);
                $UserArray[$Name] = $Value;
            }
            $in = $this->SockRead();
        }
        if ($this->ShowData) {
            echo "\nReadUserInfo collected: ";
            print_r($UserArray);
        }
        if ($this->ShowRecv) echo "<<--  Finish ReadUserInfo  -->>\n";
        return $UserArray;
    }
    ################################################################
    #
    #  f u n c t i o n      R e a d L i m i t s
    #
    function ReadLimits() {
        if ($this->ShowRecv) echo "<<--  Start ReadUserInfo  -->>\n";
        $UserArray = array();
        $in = $this->SockRead();
        while (!$this->dotOnly($in) AND !$this->statusOk($in) AND !$this->statusErr($in)) {
            if ('' != $in) {
                unset($Value);
                list($Name, $Value) = explode(' ', $in, 2);
                $Value = trim($Value);
                $UserArray[$Name] = $Value;
            }
            $in = $this->SockRead();
        }
        if ($this->ShowData) {
            echo "\nReadUserInfo collected: ";
            print_r($UserArray);
        }
        if ($this->ShowRecv) echo "<<--  Finish ReadUserInfo  -->>\n";
        return $UserArray;
    }
    ################################################################
    #
    #  f u n c t i o n      R e a d D o m a i n I n f o
    #
    function ReadDomainInfo() {
        if ($this->ShowRecv) echo "<<--  Start ReadDomainInfo  -->>\n";
        $UserArray = array();
        $in = $this->SockRead();
        while (!$this->dotOnly($in) AND !$this->statusOk($in) AND !$this->statusErr($in)) {
            if ('' != $in) {
                unset($Value);
                list($Name, $Value) = explode(' ', $in, 2);
                $Value = trim($Value);
                if ('alias' == $Name) {
                    $Aliases[] = $Value;
                } else {
                    $UserArray[$Name] = $Value;
                }
            }
            $in = $this->SockRead();
        }
        if (count($Aliases) > 0) {
            $UserArray['aliases'] = $Aliases;
        }
        if ($this->ShowData) {
            echo "\nReadUserInfo collected: ";
            print_r($UserArray);
        }
        if ($this->ShowRecv) echo "<<--  Finish ReadUserInfo  -->>\n";
        return $UserArray;
    }

    /**
     * __destruct 
     * 
     * @access protected
     * @return void
     */
    function __destruct() {
        if($this->socket instanceof Net_Socket) {
            $this->quit();
            $this->socket->disconnect();
        }
    }

}


?>
