<?php
/**
 * ToasterAdmin_Common 
 * 
 * PHP Version 5.1.0+
 * 
 * @uses Framework_Auth_User
 * @abstract
 * @category  Mail
 * @package   ToasterAdmin
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 * @todo      Check arguments to sameDomain() - first argument is not used
 */

/**
 * ToasterAdmin_Common 
 * 
 * Common helper functions used by modules.
 * 
 * @uses Framework_Auth_User
 * @abstract
 * @category  Mail
 * @package   ToasterAdmin
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */
abstract class ToasterAdmin_Common extends Framework_Auth_User
{

    /**
     * domain 
     * 
     * $domain is set from $_REQUEST['domain'];
     * 
     * @var mixed
     * @access public
     */
    public $domain = null;

    /**
     * controllers 
     * 
     * We're using a custom controller here to add gettext support
     * 
     * @var string
     * @access public
     */
    public $controllers = array('ToasterAdmin');

    /**
     * __construct 
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        if (isset($_REQUEST['domain'])) {
            $this->domain = $_REQUEST['domain'];
        }
        $this->setData('domain', $this->domain);
        $durl = './?module=Domains&class=Menu&domain=' . $this->domain;
        $this->setData('domain_url', htmlspecialchars($durl));
    }
    

    /**
     * paginate 
     * 
     * Set pagination data
     * 
     * @param mixed $total total number of items
     * 
     * @access public
     * @return void
     */
    public function paginate($total)
    {
        $this->setData('total', $total);
        $this->setData('limit', (integer)Framework::$site->config->maxPerPage);
        $start = !empty($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
        $this->setData('start', $start);
        $this->setData('currentPage',
            ceil($this->data['start'] / $this->data['limit']) + 1);
        $this->setData('totalPages',
            ceil($this->data['total'] / $this->data['limit']));
    }

    /**
     * noDomainPrivs 
     * 
     * Simple wrapper for isDomainAdmin that can be called in constructors
     * 
     * @access protected
     * @throws Framework_Exception on failure
     * @return void
     */
    protected function noDomainPrivs()
    {
        // Verify that they have access
        if (!$this->user->isDomainAdmin($this->domain)) {
            $message = _('Error: you do not have edit privileges on domain ');
            throw new Framework_Exception($message . $this->domain);
        }
    }

    /**
     * noDomainSupplied 
     * 
     * Was $_REQUEST['domain'] supplied?
     * Required by several modules
     *
     * @return void
     * @throws Framework_Exception on failure
     */
    protected function noDomainSupplied()
    {
        if (is_null($this->domain)) {
            throw new Framework_Exception (_('Error no domain supplied'));
        }
    }

    /**
     * sameDomain 
     * 
     * Compare email domin to $this->value
     * 
     * @param mixed $name  not sure
     * @param mixed $value email address
     * 
     * @static
     * @access public
     * @return void
     */
    static public function sameDomain($name, $value)
    {
        $emailArray = explode('@', $value);
        if ($emailArray[1] == $this->domain) return true;
        return false;
    }
}
?>
