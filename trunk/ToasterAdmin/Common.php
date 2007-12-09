<?php

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
     * __construct 
     * 
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
        if (isset($_REQUEST['domain'])) {
            $this->domain = $_REQUEST['domain'];
        }
        $this->setData('domain', $this->domain);
        $this->setData('domain_url', htmlspecialchars('./?module=Domains&event=domainMenu&domain=' . $this->domain));
    }
    

    public function paginate($total) {
        $this->setData('total', $total);
        $this->setData('limit', (integer)Framework::$site->config->maxPerPage);
        $start = !empty($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
        $this->setData('start', $start);
        $this->setData('currentPage', ceil($this->data['start'] / $this->data['limit']) + 1);
        $this->setData('totalPages', ceil($this->data['total'] / $this->data['limit']));
    }

    /**
     * noDomainPrivs 
     * 
     * Simple wrapper for isDomainAdmin that can be called in constructors
     * 
     * @access protected
     * @return mixed PEAR_Error if they do NOT have domain admin privs, false if they do
     */
    protected function noDomainPrivs() {
        // Verify that they have access
        if (!$this->user->isDomainAdmin($this->domain)) {
            return PEAR::raiseError(_('Error: you do not have edit privileges on domain ') . $this->domain);
        }
        return false;
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
    protected function noDomainSupplied() {
        if (is_null($this->domain)) {
            throw new Framework_Exception (_('Error no domain supplied'));
        }
    }

    /**
     * sameDomain 
     * 
     * @param mixed $name 
     * @param mixed $value 
     * @static
     * @access public
     * @return void
     */
    static public function sameDomain ($name, $value) {
        $emailArray = explode('@', $value);
        if ($emailArray[1] == $this->domain) return true;
        return false;
    }

}
?>
