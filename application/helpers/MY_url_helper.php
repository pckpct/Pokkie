<?php
	function current_url() {
		$CI =& get_instance();
	 
		$get_params = '';
	 
		if (strpos($_SERVER['REQUEST_URI'], '?')) {
			$get_params = (substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '?')) != '') ? substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '?')) : '';
	}
 
	return $CI->config->site_url($CI->uri->uri_string()).$get_params;
}

?>