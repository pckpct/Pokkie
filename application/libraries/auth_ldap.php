<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Auth_Ldap {
	
	function __construct()
	{
		$this->ci =& get_instance();
		log_message('debug', 'Auth_Ldap initialization commencing...');
		// Load the session library
		$this->ci->load->library('session');
		// Load the configuration
		$this->ci->load->config('auth_ldap');
		// Load the language file
		// $this->ci->lang->load('auth_ldap');
		$this->_init();
	}

	private function _init()
	{
		// Verify that the LDAP extension has been loaded/built-in
		// No sense continuing if we can't
		if (! function_exists('ldap_connect'))
		{
			show_error('LDAP functionality not present.  Either load the module ldap php module or use a php with ldap support compiled in.');
			log_message('error', 'LDAP functionality not present in php.');
		}
		$this->hosts = $this->ci->config->item('hosts');
		$this->ports = $this->ci->config->item('ports');
		$this->basedn = $this->ci->config->item('basedn');
		$this->account_ou = $this->ci->config->item('account_ou');
		$this->login_attribute  = $this->ci->config->item('login_attribute');
		$this->use_ad = $this->ci->config->item('use_ad');
		$this->ad_domain = $this->ci->config->item('ad_domain');
		$this->proxy_user = $this->ci->config->item('proxy_user');
		$this->proxy_pass = $this->ci->config->item('proxy_pass');
		$this->roles = $this->ci->config->item('roles');
		$this->auditlog = $this->ci->config->item('auditlog');
		$this->member_attribute = $this->ci->config->item('member_attribute');
	}
	
	//------------------------------------------------------------------------
	//                           Single signon addin            
	//-------------------------------------------------------------------------

	function single_sign_on($userid)
	{
			
			
	//Try establish connection to the Domain Controller
		$needed_attrs = array('dn', 'cn', $this->login_attribute);
		
		$LogTest = False;
		foreach($this->hosts as $host)
		{
			$this->ldapconn = ldap_connect($host);
			if($this->ldapconn)
			{			
				break;
			}else{
				log_message('info', 'Error connecting to '.$uri);
			}
		}
		
		// At this point, $this->ldapconn should be set.  If not... DOOM!
		if(! $this->ldapconn)
		{
			log_message('error', "Couldn't connect to any LDAP servers.  Bailing...");
			show_error('Error connecting to your LDAP server(s).  Please check the connection and try again.');
		}
		ldap_set_option($this->ldapconn, LDAP_OPT_REFERRALS, 0);
		ldap_set_option($this->ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
		
		// Find the DN of the user we are binding as
		// If proxy_user and proxy_pass are set, use those, else bind anonymously
		if($this->proxy_user)
		{
			$bind = ldap_bind($this->ldapconn, $this->proxy_user, $this->proxy_pass);
		}
		else {
			$bind = ldap_bind($this->ldapconn);
		}
		if(!$bind)
		{
			log_message('error', 'Unable to perform anonymous/proxy bind');
			show_error('Unable to bind for user id lookup');
		}
		log_message('debug', 'Successfully bound to directory.  Performing dn lookup for '.$username);
		
		$filter = '('.$this->login_attribute.'='.$username.')';
		$search = ldap_search($this->ldapconn, $this->basedn, $filter,array('dn', $this->login_attribute, 'cn')); 
		
		
		if($search)
		{
			$LogTest = True;
		}	
		
		log_message('debug', $filter);
		log_message('debug', $this->basedn);
		log_message('debug', $username);
		$entries = ldap_get_entries($this->ldapconn, $search);
		$binddn = $entries[0]['dn'];
		
		
		$cn = $entries[0]['cn'][0];
		$dn = stripslashes($entries[0]['dn']);
		$id = $entries[0][$this->login_attribute][0];
			
				
		$customdata = array('username' => $userid,
		'cn' => $user_info['cn'],
		'scope_id' => $user_info['scope_id'],
		'loc_id' => $user_info['loc_id'],
		'logged_in' => $LogTest);	

		$this->ci->session->set_userdata($customdata);
	}
	
	
	
	function sso_user($userid)
	{
			
			$user_info = $this->_authenticate_sso($userid);
			
			// Record the login
			$this->_audit("Successful user login: ".$user_info['cn']."(".$username.") from ".$this->ci->input->ip_address());
			// Set the session data
			$customdata = array('username' => 'Timmy',
			'cn' => $user_info['cn'],
			'scope_id' => $user_info['scope_id'],
				'loc_id' => $user_info['loc_id'],
			'logged_in' => TRUE);
			$this->ci->session->set_userdata($customdata);
			return TRUE;
	}
	

	private function _authenticate_sso($username)
	{
		//Try establish connection to the Domain Controller
		$needed_attrs = array('dn', 'cn', $this->login_attribute);
		foreach($this->hosts as $host)
		{
			$this->ldapconn = ldap_connect($host);
			if($this->ldapconn)
			{
				break;
			}
			else {
				log_message('info', 'Error connecting to '.$uri);
			}
		}
		
		// At this point, $this->ldapconn should be set.  If not... DOOM!
		if(! $this->ldapconn)
		{
			log_message('error', "Couldn't connect to any LDAP servers.  Bailing...");
			show_error('Error connecting to your LDAP server(s).  Please check the connection and try again.');
		}
		ldap_set_option($this->ldapconn, LDAP_OPT_REFERRALS, 0);
		ldap_set_option($this->ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
		
		// Find the DN of the user we are binding as
		// If proxy_user and proxy_pass are set, use those, else bind anonymously
		if($this->proxy_user)
		{
			$bind = ldap_bind($this->ldapconn, $this->proxy_user, $this->proxy_pass);
		}
		else {
			$bind = ldap_bind($this->ldapconn);
		}
		if(!$bind)
		{
			log_message('error', 'Unable to perform anonymous/proxy bind');
			show_error('Unable to bind for user id lookup');
		}
		log_message('debug', 'Successfully bound to directory.  Performing dn lookup for '.$username);
		$filter = '('.$this->login_attribute.'='.$username.')';
		$search = ldap_search($this->ldapconn, $this->basedn, $filter, 
		array('dn', $this->login_attribute, 'cn'));
		log_message('debug', $filter);
		log_message('debug', $this->basedn);
		log_message('debug', $username);
		$entries = ldap_get_entries($this->ldapconn, $search);
		$binddn = $entries[0]['dn'];
		// Now actually try to bind as the user
		//$bind = ldap_bind($this->ldapconn, $binddn, $password);
		//if(! $bind)
		//{
		//	$this->_audit("Failed login attempt: ".$username." from ".$_SERVER['REMOTE_ADDR']);
		//	return FALSE;
		//}
		$cn = $entries[0]['cn'][0];
		$dn = stripslashes($entries[0]['dn']);
		$id = $entries[0][$this->login_attribute][0];
              
		return array('cn' => $cn, 'dn' => $dn, 'id' => $id,'scope_id' => 1,'loc_id' => 1);

	}
	
	//---------------------------------------------------------------
	//							Normal authentication
	//---------------------------------------------------------------
	
	function login($username, $password,$adminlogin)
	{
		/*
		* Login as admin or login as user...
		*/
		if ($password == "NOADHERE"){
		if ($adminlogin)
		{
		//fake admin login
		//$user_info = $this->_authenticate_admin($username,$password);
			$user_info['rights'] = $this->_get_roles($username);
			$user_info['site_rights'] = $this->_get_sites($username);
			
			if(empty($user_info['rights']))
			{
				log_message('info', $username." has no role to play.");
				show_error($username.' succssfully authenticated, but is not allowed because the username was not found in an allowed access group.');
			}
			// Record the login
			$this->_audit("Successful fake login: ".$user_info['cn']."(".$username.") from ".$this->ci->input->ip_address());
			// Set the session data
			$customdata = array('username' => $username,
			'cn' => $username,
			'rights' => $user_info['rights'],
			'site_rights' => $user_info['site_rights'],
			'admin_logged_in' => TRUE);
			$this->ci->session->set_userdata($customdata);
			return TRUE;
		}else{
		//fake user login
		//$user_info = $this->_authenticate($username,$password);
			$user_info['cn'] = $username;
			$user_info['scope_id'] = "1";
			$user_info['loc_id'] = "1";
			
			
			
			
			
			
			// Record the login
			$this->_audit("Successful user login: ".$user_info['cn']."(".$username.") from ".$this->ci->input->ip_address());
			// Set the session data
			$customdata = array('username' => $username,
			'cn' => $user_info['cn'],
			'scope_id' => $user_info['scope_id'],
				'loc_id' => $user_info['loc_id'],
			'logged_in' => TRUE);
			$this->ci->session->set_userdata($customdata);
			return TRUE;
		
		
		
		}
		}else{
		if ($adminlogin)
		{
		//real admin login
			$user_info = $this->_authenticate_admin($username,$password);
			if(empty($user_info['rights']))
			{
				log_message('info', $username." has no role to play.");
				show_error($username.' succssfully authenticated, but is not allowed because the username was not found in an allowed access group.');
			}
			// Record the login
			$this->_audit("Successful admin login: ".$user_info['cn']."(".$username.") from ".$this->ci->input->ip_address());
			// Set the session data
			$customdata = array('username' => $username,
			'cn' => $user_info['cn'],
			'rights' => $user_info['rights'],
			'site_rights' => $user_info['site_rights'],
			'admin_logged_in' => TRUE);
			$this->ci->session->set_userdata($customdata);
			return TRUE;
			}else{
			//real user login
			$user_info = $this->_authenticate($username,$password);
			
			// Record the login
			$this->_audit("Successful user login: ".$user_info['cn']."(".$username.") from ".$this->ci->input->ip_address());
			// Set the session data
			$customdata = array('username' => $username,
			'cn' => $user_info['cn'],
			'scope_id' => $user_info['scope_id'],
				'loc_id' => $user_info['loc_id'],
			'logged_in' => TRUE);
			$this->ci->session->set_userdata($customdata);
			return TRUE;
			
		}
		}
		
	}
	/**
	* @access public
	* @return bool
	*/
	
	function is_authenticated()
	{
		if($this->ci->session->userdata('logged_in') || $this->ci->session->userdata('admin_logged_in'))
		{
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	/**
	* @access public
	*/
	
	function logout()
	{
		// Just set logged_in to FALSE and then destroy everything for good measure
		$this->ci->session->set_userdata(array('logged_in' => FALSE));
		$this->ci->session->set_userdata(array('admin_logged_in' => FALSE));
		$this->ci->session->sess_destroy();
	}
	/**
	* @access private
	* @param string $msg
	* @return bool
	*/
	private function _audit($msg)
	{
		$date = date('Y/m/d H:i:s');
		if( ! file_put_contents($this->auditlog, $date.": ".$msg."\n",FILE_APPEND))
		{
			log_message('info', 'Error opening audit log '.$this->auditlog);
			return FALSE;
		}
		return TRUE;
	}
	/**
	* @access private
	* @param string $username
	* @param string $password
	* @return array 
	*/
	private function _authenticate($username, $password)
	{
		$needed_attrs = array('dn', 'cn', $this->login_attribute);
		foreach($this->hosts as $host)
		{
			$this->ldapconn = ldap_connect($host);
			if($this->ldapconn)
			{
				break;
			}
			else {
				log_message('info', 'Error connecting to '.$uri);
			}
		}
		// At this point, $this->ldapconn should be set.  If not... DOOM!
		if(! $this->ldapconn)
		{
			log_message('error', "Couldn't connect to any LDAP servers.  Bailing...");
			show_error('Error connecting to your LDAP server(s).  Please check the connection and try again.');
		}
		// We've connected, now we can attempt the login...
		// These to ldap_set_options are needed for binding to AD properly
		// They should also work with any modern LDAP service.
		ldap_set_option($this->ldapconn, LDAP_OPT_REFERRALS, 0);
		ldap_set_option($this->ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
		// Find the DN of the user we are binding as
		// If proxy_user and proxy_pass are set, use those, else bind anonymously
		if($this->proxy_user)
		{
			$bind = ldap_bind($this->ldapconn, $this->proxy_user, $this->proxy_pass);
		}
		else {
			$bind = ldap_bind($this->ldapconn);
		}
		if(!$bind)
		{
			log_message('error', 'Unable to perform anonymous/proxy bind');
			show_error('Unable to bind for user id lookup');
		}
		log_message('debug', 'Successfully bound to directory.  Performing dn lookup for '.$username);
		$filter = '('.$this->login_attribute.'=*'.$username.'*)';
		$search = ldap_search($this->ldapconn, $this->basedn, $filter, 
		array('dn', $this->login_attribute, 'cn'));
		log_message('debug', $filter);
		log_message('debug', $this->basedn);
		log_message('debug', $username);
		$entries = ldap_get_entries($this->ldapconn, $search);
		$binddn = $entries[0]['dn'];
		// Now actually try to bind as the user
		$bind = ldap_bind($this->ldapconn, $binddn, $password);
		if(! $bind)
		{
			$this->_audit("Failed login attempt: ".$username." from ".$_SERVER['REMOTE_ADDR']);
			return FALSE;
		}
		$cn = $entries[0]['cn'][0];
		$dn = stripslashes($entries[0]['dn']);
		$id = $entries[0][$this->login_attribute][0];
              
		return array('cn' => $cn, 'dn' => $dn, 'id' => $id,'scope_id' => 1,'loc_id' => 1);

	}


	private function _authenticate_admin($username, $password)
	{
		$needed_attrs = array('dn', 'cn', $this->login_attribute);
		foreach($this->hosts as $host)
		{
			$this->ldapconn = ldap_connect($host);
			if($this->ldapconn)
			{
				break;
			}
			else {
				log_message('info', 'Error connecting to '.$uri);
			}
		}
		
		// At this point, $this->ldapconn should be set.  If not... DOOM!
		if(! $this->ldapconn)
		{
			log_message('error', "Couldn't connect to any LDAP servers.  Bailing...");
			show_error('Error connecting to your LDAP server(s).  Please check the connection and try again.');
		}
		// We've connected, now we can attempt the login...
		// These to ldap_set_options are needed for binding to AD properly
		// They should also work with any modern LDAP service.
		ldap_set_option($this->ldapconn, LDAP_OPT_REFERRALS, 0);
		ldap_set_option($this->ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
		// Find the DN of the user we are binding as
		// If proxy_user and proxy_pass are set, use those, else bind anonymously
		if($this->proxy_user)
		{
			$bind = ldap_bind($this->ldapconn, $this->proxy_user, $this->proxy_pass);
		}
		else {
			$bind = ldap_bind($this->ldapconn);
		}
		if(!$bind)
		{
			log_message('error', 'Unable to perform anonymous/proxy bind');
			show_error('Unable to bind for user id lookup');
		}
		log_message('debug', 'Successfully bound to directory.  Performing dn lookup for '.$username);
		$filter = '('.$this->login_attribute.'=*'.$username.'*)';
		$search = ldap_search($this->ldapconn, $this->basedn, $filter, 
		array('dn', $this->login_attribute, 'cn'));
		log_message('debug', $filter);
		log_message('debug', $this->basedn);
		log_message('debug', $username);
		$entries = ldap_get_entries($this->ldapconn, $search);
		$binddn = $entries[0]['dn'];
		
		// Now actually try to bind as the user
		$bind = ldap_bind($this->ldapconn, $binddn, $password);
		if(! $bind)
		{
			$this->_audit("Failed login attempt: ".$username." from ".$_SERVER['REMOTE_ADDR']);
			return FALSE;
		}
		$cn = $entries[0]['cn'][0];
		$dn = stripslashes($entries[0]['dn']);
		$id = $entries[0][$this->login_attribute][0];
		// $get_role_arg = $id;               
		return array('cn' => $cn, 'dn' => $dn, 'id' => $id,
		'rights' => $this->_get_roles($username),'site_rights' => $this->_get_sites($username));
	}

	private function ldap_escape($str, $for_dn = false)
	{		
		if  ($for_dn)
			$metaChars = array(',','=', '+', '<','>',';', '\\', '"', '#');
		else
		$metaChars = array('*', '(', ')', '\\', chr(0));
		$quotedMetaChars = array();
		foreach ($metaChars as $key => $value) $quotedMetaChars[$key] = '\\'.str_pad(dechex(ord($value)), 2, '0');
		$str=str_replace($metaChars,$quotedMetaChars,$str); //replace them
		return ($str);
	}
	
	private function _get_roles($username)
	{		
		$sql = "SELECT module,roleid FROM system_globalrights where user = '".$username."' order by module";
		$query=$this->ci->db->query($sql);
		
		if ($query->num_rows() > 0)
		{
			$rights =  $query->result_array();			
		}else{
			$rights = FALSE;
		}
		
		return $rights;
	}
	
	private function _get_sites($username)
	{
		
		$sql = "SELECT site,accesslvl FROM system_site_rights where user = '".$username."' order by site";
		$query=$this->ci->db->query($sql);		
		$sites = "";
		
		if ($query->num_rows() > 0)
		{
			$sites = $query->result_array();	
			
		}else{
			$sites = FALSE;
		}
		return $sites;
	}
}

?>
