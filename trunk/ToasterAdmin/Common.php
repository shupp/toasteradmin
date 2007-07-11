<?php

class ToasterAdmin_Common extends Framework_User_ToasterAdmin
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
    }
    

    public function paginate($total) {
        $this->setData('total', $total);
        $this->setData('limit', (integer)Framework::$site->config->maxPerPage);
        if (isset($_REQUEST['start']) && !ereg('[^0-9]', $_REQUEST['start'])) {
            if ($_REQUEST['start'] == 0) {
                $start = 1;
            } else {
                $start = $_REQUEST['start'];
            }
        }
        if (!isset($start)) $start = 1;
        $this->setData('start', $start);
        $this->setData('currentPage', ceil($this->data['start'] / $this->data['limit']));
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
     * @access protected false if it was supplied, PEAR_Error if it was not
     * @return mixed
     */
    protected function noDomainSupplied() {
        if (!isset($_REQUEST['domain'])) {
            return PEAR::raiseError(_('Error no domain supplied'));
        }
        return false;
    }

}
?>

