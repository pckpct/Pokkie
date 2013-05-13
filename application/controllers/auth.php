<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends MY_Controller {

    function __construct() {
        parent::__construct();
		
		$this->data['module'] = 'auth';

        $this->load->helper('form');
		$this->load->helper('url');
		
        $this->load->library('Form_validation');
        $this->load->library('auth_ldap');
        $this->load->library('table');

    }

    function index() {
        $this->session->keep_flashdata('tried_to');		
		        
		$this->login_test();					//Used outside the active directory environment
		//$this->login_live();					//Used within the active directory environmnet
			
    }
	
	
	//Live version of the index function (only used within Active Directory environment)
	//Different roles are assigned according to the ID used to loggin to Windows.
	function login_live () {
	
		//get sso username and split to remove domain name
		if ( isset($_SERVER['AUTH_USER'])) {
			$temp = explode('\\', strtolower($_SERVER["AUTH_USER"]));
		
			if ($temp[1] == "") {
				$adname = $temp[0];
			} else {
				$adname = $temp[1];
			}		
		}
		
		$userid = $adname;	
	
		//now go get rights and set session correctly
		$this->auth_ldap->single_sign_on($userid);		
	
		if($this->session->userdata('logged_in')) {
			//SSO OK
			
			$role = $this->get_rights($userid);
			$this->session->set_userdata('role_level',$role);			
			redirect('/portal/');
			
		}else{
			//something went wrong redirect to local login.
			redirect('/auth/loginfailure');	
		}		

    }
		
	//Development version of the index function. (Only use outside the Active Directory Environment)
	//Different roles are assigned according to the login ID provided when loggin in	
    function login_development() {
	
		if ($this->input->post()) {
			$password = $this->input->post('password');	
			$role_level = $this->input->post('user_role');
			
			if ($password == 'MMAccess') {		
			
				if ($role_level == 1) {
						$username = '277268';	
				} else if ($role_level == 2) {
						$username = '277268';
				} else if ($role_level == 3) {
						$username = '277268';		
				} else if ($role_level == 4) {
						$username = '277268';						
				} else if ($role_level == 5) {
						$username = '277268';		
				}				
				
				$this->session->set_userdata('logged_in', TRUE);
				$this->session->set_userdata('username', $username);
				$this->session->set_userdata('role_level',$role_level);
				$this->session->set_userdata('scope_id',"3");
				redirect('portal');
								
			} else {
			
				redirect('/auth/loginfailure');	
		
			}
		} 
		
		$this->data['title'] = "Authenticate";
		$this->template->write_view('content', 'auth/login_form');
		$this->template->render();
		
    }
	
	//Development version of the index function. (Only use outside the Active Directory Environment)
	//Different roles are assigned according to the login ID provided when loggin in	
    function login_test() {
	
		if ($this->input->post()) {
			$password = $this->input->post('password');	
			$role_level = $this->input->post('user_role');
			
			if ($password == 'NOADHERE') {		
			
				if ($role_level == 1) {
						$username = '277268';	
				} else if ($role_level == 2) {
						$username = '277268';
				} else if ($role_level == 3) {
						$username = '277268';		
				} else if ($role_level == 4) {
						$username = '277268';						
				} else if ($role_level == 5) {
						$username = '277268';		
				} else if ($role_level == 6) {
						$username = '277268';
				} else if ($role_level == 7) {
						$username = '277268';						
				} else if ($role_level == 13) {
						$username = '277268';						
				} else if ($role_level == 14) {
						$username = '277268';		
				} else if ($role_level >= 10) {
						$username = '277268';		
				}				
				
				$this->session->set_userdata('logged_in', TRUE);
				$this->session->set_userdata('username', $username);
				$this->session->set_userdata('role_level',$role_level);
				$this->session->set_userdata('scope_id',"3");
				redirect('portal');
								
			} else {
			
				redirect('/auth/loginfailure');	
		
			}
		} 
		
		$this->data['title'] = "Authenticate";
		$this->template->write_view('content', 'auth/login_form');
		$this->template->render();
		
    }
	
	private function get_rights($userid)
	{		
			
		//Retrieve user data from the database
		$query = $this->db->query("SELECT * FROM system_globalrights where user = '".$userid."';");
		$data = $query->row();	
		
		//Check records found and assign roles
		if ($query->num_rows() > 0) {			
			$rights = $data->roleid;					
		} else {
			$rights = '1';
		}
		
		return $rights;
	}

    function logout() {
        if($this->session->userdata('logged_in') || $this->session->userdata('admin_logged_in')) {
            $data['name'] = $this->session->userdata('cn');
            $data['username'] = $this->session->userdata('username');
            $data['logged_in'] = TRUE;
            $this->auth_ldap->logout();
        } else {
            $data['logged_in'] = FALSE;
		}
		
		$this->data['title'] = "Authenticate";
		$this->template->write_view('content', 'auth/logout_view', $data);
		$this->template->render();
    }
	
	//The login failed
	function loginfailure() {
	
		$this->data['title'] = "Authenticate";
		$this->template->write_view('content', 'auth/loginfailure');
		$this->template->render();
	}
}

?>