<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
 
class Main extends CI_Controller {
    function __construct() {
        parent::__construct();
		$this->data['title'] = "Authenticate";
        $this->load->helper('form');
        $this->load->library('Form_validation');
        $this->load->library('auth_ldap');
        $this->load->helper('url');
        $this->load->library('table');
		$this->template->add_css('css/master.css');
    }
	
	

	function index() {
		
		redirect('auth');

    }
	
	  
}

?>
