<?php
/**
 * Framework_Controller_ToasterAdmin 
 * 
 * PHP Version 5.1.0+
 * 
 * @uses       Framework_Controller_Web
 * @category   Mail
 * @package    ToasterAdmin
 * @subpackage Controller
 * @author     Bill Shupp <hostmaster@shupp.org> 
 * @copyright  2008 Bill Shupp
 * @license    GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link       http://trac.merchbox.com/toasteradmin/trac
 */
/**
 * Framework_Controller_ToasterAdmin 
 * 
 * Simple extension to overide the request parser
 * 
 * @uses       Framework_Controller_Web
 * @category   Mail
 * @package    ToasterAdmin
 * @subpackage Controller
 * @author     Bill Shupp <hostmaster@shupp.org> 
 * @copyright  2008 Bill Shupp
 * @license    GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link       http://trac.merchbox.com/toasteradmin/trac
 */
class Framework_Controller_ToasterAdmin extends Framework_Controller_Web
{
    /**
     * requester 
     * 
     * Override the request parser
     * 
     * @var string
     * @access public
     */
    public $requester = 'ToasterAdmin';
}
