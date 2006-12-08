<?php
/**
 *
 * vpopmaild.php (vpopmaild.pobj)
 *
 * This class makes vpopmaild functions available.  Requires sockets.
 *
 * @author Rick Widmer
 * @package Vpopmaild
 * @version 1.0
 *
 */
class Framework_User_vpopmail extends Framework_User {
    /**
     * Socket 
     * 
     * @var mixed
     * @access public
     */
    public $Socket = null;
    /**
     * Error 
     * 
     * @var mixed
     * @access public
     */
    public $Error = null;
    /**
     * LoginUser 
     * 
     * @var mixed
     * @access public
     */
    public $LoginUser = null;
    /**
     * Compact 
     * 
     * @var mixed
     * @access public
     */
    public $Compact = null;
    /**
     * ShowXmit 
     * 
     * @var mixed
     * @access public
     */
    public $ShowXmit = false;
    /**
     * ShowRecv 
     * 
     * @var mixed
     * @access public
     */
    public $ShowRecv = false;
    /**
     * ShowData 
     * 
     * @var mixed
     * @access public
     */
    public $ShowData = false;
    /**
     * ShowCmd 
     * 
     * @var mixed
     * @access public
     */
    public $ShowCmd = false;
    /**
     * GidFlagValues 
     * 
     * @var array
     * @access public
     */
    public $GidFlagValues = array(
        'no_password_change' => 0x01, 
        'no_pop' => 0x02, 
        'no_webmail' => 0x04, 
        'no_imap' => 0x08, 
        'bounce_mail' => 0x10, 
        'no_relay' => 0x20, 
        'no_dialup' => 0x40, 
        'user_flag_0' => 0x080, 
        'user_flag_1' => 0x100, 
        'user_flag_2' => 0x200, 
        'user_flag_3' => 0x400, 
        'no_smtp' => 0x800, 
        'domain_admin_privileges' => 0x1000, 
        'override_domain_limits' => 0x2000, 
        'no_spamassassin' => 0x4000, 
        'delete_spam' => 0x8000, 
        'system_admin_privileges' => 0x10000, 
        'system_expert_privileges' => 0x20000);


    ################################################################
    #
    #  f u n c t i o n      v p o p m a i l d  ( C o n s t r u c t o r )
    #
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

    // public function authenticate($Email, $Password)
    // {

        // if($Email == (string)Framework::$site->config->loginEmail &&
            // $Password == (string)Framework::$site->config->loginPass) {
            // return true;
        // }
        // return false;
    // }

    ################################################################
    #
    #  f u n c t i o n      S t a t u s O k
    #
    function StatusOk($data) {
        if (ereg('^[+]OK', $data)) return TRUE;
        return FALSE;
    }
    ################################################################
    #
    #  f u n c t i o n      S t a t u s O k M o r e
    #
    function StatusOkMore($data) {
        if (ereg('^[+]OK[+]$', $data)) return TRUE;
        return FALSE;
    }
    ################################################################
    #
    #  f u n c t i o n      S t a t u s O k No More
    #
    function StatusOkNoMore($data) {
        if (ereg('^[+]OK$', $data)) return TRUE;
        return FALSE;
    }
    ################################################################
    #
    #  f u n c t i o n      S t a t u s E r r
    #
    function StatusErr($data) {
        if (ereg('^[-]ERR ', $data)) return TRUE;
        return FALSE;
    }
    ################################################################
    #
    #  f u n c t i o n      D o t O n l y
    #
    function DotOnly($data) {
        if (ereg('^[.]$', $data)) return TRUE;
        return FALSE;
    }
    ################################################################
    #
    #  f u n c t i o n       G e t G i d B i t
    #
    function GetGidBit($Bitmap, $Bit, $Flip = false) {
        $this->Error = '';
        #echo "<BR>GetGidBit - Bitmap: $Bitmap  Bit: $Bit Flip: $Flip<BR>\n";
        #echo "<PRE>GidFlags: ";print_r($this->GidFlagValues);echo"</PRE>\n";
        if (!isset($this->GidFlagValues[$Bit])) {
            echo "Error - unknown GID Bit value specified. $Bit<BR>\n";
            $this->Error = 'Unknown GID Bit value specified. $Bit';
            return -1;
        }
        $BitValue = $this->GidFlagValues[$Bit];
        #echo "BitValue: $BitValue<BR>\n";
        if ($Flip) {
            $Return = ($Bitmap&$BitValue) ? false : true;
        } else {
            $Return = ($Bitmap&$BitValue) ? true : false;
        }
        #echo "Returning: $Return<BR>\n";
        return ($Return);
    }
    ################################################################
    #
    #  f u n c t i o n       S e t G i d B i t
    #
    function SetGidBit(&$Bitmap, $Bit, $Value, $Flip = false) {
        $this->Error = '';
        #echo "SetGidBit - Bitmap: $Bitmap  Bit: $Bit Value: $Value<BR>\n";
        if (!isset($this->GidFlagValues[$Bit])) {
            #   echo "Unknown GID bit value specified. $Bit<BR>\n";
            $this->Error = "Unknown GID Bit value specified. $Bit";
            return -1;
        }
        $BitValue = $this->GidFlagValues[$Bit];
        #echo "BitValue: $BitValue<BR>\n";
        if ($Flip) {
            $Value = ('t' == $Value{0}) ? 0 : $BitValue;
        } else {
            $Value = ('t' == $Value{0}) ? $BitValue : 0;
        }
        #echo "Value: $Value<BR>\n";
        $Bitmap = (int)$Value|(~(int)$BitValue&(int)$Bitmap);
        #echo "Bitmap After: $Bitmap<HR><BR>\n";
        
    }
    ################################################################
    #
    #  f u n c t i o n      G e t I P M a p
    #
    function GetIPMap($IP) {
        $this->Error = '';
        if ($Status = $this->SockWrite("get_ip_map $IP")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if ($this->StatusErr($Status)) {
            $this->Error = "command failed - $Status";
            return;
        }
        $Lists = array();
        $in = $this->SockRead();
        while (!$this->StatusErr($in) AND !$this->StatusOk($in) AND !$this->DotOnly($in)) {
            $Lists[] = $in;
            $in = $this->SockRead();
        }
        $Exploded = explode(" ", $Lists[0]);
        #echo "Exploded: "; print_r( $Exploded );
        return $Exploded[1];
    }
    ################################################################
    #
    #  f u n c t i o n      A d d I P M a p
    #
    function AddIPMap($Domain, $IP) {
        $this->Error = '';
        if ($Status = $this->SockWrite("add_ip_map $IP $Domain")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->StatusOk($Status)) {
            $this->Error = "command failed - $Status";
            return true;
        }
        return false;
    }
    ################################################################
    #
    #  f u n c t i o n      D e l I P M a p
    #
    function DelIPMap($Domain, $IP) {
        $this->Error = '';
        if ($Status = $this->SockWrite("del_ip_map $IP $Domain")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->StatusOk($Status)) {
            $this->Error = "command failed - $Status";
            return true;
        }
        return false;
    }
    ################################################################
    #
    #  f u n c t i o n      S h o w I P M a p
    #
    function ShowIPMap() {
        $this->Error = '';
        if ($Status = $this->SockWrite("show_ip_map")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->StatusOk($Status)) {
            $this->Error = "command failed - $Status";
            return;
        }
        $Lists = array();
        $in = $this->SockRead();
        while (!$this->DotOnly($in) AND !$this->StatusOk($in) AND !$this->StatusError($in)) {
            list($IP, $Domain) = explode(' ', $in);
            if (!empty($Lists[$IP])) {
                $Lists[$IP].= ", ".$Domain;
            } else { #  Not duplicate
                $Lists[$IP] = $Domain;
            }
            $in = $this->SockRead();
        }
        ksort($Lists);
        return $Lists;
    }
    ################################################################
    #
    #  f u n c t i o n      S o c k W r i t e
    #
    function SockWrite($Data) {
        if ($this->ShowXmit) {
            echo "SockWrite sending: $Data\n";
        }
        $Data.= "\n";
        $DataLen = strlen($Data);
        $SendLen = Socket_write($this->Socket, $Data);
        if ($this->ShowXmit > 1) {
            echo "SockWrite result - Data Len: $DataLen  SendLen: $SendLen\n";
        }
        if ($DataLen != $SendLen) {
            return "Wrong length sent";
        }
        return false;
    }
    ################################################################
    #
    #  f u n c t i o n      S o c k R e a d
    #
    function SockRead($MaxLen = 2048) {
        $in = '';
        while ('' == $in) {
            $in = trim(Socket_read($this->Socket, $MaxLen, PHP_NORMAL_READ));
            if ($this->ShowRecv > 1) {
                echo "SockRead Read: $in\n";
            }
        }
        if ($this->ShowRecv) {
            echo "SockRead Returned: $in\n";
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
            echo "SockRead Returned: $in\n";
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
        if (!$this->StatusOk($Status)) {
            $this->Error = "command failed - $Status";
            return;
        }
        $Lists = array();
        $in = $this->SockRead();
        while (!$this->DotOnly($in) AND !$this->StatusOk($in) AND !$this->StatusErr($in)) {
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
        if (!$this->StatusOk($Status)) {
            $this->Error = "command failed - $Status";
            return;
        }
        $Alii = array();
        $in = $this->SockRead();
        while (!$this->DotOnly($in) AND !$this->StatusOk($in) AND !$this->StatusErr($in)) {
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
        if (!$this->StatusOk($Status)) {
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
        if (!$this->StatusOk($Status)) {
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
        if (!$this->StatusOk($Status)) {
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
        if (!$this->StatusOk($Status)) {
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
        if (!$this->StatusOk($Status)) {
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
        if (!$this->StatusOk($Status)) {
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
        if (!$this->StatusOk($Status)) {
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
        if (!$this->StatusOk($Status)) {
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
        if (!$this->StatusOk($Status)) {
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
        if (!$this->StatusOk($Status)) {
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
        if (!$this->StatusOk($Status)) {
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
        if (!$this->StatusOk($Status)) {
            $this->Error = "command failed - $Status";
            return true;
        }
        while (!$this->DotOnly($in) AND !$this->StatusOk($in) AND !$this->StatusErr($in)) {
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
        if (!$this->StatusOk($Status)) {
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
        if (!$this->StatusOk($Status)) {
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
        if (!$this->StatusOk($Status)) {
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
        if (!$this->StatusOk($Status)) {
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
        if (!$this->StatusOk($Status)) {
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
        if ($Status = $this->SockWrite("read_file $BasePath")) {
            $this->Error = "Error - write to Socket failed! $Status";
            return;
        }
        $Status = $this->SockRead();
        if (!$this->StatusOk($Status)) {
            $this->Error = "command failed - $Status";
            return;
        }
        $FileContents = array();
        $in = $this->SockRead();
        while (!$this->DotOnly($in) AND !$this->StatusOk($in) AND !$this->StatusErr($in)) {
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
        if (!$this->StatusOk($Status)) {
            $this->Error = "command failed - $Status";
            return;
        }
        $DirectoryContents = array();
        $in = $this->SockRead();
        while (!$this->DotOnly($in) AND !$this->StatusOk($in) AND !$this->StatusErr($in)) {
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
        if (!$this->StatusOk($Status)) {
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
        if (!$this->StatusOk($Status)) {
            $this->Error = "command failed - $Status\n";
            return;
        }
        $I = 0;
        $CurrentName = '';
        $List = array();
        if ($this->ShowData) echo "<<--  Start collecting user data  -->>";
        $in = $this->SockRead();
        while (!$this->DotOnly($in) AND !$this->StatusOk($in) AND !$this->StatusErr($in) AND $I < 10) {
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
        if (!$this->StatusOk($Status)) {
            $this->Error = "command failed - $in";
            return;
        }
        $Domains = array();
        $List = array();
        $in = $this->SockRead();
        while (!$this->DotOnly($in) AND !$this->StatusOk($in) AND !$this->StatusErr($in)) {
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
        if (!$this->StatusOk($Status)) {
            $this->Error = "command failed - $in";
            return;
        }
        $Domains = array();
        $List = array();
        $in = $this->SockRead();
        while (!$this->DotOnly($in) AND !$this->StatusOk($in) AND !$this->StatusErr($in)) {
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
        if (!$this->StatusOk($Status)) {
            $this->Error = "command failed - $in";
            return;
        }
        $in = $this->SockRead();
        while (!$this->DotOnly($in) AND !$this->StatusOk($in) AND !$this->StatusErr($in)) {
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
    ################################################################
    #
    #  f u n c t i o n      Q u i t
    #
    function Quit() {
        $out = "quit\n";
        Socket_write($this->Socket, $out);
        Socket_shutdown($this->Socket, 2);
        Socket_close($this->Socket);
        unset($this->Socket);
    }
    ################################################################
    #
    #  f u n c t i o n      R e a d U s e r I n f o
    #
    function ReadUserInfo() {
        if ($this->ShowRecv) echo "<<--  Start ReadUserInfo  -->>\n";
        $UserArray = array();
        $in = $this->SockRead();
        while (!$this->DotOnly($in) AND !$this->StatusOk($in) AND !$this->StatusErr($in)) {
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
        while (!$this->DotOnly($in) AND !$this->StatusOk($in) AND !$this->StatusErr($in)) {
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
        while (!$this->DotOnly($in) AND !$this->StatusOk($in) AND !$this->StatusErr($in)) {
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
    /////////////////////////////////////////
    /////////////////////////////////////////
    
    /**
     *
     * Extensions to Vpopmaild for Bill's ToasterAdmin
     *
     * This class extends Vpopmaild with some ToasterAdmin specific functions
     *
     * @author Bill Shupp <hostmaster@shupp.org>
     * @package ToasterAdmin
     * @category ToasterAdminCat
     * @version 1.0
     *
     */
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
     * Has Domain Privs
     *
     * Determin if this user have privileges on this domain
     *
     * @author Bill Shupp <hostmaster@shupp.org>
     *
     */
    function has_domain_privs($domain, $acct_info = '') {
        if ($acct_info == '') {
            global $user_info;
            $acct_info = $user_info;
        }
        if ($this->has_sysadmin_privs()) return TRUE;
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

    /**
     * encryptPass 
     * 
     * @param mixed $pass 
     * @param mixed $key 
     * @access public
     * @return void
     */
    function encryptPass($pass, $key) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $cryptpass = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $pass, MCRYPT_MODE_ECB, $iv);
        return $cryptpass;
    }

    /**
     * decryptPass 
     * 
     * @param mixed $encryptedpass 
     * @param mixed $key 
     * @access public
     * @return void
     */
    function decryptPass($encryptedpass, $key) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $clearpass = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $encryptedpass, MCRYPT_MODE_ECB, $iv);
        return trim($clearpass);
    }

}
?>
