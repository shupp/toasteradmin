<?php

/**
 * Vpopmail_Main 
 * 
 * @uses Vpopmail_Base
 * @package ToasterAdmin
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @author Rick Widmer
 * @license PHP 3.01  {@link http://www.php.net/license/3_01.txt}
 */

/**
 *  VPOPMAIL_ROBOT_TIME
 *  
 *  define VPOPMAIL_ROBOT_TIME
 */
define('VPOPMAIL_ROBOT_TIME', 1000);
/**
 *  VPOPMAIL_ROBOT_NUMBER
 *  
 *  define VPOPMAIL_ROBOT_NUMBER
 */
define('VPOPMAIL_ROBOT_NUMBER', 5);

/**
 * Vpopmail_Main 
 * 
 * @uses Vpopmail_Base
 * @package ToasterAdmin
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @author Rick Widmer
 * @license PHP 3.01  {@link http://www.php.net/license/3_01.txt}
 */
class Vpopmail_Main extends Vpopmail_Base {

    /**
     * clogin 
     * 
     * @param mixed $email 
     * @param mixed $password 
     * @access public
     * @return mixed true on success, PEAR_Error on failure
     */
    public function clogin($email, $password)
    {
        $status = $this->sockWrite("clogin $email $password");
        if(PEAR::isError($status)) return $status;
        $in = $this->sockRead();
        if(PEAR::isError($in)) return $in;
        if (!$this->StatusOk($in)) {
            return PEAR::raiseError("Login failed - " . $in);
        }
        $this->loginUser = $this->readInfo();
        if(PEAR::isError($this->loginUser)) return $this->loginUser;
        return true;
    }
    /**
     * getGidBit 
     * 
     * @param mixed $bitmap 
     * @param mixed $bit 
     * @param mixed $flip 
     * @access public
     * @return mixed true or false, or PEAR_Error on failure
     */
    public function getGidBit($bitmap, $bit, $flip = false)
    {
        if (!isset($this->gidFlagValues[$bit])) {
            return PEAR::raiseError("Error - unknown GID Bit value specified. $bit");
        }
        $bitValue = $this->gidFlagValues[$bit];
        if ($flip) return ($bitmap&$bitValue) ? false : true;
        return ($bitmap&$bitValue) ? true : false;
    }

    /**
     * setGidBit 
     * 
     * @param mixed $bitmap 
     * @param mixed $bit 
     * @param mixed $value 
     * @param mixed $flip 
     * @access public
     * @return mixed PEAR_Error on unknown bit value, true on success
     */
    public function setGidBit(&$bitmap, $bit, $value, $flip = false)
    {
        if (!isset($this->gidFlagValues[$bit])) 
            return PEAR::raiseError("Unknown GID Bit value specified. $bit");
        $bitValue = $this->gidFlagValues[$bit];
        if ($flip) {
            $value = ('t' == $value{0}) ? 0 : $bitValue;
        } else {
            $value = ('t' == $value{0}) ? $bitValue : 0;
        }
        $bitmap = (int)$value|(~(int)$bitValue&(int)$bitmap);
        return true;
    }

    /**
     * getIPMap 
     * 
     * @param mixed $ip 
     * @access public
     * @return mixed PEAR_Error on failure, IP Map on success
     */
    public function getIPMap($ip)
    {
        if ($status = $this->sockWrite("get_ip_map $ip")) {
            return PEAR::raiseError("Error - write to socket failed! $status");
        }
        $status = $this->sockRead();
        if ($this->statusErr($status)) {
            return PEAR::raiseError("command failed - $Status");
        }
        $lists = array();
        $in = $this->sockRead();
        while (!$this->statusErr($in) && !$this->statusOk($in) && !$this->dotOnly($in)) {
            $lists[] = $in;
            $in = $this->sockRead();
        }
        $exploded = explode(" ", $lists[0]);
        return $exploded[1];
    }

    /**
     * addIPMap 
     * 
     * @param mixed $domain 
     * @param mixed $ip 
     * @access public
     * @return mixed true on success, PEAR_Error on failure
     */
    public function addIPMap($domain, $ip)
    {
        $status = $this->sockWrite("add_ip_map $ip $domain");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if (!$this->statusOk($status)) {
            return PEAR::raiseError("command failed - $status");
        }
        return true;
    }
    /**
     * delIPMap 
     * 
     * @param mixed $domain 
     * @param mixed $ip 
     * @access public
     * @return mixed true on success, PEAR_Error on failure
     */
    public function delIPMap($domain, $ip)
    {
        $status = $this->sockWrite("del_ip_map $ip $domain");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if (!$this->statusOk($status)) 
            return PEAR::raiseError("command failed - $status");
        return true;
    }
    /**
     * showIPMap 
     * 
     * return sorted ip map list
     * 
     * @access public
     * @return mixed PEAR_Error on failure, ip map array on success
     */
    public function showIPMap()
    {
        $status = $this->sockWrite("show_ip_map");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if (!$this->statusOk($status)) 
            return PEAR::raiseError("command failed - $status");
        $lists = array();
        $in = $this->sockRead();
        if(PEAR::isError($in)) return $in;
        while (!$this->dotOnly($in) && !$this->statusOk($in) && !$this->statusError($in)) {
            list($ip, $domain) = explode(' ', $in);
            if (!empty($lists[$ip])) {
                $lists[$ip].= ", ".$domain;
            } else { #  Not duplicate
                $lists[$ip] = $domain;
            }
            $in = $this->sockRead();
            if(PEAR::isError($in)) return $in;
        }
        ksort($lists);
        return $lists;
    }

    /**
     * formatBasePath 
     * 
     * @param mixed $domain 
     * @param string $user 
     * @param string $path 
     * @param string $type 
     * @access public
     * @return var $basePath
     */
    public function formatBasePath($domain, $user = '', $path = '', $type = 'file')
    {
        $basePath = $domain;
        if (!empty($user)) $basePath  = "$user@$basePath";
        if (!empty($path)) $basePath .= "/" . $path;
        if($type = 'dir') $basePath.= '/';
        $basePath = ereg_replace('//', '/', $basePath);
        return $basePath;
    }
    /**
     * readInfo 
     * 
     * Collect user/dom info into an Array and return.
     * NOTE:  +OK has already been read.
     * 
     * @access public
     * @return mixed info array on success, PEAR_Error on failure
     */
    public function readInfo()
    {
        $this->recordio("<<--  Start readInfo  -->>");
        $infoArray = array();
        $in = $this->sockRead();
        if(PEAR::isError($in)) return $in;
        while (!$this->dotOnly($in) && !$this->statusOk($in) && !$this->statusErr($in)) {
            if ('' != $in) {
                unset($value);
                list($name, $value) = explode(' ', $in, 2);
                $value = trim($value);
                $infoArray[$name] = $value;
            }
            $in = $this->sockRead();
            if(PEAR::isError($in)) return $in;
        }
        $this->recordio("readInfo collected: ");
        $this->recordio(print_r($infoArray, 1));
        $this->recordio("<<--  Finish readInfo  -->>");
        return $infoArray;
    }
    /**
     * dotQmailSplit 
     * 
     * @param mixed $fileContents 
     * @access public
     * @return array
     */
    public function dotQmailSplit($fileContents)
    {
        $result = array('Comment' => array(), 'Program' => array(), 'Delivery' => array(), 'Forward' => array(),);
        if (!is_array($fileContents)) {
            return $result;
        }
        reset($fileContents);
        while (list(, $line) = each($fileContents)) {
            switch ($line{0}) {
                case '#':
                    $result['Comment'][] = $line;
                break;
                case '|':
                    $result['Program'][] = $line;
                break;
                case '/':
                    $result['Delivery'][] = $line;
                break;
                case '&':
                default:
                    $result['Forward'][] = $line;
                break;
            }
        }
        return $result;
    }


    /**
     * robotDel 
     * 
     * @param mixed $domain 
     * @param mixed $user 
     * @access public
     * @return mixed PEAR_Error on failure, true on success
     */
    public function robotDel($domain, $user)
    {
        $result = $this->robotGet($domain, $user);
        if(PEAR::isError($result)) return $result;
        $robotDir = strtoupper($user);
        $dotQmailName = ".qmail-$user";

        // Get domain directory for robotPath
        $domainArray = $this->domainInfo($domain);
        if(PEAR::isError($domainArray)) return $domainArray;
        $robotPath = $domainArray['path']."/$robotDir";
        $result = $this->rmDir($robotPath);
        if(PEAR::isError($result)) return $result;
        $result = $this->RmFile($domain, '', $dotQmailName);
        if(PEAR::isError($result)) return $result;
        return true;
    }

    /**
     * robotSet 
     * 
     * @param mixed $domain 
     * @param mixed $user 
     * @param mixed $subject 
     * @param mixed $message 
     * @param mixed $forward 
     * @param mixed $time 
     * @param mixed $number 
     * @access public
     * @return mixed true on success, PEAR_Error on failure
     */
    public function robotSet($domain, $user, $subject, $message, $forward, $time = VPOPMAIL_ROBOT_TIME, $number = VPOPMAIL_ROBOT_NUMBER)
    {
        $robotDir = strtoupper($user);
        $dotQmailName = ".qmail-$user";

        // Get domain directory for robotPath
        $domainArray = $this->domainInfo($domain);
        if(PEAR::isError($domainArray)) return $domainArray;
        $robotPath = $domainArray['path']."/$robotDir";

        $messagePath = "$robotPath/message";
        $program = VPOPMAIL_ROBOT_PROGRAM;
        #  Build the dot qmail file
        $dotQmail = array("|$program $time $number $messagePath $robotPath");
        if (is_array($forward)) {
            array_merge($dotQmail, $forward);
        } elseif (is_string($forward)) {
            $dotQmail[] = $forward;
        }
        $result = $this->writeFile($dotQmail, $domain, '', $dotQmailName);
        if(PEAR::isError($result)) return $result;
        $result = $this->mkDir($domain, '', $robotDir);
        if(PEAR::isError($result)) return $result;
        #  NOTE:  You have to add them backwards!
        array_unshift($message, "");
        array_unshift($message, "Subject: $subject");
        array_unshift($message, "From: $user@$domain");
        $result = $this->writeFile($message, $messagePath);
        if(PEAR::isError($result)) return $result;
        return true;
    }

    /**
     * robotGet 
     * 
     * @param mixed $domain 
     * @param mixed $user 
     * @access public
     * @return mixed PEAR_Error on failure, robot array on success
     */
    public function robotGet($domain, $user)
    {
        $robotPath = strtoupper($user) .'/';
        $dotQmailName = ".qmail-$user";
        $dotQmail = $this->readFile($domain, '', $dotQmailName);
        if(PEAR::isError($dotQmail))
            return PEAR::raseError("ERR - Unable to find dotqmail file - " . $dotQmail->getMessage());
        $this->recordio("dotQmail: " . print_r($dotQmail, 1));
        $dotQmail = $this->dotQmailSplit($dotQmail);
        $this->recordio("dotQmaili split: " . print_r($dotQmail, 1));
        if (count($dotQmail['Program']) > 1)  #  Too many programs
            return PEAR::raiseError('ERR - too many programs in robot dotqmail file');
        if (!ereg(VPOPMAIL_ROBOT_PROGRAM, $dotQmail['Program'][0]))
            return PEAR::raiseError('ERR - Mail Robot program not found');
        list($Program, $Time, $Number, $MessageFile, $RobotPath) = explode(' ', $dotQmail['Program'][0]);
        $message = $this->readFile($MessageFile);
        if(PEAR::isError($message))
            return PEAR::raseError("ERR - Unable to find message file - " . $dotQmail->getMessage());
        $result = array();
        $result['Time'] = $Time;
        $result['Number'] = $Number;
        array_shift($Message); #   Eat From: address
        $result['Subject'] = substr(array_shift($message), 9);
        array_shift($Message); #  eat blank line
        if (0 == count($dotQmail['Forward'])) { #  Empty
            $Result['Forward'] = '';
        } elseif (count($dotQmail['Forward']) > 1) { #  array
            $Result['Forward'] = $dotQmail['Forward'];
        } else { #  Single entry
            $Result['Forward'] = $dotQmail['Forward'][0];
        }
        $result['Message'] = $Message;
        return $result;
    }


    /**
     * listLists 
     * 
     * @param mixed $domain 
     * @param string $user 
     * @access public
     * @return mixed lists array on success or PEAR_Error on failure
     */
    public function listLists($domain, $user = '')
    {
        $basePath = $this->formatBasePath($domain, $user);
        $status = $this->sockWrite("list_lists $basePath");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if (!$this->statusOk($Status))
            return PEAR::raiseError("command failed - $status");
        $lists = array();
        $in = $this->sockRead();
        while (!$this->dotOnly($in) && !$this->statusOk($in) && !$this->statusErr($in)) {
            $lists[] = $in;
            $in = $this->sockRead();
            if(PEAR::isError($in)) return $in;
        }
        return $lists;
    }

    /**
     * listAlias 
     * 
     * @param mixed $domain 
     * @param string $user 
     * @access public
     * @return mixed alias array on success, PEAR_Error on failure
     */
    public function listAlias($domain, $user = '')
    {
        $basePath = $this->formatBasePath($domain, $user);
        $status = $this->sockWrite("list_alias $basePath");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if (!$this->statusOk($status))
            return PEAR::raiseError("command failed - $status");
        $alii = array();
        $in = $this->sockRead();
        if(PEAR::isError($in)) return $in;
        while (!$this->dotOnly($in) && !$this->statusOk($in) && !$this->statusErr($in)) {
            $alii[] = $in;
            $in = $this->sockRead();
            if(PEAR::isError($in)) return $in;
        }
        return $alii;
    }

    /**
     * rmFile 
     * 
     * @param mixed $domain 
     * @param string $user 
     * @param string $path 
     * @access public
     * @return mixed true on success, PEAR_Error on failure
     */
    public function rmFile($domain, $user = '', $path = '')
    {
        $basePath = $this->formatBasePath($domain, $user, $path);
        $status = $this->sockWrite("rm_file $basePath");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if (!$this->statusOk($status))
            return PEAR::raiseError("command failed - $status");
        return true;
    }

    /**
     * writeFile 
     * 
     * @param mixed $contents 
     * @param mixed $domain 
     * @param string $user 
     * @param string $path 
     * @access public
     * @return mixed true on success, PEAR_Error on failure
     */
    public function writeFile($contents, $domain, $user = '', $path = '')
    {
        $basePath = $this->formatBasePath($domain, $user, $path);
        $status = $this->sockWrite("write_file $basePath");
        if(PEAR::isError($status)) return $status;
        reset($contents);
        while (list(, $line) = each($contents)) {
            $status = $this->sockWrite($line);
            if(PEAR::isError($status)) return $status;
        }
        $status = $this->sockWrite(".");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if (!$this->statusOk($Status))
            return PEAR::raiseError("command failed - $status");
        return true;
    }

    /**
     * readFile 
     * 
     * @param mixed $domain 
     * @param string $user 
     * @param string $path 
     * @access public
     * @return mixed file contents as array on success, PEAR_Error on failure
     */
    public function readFile($domain, $user = '', $path = '')
    {
        $basePath = $domain;
        if (!empty($user)) $basePath  = "$user@$basePath";
        if (!empty($path)) $basePath .= "/".$path;
        $status = $this->sockWrite("read_file $pasePath");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if (!$this->statusOk($status))
            return PEAR::raiseError("command failed - $status");
        $fileContents = array();
        $in = $this->sockRead();
        while (!$this->dotOnly($in) && !$this->statusOk($in) && !$this->statusErr($in)) {
            $fileContents[] = $in;
            $in = $this->rawSockRead();
            if(PEAR::isError($status)) return $status;
        }
        return $fileContents;
    }

    /**
     * listDir 
     * 
     * @param mixed $domain 
     * @param string $user 
     * @param string $path 
     * @access public
     * @return array of directory contents, or PEAR_Error on failure
     */
    public function listDir($domain, $user = '', $path = '')
    {
        $basePath = $this->formatBasePath($domain, $user, $path, 'dir');
        $status = $this->sockWrite("list_dir $basePath");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if (!$this->statusOk($status))
            return PEAR::raiseError("command failed - $status");
        $directoryContents = array();
        $in = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        while (!$this->dotOnly($in) && !$this->statusOk($in) && !$this->statusErr($in)) {
            list($dirName, $type) = explode(' ', $in);
            $directoryContents[$dirName] = $type;
            $in = $this->sockRead();
            if(PEAR::isError($status)) return $status;
        }
        return ksort($directoryContents);
    }
    /**
     * rmDir 
     * 
     * @param mixed $domain 
     * @param string $user 
     * @param string $path 
     * @access public
     * @return mixed PEAR_Error on failure, true on success
     */
    public function rmDir($domain, $user = '', $path = '')
    {
        $basePath = $domain;
        if (!empty($user)) $basePath = "$user@$basePath";
        if (!empty($Path)) $basePath.= "/".$path;
        $basePath.= '/';
        $basePath = ereg_replace('//', '/', $basePath);
        $status = $this->sockWrite("rm_dir $basePath");
        if(PEAR::isError($status)) return $status;
        $Status = $this->sockRead();
        if (!$this->statusOk($status))
            return PEAR::raiseError("command rmdir failed - $status");
        return true;
    }


    /**
     * mkDir 
     * 
     * @param mixed $domain 
     * @param string $user 
     * @param string $path 
     * @access public
     * @return mixed PEAR_Error on failure, true on success
     */
    public function mkDir($domain, $user = '', $path = '')
    {
        $basePath = $this->formatBasePath($domain, $user, $path);
        $status = $this->sockWrite("mk_dir $basePath");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if (!$this->statusOk($Status))
            return PEAR::raiseError("command failed - $status");
        return true;
    }

    /**
     * getLimits 
     * 
     * @param mixed $domain 
     * @access public
     * @return mixed array limits on success, PEAR_Error on failure
     */
    public function getLimits($domain)
    {
        $status = $this->sockWrite("get_limits $domain");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if (!$this->statusOk($Status))
            return PEAR::raiseError("command failed - $status");
        $limits = $this->readInfo();
        return $limits;
    }

    /**
     * setLimits 
     * 
     * @param mixed $domain 
     * @param mixed $limits 
     * @access public
     * @return mixed true on success, PEAR_Error on failure
     */
    public function setLimits($domain, $limits)
    {
        $stringParms = array(   'max_popaccounts',
                                'max_aliases',
                                'max_forwards',
                                'max_autoresponders',
                                'max_mailinglists', 
                                'disk_quota',
                                'max_msgcount',
                                'default_quota',
                                'default_maxmsgcount');

        $flagParms = array(     'disable_pop',
                                'disable_imap',
                                'disable_dialup',
                                'disable_password_changing',
                                'disable_webmail',
                                'disable_external_relay',
                                'disable_smtp',
                                'disable_spamassassin',
                                'delete_spam',
                                'perm_account',
                                'perm_alias',
                                'perm_forward',
                                'perm_autoresponder',
                                'perm_maillist',
                                'perm_maillist_users',
                                'perm_maillist_moderators',
                                'perm_quota',
                                'perm_defaultquota');

        $status = $this->sockWrite("set_limits $domain");
        if(PEAR::isError($status)) return $status;
        // string parms
        while (list(, $name) = each($stringParms)) {
            if (!empty($limits[$name])) {
                $value = $limits[$name];
                $status = $this->sockWrite("$name $value");
                if(PEAR::isError($status)) return $status;
            }
        }
        // flag parms
        while (list(, $name) = each($flagParms)) {
            if (!empty($limits[$name])) {
                $value = $limits[$name];
                $status = $this->sockWrite("$name $value");
                if(PEAR::isError($status)) return $status;
            }
        }
        $status = $this->sockWrite(".");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if (!$this->statusOk($status))
            return PEAR::raiseError("command failed - $status");
        return true;
    }

    /**
     * delLimits 
     * 
     * @param mixed $domain 
     * @access public
     * @return mixed true on success, PEAR_Error on failure
     */
    public function delLimits($domain)
    {
        $status = $this->sockWrite("del_limits $domain");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if (!$this->statusOk($status))
            return PEAR::raiseError("command failed - $status");
        return true;
    }

    /**
     * domainInfo 
     * 
     * @param mixed $domain 
     * @access public
     * @return mixed dom_info array on success, PEAR_Error on failure
     */
    public function domainInfo($domain)
    {
        $out = $this->sockWrite("dom_info $domain");
        if(PEAR::isError($out))  return $out;
        $status = $this->sockRead();
        if(PEAR::isError($in))  return $in;
        if(!$this->statusOk($in)) return PEAR::raiseError("dom_info failed - " . $in);
        return $this->readInfo();
    }
    /**
     * listDomains 
     * 
     * @param int $page 
     * @param int $perPage 
     * @access public
     * @return mixed
     */
    public function listDomains($page = 0, $perPage = 0)
    {
        $return = $this->sockWrite("list_domains $page $perPage");
        if(PEAR::isError($return)) return $return;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if (!$this->statusOk($status)) return PEAR::raiseError("command failed - " . $status);
        $domains = array();
        $list = array();
        $in = $this->sockRead();
        if(PEAR::isError($in)) return $in;
        while (!$this->dotOnly($in) && !$this->statusOk($in) && !$this->statusErr($in)) {
            list($parent, $domain) = explode(' ', $in, 2);
            $domains[$domain] = $parent;
            $in = $this->sockRead();
            if(PEAR::isError($in)) return $in;
        }
        return $domains;
    }
    public function domainCount()
    {
        $status = $this->sockWrite("domain_count");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if (!$this->statusOk($status)) 
            return PEAR::raiseError("command failed - $status");
        $in = $this->sockRead();
        while (!$this->dotOnly($in) && !$this->statusOk($in) && !$this->statusErr($in)) {
            list(, $count) = explode(' ', $in, 2);
            $in = $this->sockRead();
        }
        return $count;
    }
    /**
     * addDomain 
     * 
     * @param mixed $domain 
     * @param mixed $password 
     * @access public
     * @return mixed true on success, PEAR_Error on failure
     */
    public function addDomain($domain, $password)
    {
        $status = $this->sockWrite("add_domain $domain $password");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if (!$this->statusOk($Status))
            return PEAR::raiseError("command failed - $status");
        return true;
    }

    /**
     * addAliasDomain 
     * 
     * @param mixed $domain 
     * @param mixed $alias 
     * @access public
     * @return mixed true on success, PEAR_Error on failure
     */
    public function addAliasDomain($domain, $alias)
    {
        $this->Error = '';
        $status = $this->sockWrite("add_alias_domain $domain $alias");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if (!$this->statusOk($Status))
            return PEAR::raiseError("command failed - $status");
        return true;
    }
    /**
     * delDomain 
     * 
     * @param mixed $domain 
     * @access public
     * @return mixed PEAR_Error on failure, true on success
     */
    public function delDomain($domain)
    {
        $status = $this->sockWrite("del_domain $domain");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if(!$this->statusOk($Status))
            return PEAR::raiseError("command failed - $status");
        return true;
    }

    /**
     * findDomain 
     * 
     * return page number that the domain occurs on
     * 
     * @param mixed $domain 
     * @param mixed $perPage 
     * @access public
     * @return mixed int page on success, PEAR_Error on failure
     */
    public function findDomain($domain, $perPage)
    {
        $status = $this->sockWrite("find_domain $domain $perPage");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if(!$this->statusOk($status))
            return PEAR::raiseError("command failed - $status");
        while(!$this->dotOnly($in) && !$this->statusOk($in) && !$this->statusErr($in)) {
            list(, $page) = explode(' ', $in, 2);
            $in = $this->sockRead();
            if(PEAR::isError($in)) return $in;
        }
        return $page;
    }

    /**
     * addUser 
     * 
     * @param mixed $domain 
     * @param mixed $user 
     * @param mixed $password 
     * @param mixed $gecos 
     * @access public
     * @return mixed true on success, PEAR_Error on failure
     */
    public function addUser($domain, $user, $password, $gecos)
    {
        $status = $this->sockWrite("add_user $user@$domain $password $gecos");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if (!$this->statusOk($status))
            return PEAR::raiseError("command failed - $status");
        return true;
    }

    /**
     * delUser 
     * 
     * @param mixed $domain 
     * @param mixed $user 
     * @access public
     * @return true on success, PEAR_Error on failure
     */
    public function delUser($domain, $user)
    {
        $status = $this->sockWrite("del_user $user@$domain");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if (!$this->statusOk($status))
            return PEAR::raiseError("command failed - $status");
        return true;
    }


    /**
     * modUser 
     * 
     * @param mixed $domain 
     * @param mixed $user 
     * @param mixed $userInfo 
     * @access public
     * @return mixed true on success, PEAR_Error on failure
     */
    public function modUser($domain, $user, $userInfo)
    {
        #  NOTE:  If you want your users to be able to change passwords
        #         from ModUser, you must un-comment the name below.
        $stringParms = array(   'quota',
                                'comment',
                                'clear_text_password');

        $flagParms = array(     'no_password_change',
                                'no_pop',
                                'no_webmail',
                                'no_imap',
                                'no_smtp',
                                'bounce_mail',
                                'no_relay',
                                'no_dialup',
                                'user_flag_0',
                                'user_flag_1',
                                'user_flag_2',
                                'user_flag_3',
                                'system_admin_privileges',
                                'system_expert_privileges',
                                'domain_admin_privileges',
                                'override_domain_limits',
                                'no_spamassassin',
                                'no_maildrop',
                                'delete_spam',);

        $status = $this->sockWrite("mod_user $user@$domain");
        if(PEAR::isError($status)) return $status;
        while (list(, $name) = each($stringParms)) {
            if (!empty($userInfo[$name])) {
                $value = $userInfo[$name];
                $status = $this->sockWrite("$name $value");
                if(PEAR::isError($status)) return $status;
            }
        }
        while (list(, $name) = each($flagParms)) {
            $flip = false;
            $value = $this->getGidBit($userInfo['gidflags'], $name, $flip);
            $value = ($value) ? '1' : '0';
            $status = $this->sockWrite("$name $value");
            if(PEAR::isError($status)) return $status;
        }
        $status = $this->sockWrite(".");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if (!$this->statusOk($Status))
            return PEAR::raiseError("command failed - $status");
        // Not sure what this is for - bshupp
        // if (!empty($Warnings)) {
        //     return "Warning:\n   ".implode("\n   ", $Warnings) ."\n\n";
        // }
        return true;
    }
    /**
     * userInfo 
     * 
     * @param mixed $domain 
     * @param mixed $user 
     * @access public
     * @return mixed user info array on success, PEAR_Error on failure
     */
    public function userInfo($domain, $user)
    {
        $status = $this->sockWrite("user_info $user@$domain");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if (!$this->statusOk($status)) return PEAR::raiseError("command failed - $status");
        return $this->readInfo();
    }

    public function listUsers($domain, $page = 0, $perPage = 0)
    {
        $status = $this->sockWrite("list_users $domain $page $perPage");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if (!$this->statusOk($status)) return PEAR::raiseError("command failed - $status");
        $i = 0;
        $currentName = '';
        $list = array();
        $this->recordio("<<--  Start collecting user data  -->>");
        $in = $this->sockRead();
        if(PEAR::isError($in)) return $in;
        while (!$this->dotOnly($in) && !$this->statusOk($in) && !$this->statusErr($in) && $i < 10) {
            list($name, $value) = explode(' ', $in, 2);
            if ('name' == $name) {
                if (!empty($currentName)) {
                    $list[$currentName] = $user;
                }
                $currentName = $value;
                $user = array();
            } else {
                $user[$name] = trim($value);
            }
            $in = $this->sockRead();
            if(PEAR::isError($in)) return $in;
        }
        if (!empty($currentName)) $list[$currentName] = $user;
        $this->recordio("<<--  Stop collecting user data  -->>");
        return $list;
    }
    /**
     * userCount 
     * 
     * @param mixed $domain 
     * @access public
     * @return int count on success, PEAR_Error on failure
     */
    public function userCount($domain)
    {
        $status = $this->sockWrite("user_count $domain");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if (!$this->statusOk($status)) 
            return PEAR::raiseError("command failed - $in");
        $in = $this->sockRead();
        while (!$this->dotOnly($in) && !$this->statusOk($in) && !$this->statusErr($in)) {
            list(, $count) = explode(' ', $in, 2);
            $in = $this->sockRead();
        }
        return $count;
    }

    /**
     * getLastAuthIP 
     * 
     * @param mixed $domain 
     * @param mixed $user 
     * @access public
     * @return return string IP on success, PEAR_Error on failure
     */
    public function getLastAuthIP($domain, $user)
    {
        $status = $this->sockWrite("get_lastauthip $user@$domain");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if (!$this->statusOk($status))
            return PEAR::raiseError("command failed - $status");
        $in = $this->sockRead();
        return $in;
    }

    /**
     * getLastAuth 
     * 
     * @param mixed $domain 
     * @param mixed $user 
     * @access public
     * @return string time on success, PEAR_Error on failure
     */
    public function getLastAuth($domain, $user)
    {
        $status = $this->sockWrite("get_lastauth $user@$domain");
        if(PEAR::isError($status)) return $status;
        $status = $this->sockRead();
        if(PEAR::isError($status)) return $status;
        if (!$this->statusOk($Status))
            return PEAR::raiseError("command failed - $status");
        $in = $this->sockRead();
        return $in;
    }

}
?>
