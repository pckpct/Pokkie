<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

	public function __construct ()	{
		parent::__construct();
			
		$this->template->add_css('css/master.css');
		$this->template->add_js('js/jquery.js');
		
		$this->data['logged_in'] = true;
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */