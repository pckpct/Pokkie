<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
temp file to test session data
*/

class Portal extends CI_Controller {
	function __construct() {
		parent::__construct();
		
		$this->template->add_css('css/portal.css');
		$this->template->add_css('css/master.css');
		$this->template->add_js('js/jquery.js');
		$this->template->add_js('js/userValidate.js');
		$this->template->add_js('js/master.js');
		$this->template->add_js('js/settings.js');
		$this->template->add_js('js/comments.js');
		
		if ($this->session->userdata('role_level') >= 1) {
			$this->data['module'] = 'portal';
			$this->data['role_level'] = $this->session->userdata('role_level');
		} else {
			redirect(site_url('auth'));		
		}
		 
	}
	
	function index () {
		if ($this->session->userdata('logged_in')){
			//	retrieve data from the session
			$userupn = $this->session->userdata('username');
	
			//	retrieve user name from database
			$query = $this->db->query('
				SELECT * 
				FROM disc_userpool 
				WHERE AD_Account = "'. $userupn .'"
			');			
			$data = $query->row();	
			
			if ($query->num_rows() > 0) {
				$username= $data->ad_DispName;
			} else {
				$username= $userupn;
			}

			//	set data to the view
			$this->data['username'] = $username;
			$this->data['user_role'] = $this->session->userdata('role_level');
			
			//	write to view with title
			$this->data['title'] = "Migration Portal | Select Option";
			$this->template->write_view('content', 'users/landing_page', $this->data);
			$this->template->render();
		} else {
			redirect('/landscape/');
		}
	}
	
	function migration () {
		if (!$this->session->userdata('logged_in')){
			redirect('/auth/');
		}
			$userupn = $this->session->userdata('username');
			$this->data['username'] = $userupn;
			
			//	write to view with title
			$this->data['title'] = "Migration Portal | Start Migration";
			$this->template->write_view('content', 'users/migration', $this->data);
			$this->template->render();
	}
	// ============================= List apps - step 2 ============================= //
	function planning () {
		//	verfiy login
		if(!$this->session->userdata('logged_in')){
			redirect('/auth/');
		}
		
		
		// Load models and helpers
		$this->load->model('mRolloutSims', 'model_sims');		
		$this->load->model('mRolloutComments', 'model_comment');
		$this->load->library('form_validation');		
				
		// get information from input Post - Get
		$post = $this->input->post();
		$get = $this->input->get();
		if ($post) {  	//Submit by submit button
			$action = $this->input->post('submit');
			
		} else {		
			$mode = $this->input->get('mode');
			$uID = $this->input->get('uID');
			
		} 
		
		// set session data for migration
		$this->session->set_userdata('MP_source', 'user');
		$this->session->set_userdata('MP_user',$this->session->userdata('username'));
		
		$userID = $this->session->userdata('username');
		
		// process post actions
		if ($post) {  	
			switch ($action) {				
				case "Confirm":	// Data is corrected by the end user
					$post['loggin_user'] = $userID;
					$this->model_sims->confirmByUser($post['ro_ID']);	// save the confirmation					
					$this->model_comment->InsertConfirmation($post);	// add warning in the table
					//$this->model_sims->saveByUser($post);
					break;
				case "Submit":	//	Feedback is submitted by the end user
					// Insert comment
					$data['rolloutid'] = $post['selected_ro_ID'];
					$data['topic']  = $post['description'];
					$data['topic']  = $post['description'];
					$data['user_name']	= $userID;						
					$data['category'] = "issue";
					$data['status'] = 'open';
					
					$this->model_comment->InsertComment($data);	// add warning in the table
					break;
			}			
		}
		
		//select information from database
		$query = $this->db->query('
			SELECT * 
			FROM rollout_sims
			WHERE rollout_sims.Subscriber_aurora = "'.$userID.'"
		');	
			
		$systems = null;
		$validated = "yes";
		
		foreach ($query->result() as $row) {
			$data['ro_ID'] = $row->ro_ID;
			$data['Subscriber_Email'] = $row->Subscriber_Email;
			$data['Subscriber_Aurora'] = $row->Subscriber_Aurora;
			$data['Phone_nr'] = $row->Phone_nr;
			$data['sim_type'] = $row->sim_type;
			$data['sim_card'] = $row->sim_card;
			$data['Device_type'] = $row->Device_type;
			$data['TCP_email'] = $row->TCP_email;
			$data['TCP_aurora'] = $row->TCP_aurora;
			$data['admin_validation'] = $row->admin_validation;
			if ($data['admin_validation'] == "no") {
				$validated = "no";	
			}
			
			$systems[] = $data;
		}
		

		$this->data['systems'] = $systems;
		$this->data['validated'] = $validated;
		
		
		//	write to view with title
		$this->data['title'] = "Migration Portal | Select System";
		$this->template->add_js('js/jquery.js');
		$this->template->add_js('js/comments.js');
		$this->template->write_view('content', 'users/system_select', $this->data);
		$this->template->render();
	}
	
	function system () {
		if (!$this->session->userdata('logged_in')){
			redirect('/auth/');
		} 
		
		// get information from input
		$rolloutid = $this->input->get('roID');
		$migrationUser = $this->session->userdata('MP_user');
		$mode = $this->session->userdata('MP_source');
				
		// ======================== Personal hardware list =================================================
		//$this->session->set_userdata('MP_source', 'admin');
		$query2 = $this->db->query('
			SELECT *
			FROM rollout_systems
			WHERE ro_id = "'. $rolloutid .'"			
		');
		
		if ($query2->num_rows() > 0) {
			$row = $query2->row();
			$data['sys_name'] = $row->sys_name;
			$userID = $row->EAM_User;
			$scopeID = $row->scope_ID;
			$data['new_system'] = $row->new_system;
			$data['eam_model'] = $row->EAM_Model;
			$data['new_model'] = $row->new_model;
			
			$hardware = $data;
		}
		
		// user check (validated user with logged in user or admin.
		if ($migrationUser != $userID) {
			redirect('/auth/');
		}
		
		// ======================== standard software list =================================================
		$this->data['hardware'] = $hardware;
		$query = $this->db->query('
			SELECT * FROM disc_win7_software where win7_layer = "COE"
		');
		
		if ($query->num_rows() > 0) {
			$i = 0;
			foreach ($query->result() as $row) {
				$i = $i + 1;
				$data['i'] = $i;
				$data['app_ist'] = $row->win7_displayname;				
				$standardSoftware[] = $data;
			}
		
			$this->data['standardSoftware_list'] = $standardSoftware;
		}
		// ======================== Personal software list used ============================================
		$query = $this->db->query('
			SELECT xp_displayname,app_display_msg, win7_status 
			FROM disc_software_allocations inner join disc_xp_software on app_xp_officialname = xp_officialname
			inner join disc_win7_software on app_local_win7 = disc_win7_software.win7_id			
			WHERE Aurora_ID = "'. $migrationUser .'"
			and xp_showuser = "yes"
			and app_allocation_delete = "no"
			and app_local_status = "Used"
			order by xp_displayname
		');
		
		if ($query->num_rows() > 0) {
			$i = 0;
			foreach ($query->result() as $row) {
				$i = $i + 1;
				$data['i'] = $i;
				$data['app_ist'] = $row->xp_displayname;
				$data['app_sol'] = $row->app_display_msg;
				$data['app_status'] = $row->win7_status;
				$software[] = $data;
			}
		
			$this->data['software_list'] = $software;
		}
		
				// ======================== Personal software list not used ===============================
		$query2 = $this->db->query('
			SELECT xp_displayname,app_display_msg 
			FROM disc_software_allocations inner join disc_xp_software on app_xp_officialname = xp_officialname						
			WHERE Aurora_ID = "'. $migrationUser .'"
			and xp_showuser = "yes"		
			and app_local_status != "Used"
			order by app_display_msg
		');
		
		if ($query2->num_rows() > 0) {
			$i = 0;
			foreach ($query2->result() as $row) {
				$i = $i + 1;
				$data['i'] = $i;
				$data['app_ist'] = $row->xp_displayname;
				$data['app_sol'] = $row->app_display_msg;
				$data['app_status'] = '';
				$software2[] = $data;
			}
		
			$this->data['software2_list'] = $software2;
		}
		
		
		// set data for the view/session		
		$this->session->set_userdata('mig_id', $rolloutid);
				
		$this->session->set_userdata('scopeId', $scopeID);
		
		
		//	write to view with title
		$this->data['title'] = "Migration Portal | Verify Applications";
		$this->template->write_view('content', 'users/app_list', $this->data);			
		$this->template->render();
	}
	
	function confirmation () {
		if($this->session->userdata('logged_in')){
			$choice = $this->input->post('choice');
			if ($choice) {
				redirect('/portal/calendar');
			} else {
				redirect('/portal/issue');
			}
		} else {
			redirect('/auth/');
		}
	}
	
	function issue () {
		if($this->session->userdata('logged_in')){
			
			//Load helpers and models
			$this->load->helper('form', 'url');
			$this->load->library('form_validation');
			$this->load->model('mRolloutComments', 'RolloutComment');
			
			//get information input/session
			$user_username = $this->session->userdata('MP_user');			
			$rolloutid = $this->session->userdata('mig_id');		
			$insertData = $this->input->post();
			
			//form validation
			$this->form_validation->set_error_delimiters('<div class="error">', '</div>');

			# Set the validation on the fields
			$this->form_validation->set_rules('topic', 'Issue description', 'required|max_length[255]');
		
			# Set the validation messages for each field
			$this->form_validation->set_message('required', $this->config->item('validation_msg_required'));
			$this->form_validation->set_message('max_length', $this->config->item('validation_msg_maxlength'));		
		
		
			if ($this->form_validation->run() != FALSE) {
				
				$insertData['user_name'] = $this->session->userdata('username');			
				$insertData['rolloutid'] = $rolloutid;	
				$insertData['category'] = 'issue';
				$insertData['status'] = 'open';		
			
				$this->RolloutComment->InsertComment($insertData);	
				
				$query2 = $this->db->query('
					update rollout_systems 
					set mig_status = "issue",
					plan_date = date(now())
					WHERE ro_ID = '. $rolloutid .'
				');
				
				redirect('/portal');
				
			} 			
			
			//	retrieve information from database
			$where = 'WHERE rollout_systems.ro_ID = "'.$rolloutid.'"';
			$query = $this->db->query('
				SELECT * 
				FROM rollout_systems inner join disc_userpool on eam_user = ad_account 
				'.$where.' 
			');
			$rollout = $query->row_array();
			
			//set information to the view			
			$data['username'] = $rollout["ad_DispName"];
			$data['source'] = $this->session->userdata('MP_source');			
			
			//	write to view with title
			$data['title'] = 'Migration Portal | Issue';
			$this->template->write_view('content', 'users/issue', $data);
			$this->template->render();
		} else {
			redirect('/auth/');
		}	
	}	
						   
	function calendar () {
	
		// get information input/session
		
		$mode = $this->session->userdata('MP_source');
		$rolloutid = $this->session->userdata('mig_id');
		
		$query5 = $this->db->query('
			SELECT count(phone_nr) as simCount, scope_ID 
			FROM rollout_sims 
			WHERE subscriber_aurora = '. $this->session->userdata('username')  .'
		');
		if ($query5->num_rows() > 0) {
			$row = $query5->row();
			$scopeID = $row->scope_ID;			
			$simCount = $row->simCount;
		} else {
			echo 'nope';
		}
	
		
		if($this->session->userdata('logged_in')) {			
			$calendar = '';
			$thismonth = false;

			if ($this->input->get('month')) {
				$iMonth = $this->input->get('month');
			} else {
				$iMonth = date('n');
			}
			if ($this->input->get('year')) {
				$iYear = $this->input->get('year');
			} else {
				$iYear = date('Y');
			}	
		
			$iMonth2 = date('m',strtotime(date("Y")."-".$iMonth."-01"));
			if (!$iMonth || !$iYear) {
				$iMonth = date('n');
				$iYear = date('Y');
			}
			
			$aCalendar = $this->buildCalendar($iMonth, $iYear);
			list($iPrevMonth, $iPrevYear) = $this->prevMonth($iMonth, $iYear);
			list($iNextMonth, $iNextYear) = $this->nextMonth($iMonth, $iYear);
			$iCurrentMonth = date('n');
			$iCurrentYear = date('Y');
			$iCurrentDay = '';
			if (($iMonth == $iCurrentMonth) && ($iYear == $iCurrentYear)) {
				$iCurrentDay = date('d');
				$thismonth = true;
			}
			$iNextMonth = mktime(0, 0, 0, $iNextMonth, 1, $iNextYear);
			$iPrevMonth = mktime(0, 0, 0, $iPrevMonth, 1, $iPrevYear);
			$iCurrentDay = $iCurrentDay;
			$iCurrentMonth = mktime(0, 0, 0, $iMonth, 1, $iYear);
			$seconds = 0;
			
			$prev_month_link = "<a href=\"?month=". date('m',$iPrevMonth) ."&year=". date('Y',$iPrevMonth) ."\" class=\"previous_month\">". date('M',$iPrevMonth) ."</a>";
			$next_month_link = "<a href=\"?month=". date('m',$iNextMonth) ."&year=". date('Y',$iNextMonth) ."\" class=\"next_month\">". date('M',$iNextMonth) ."</a>";
			$title = date('F Y', $iCurrentMonth);
			
			foreach ($aCalendar as $aWeek) {			
				$calendar .= "<tr>";
				foreach ($aWeek as $iDay => $mDay) {
					if ($iDay == '') {
						$calendar .= "<td colspan=\"". $mDay ."\"  class=\"cal_reg_off\">&nbsp;</td>";
					} else { 
						if (strlen($iDay) == 1) {
							$iDay = '0'. $iDay; 
						}
						$datetocheck = $iYear ."-". $iMonth2 ."-". $iDay;
						
						
								$sql = '
									SELECT id 
									FROM rollout_sessions 
									WHERE session_date = "'. $datetocheck .'" 
									AND scope_id = "'. $scopeID .'" 
									ORDER BY session_date ASC 
								';
							
								$result = $this->db->query($sql);
								
								if ($result->num_rows() > 0) {
									$event_num = $result->num_rows();
									$event_available = false;
									$event_count = 0;
									$spaces_count = 0;
									$spaces_left = 0;
									$text = '';
										
									foreach ($result->result() as $row) {
										$q = '
											SELECT spots 
											FROM rollout_sessions 
											WHERE id="'. $row->id .'"
										';
										$spaces_result = $this->db->query($q);
										
										$rr = $spaces_result->row();
										
										$space = $rr->spots;
									
										$q = '
											SELECT 
											COUNT(distinct(Subscriber_Aurora)) as num 											
											FROM rollout_sims 
											WHERE session_ID = "'.$row->id.'"
										';
										
										$rs = $this->db->query($q);
										$rr2 = $rs->row();
										$spaces_left = $space - $rr2->num;
										
										if ($spaces_left > 0) { 
											$event_available = true; 
											$event_count++;
											$spaces_count = $spaces_count + $spaces_left;
										}
									} 
									
									if ($datetocheck <= date("Y-m-d")) {
										$event_available = false; 
										$text = 'Session in the past';
									} else {	
										$text = $spaces_count .' spots available';
									}
									
									if ($event_available) {
										$bgClass = 'cal_reg_on';  
									} else {
										$bgClass = 'cal_reg_off';
									}
									
									if (strtotime($datetocheck) == time()) {
										$bgClass = 'cal_reg_off';
									}
									
									if ($iDay < $iCurrentDay) {
										$bgClass = 'cal_reg_off';
									}
						
									$calendar .= '<td id=\''. $iDay .'\'';
									if ($iCurrentDay != $iDay) { 
										$var = '';
									} else { 
										$var = '_today'; 
									} 
									
									if ($iCurrentDay != $iDay && $bgClass != 'cal_reg_off') {
										$calendar .= "onmouseover=\"getElementById('". $iDay ."').className='mainmenu5';\" onmouseout=\"getElementById('". $iDay ."').className='". $bgClass ."';\" "; 
									} else if($iCurrentDay == $iDay && $bgClass != "cal_reg_off" ) {
										$calendar .= "onmouseover=\"getElementById('". $iDay ."').className='mainmenu5';\" onmouseout=\"getElementById('". $iDay ."').className='". $bgClass.$var ."';\" "; 
									}
									
									$calendar .= "class=\"". $bgClass.$var ."\">". $iDay;

									if ($bgClass == 'cal_reg_off') {
										$calendar .= "<span class='hide-me-for-nojs'><br/>0 spots available</span><noscript><br/>0 spots available</noscript>";
									} else { 
										$calendar .= "<br/><a href='session?date=". $datetocheck ."'>". $text ."</a>";
									}
									
									$calendar .= "</td>";
								} else {
									$ww = date("w",strtotime($datetocheck));
									$tt = $this->getMaxSecondsForThisDay($ww);
									if ($seconds < $tt) { 
										$bgClass = 'cal_reg_on';  
									} else {
										$bgClass = 'cal_reg_off'; 
									}
									$bgClass = 'cal_reg_off';
									$calendar .= '<td id=\''. $iDay .'\'';
									if ($iCurrentDay != $iDay) {
										$var = '';
									} else {
										$var = '_today';
									} 
									if ($iCurrentDay != $iDay && $bgClass != "cal_reg_off") {
										$calendar .= "onmouseover=\"getElementById('".$iDay."').className='mainmenu5';\" onmouseout=\"getElementById('".$iDay."').className='".$bgClass."';\" onClick=\"getLightbox('".$datetocheck."');\">"; 
									} else if ($iCurrentDay == $iDay && $bgClass != "cal_reg_off") {
										$calendar .= "onmouseover=\"getElementById('".$iDay."').className='mainmenu5';\" onmouseout=\"getElementById('".$iDay."').className='".$bgClass.$var."';\" onClick=\"getLightbox('".$datetocheck."');\">"; 
									}
									$calendar .= "class=\"".$bgClass.$var."\">".$iDay;
									//check if this day available for booking or not.
									if ($bgClass == "cal_reg_off"){
										$calendar .= "<span class='hide-me-for-nojs'><br/>0 spots available</span><noscript><br/>0 spots available</noscript>";
									} else { 
										$calendar .= "<br/><a href='session?date=".$datetocheck."'>".$spaces_left." spots available</a>";
									}
									$calendar .= "</td>";	
							} 
						
						
						
					}
				}
				$calendar .= "</tr>";
				
			} 
			
			//set information to the view/session
			$this->data["calendar"] = $calendar;
			$this->data["next_month_link"] = $next_month_link;
			$this->data["prev_month_link"] = $prev_month_link;		
			
			//	write to view with title
			$this->data['title'] = $title;
			$this->template->write_view('content', 'users\cal', $this->data);
			$this->template->render();				
		} else {
			redirect('/auth/');
		}
	}
		
	function session () {
			if($this->session->userdata('logged_in')){

				if($this->input->get('date')){
					$plandate = $this->input->get('date');
					$scopeid = $this->session->userdata('scopeId');
					//	get sessions for this dates
					$where = 'WHERE rollout_sessions.scope_ID = "'.$scopeid.'" and rollout_sessions.session_date = "'.$plandate.'"';
					$query = $this->db->query('	SELECT * FROM rollout_sessions '.$where.' ');
					
					$sessiondata = null;
					
					foreach ($query->result() as $row) {
						$query2 = $this->db->query('
							SELECT *
							FROM rollout_sims
							WHERE session_id = "'. $row->id .'"
						');
						
						if ($query2->num_rows() >= $row->spots) {
							$data['full'] = 'true';
						} else {
							$data['full'] = 'false';
						}
						$data['id'] = $row->id;
						$data['name'] = $row->desc;
						$data['start'] = $row->time_start;
						$data['end'] = $row->time_end;
						
						$sessiondata[] = $data;
					}
					
					// set information to the view
					$this->data['sessions'] = $sessiondata;			
									
					$this->template->write_view('content', 'users/sessions', $this->data);
					$this->template->render();
				} else {
					redirect('/portal/calendar');
				}	
			} else {
				redirect('/auth/');
			}
		}
		
	function plan () {
			if($this->session->userdata('logged_in')){
				if ($this->input->get('source')) {
					$this->data['source'] = $this->input->get('source');
					$linkSource = '?source='. $this->data['source'] .'';
				}
				
				//get information input/session
				$this->load->model('mDiscUserPool', 'userpool');
				$rolloutid = $this->session->userdata('mig_id');
				$migrationUser = $this->session->userdata('MP_user');				
				$mode = $this->session->userdata('MP_source');								
				$sessionid = $this->input->post('session');				
												
				$user_username = $this->userpool->getName($migrationUser);
				
				//$user_cn= $this->session->userdata('cn');
				
				$where = 'WHERE rollout_sessions.id = "'. $sessionid .'"';
				$query = $this->db->query('	SELECT * FROM rollout_sessions '. $where .' ');
				$result = $query->row_array();
				
				$where = 'WHERE subscriber_aurora = "'. $this->session->userdata('username') .'"';
				$query = $this->db->query('	SELECT * FROM rollout_sims '. $where .' ');
				
				$i = 0;
				
				foreach ($query->result() as $row) {
					$i = $i + 1;
					$data['i'] = $i;
					$data['ro_ID'] = $row->ro_ID;
					$data['Subscriber_Email'] = $row->Subscriber_Email;
					$data['Subscriber_Aurora'] = $row->Subscriber_Aurora;
					$data['Phone_nr'] = $row->Phone_nr;
					$data['sim_type'] = $row->sim_type;
					$data['sim_card'] = $row->sim_card;
					$data['Device_type'] = $row->Device_type;
					$data['TCP_email'] = $row->TCP_email;
					$data['TCP_aurora'] = $row->TCP_aurora;
					$data['admin_validation'] = $row->admin_validation;
					$data['admin_val_date'] = $row->admin_val_date;
					
					$sims[] = $data;
				}
				
				
				$result2 = $query->row_array();
				
				//set information to the view
				$this->data["username"] =  "Username: ".$user_username;
				$this->session->set_userdata('MP_session',$sessionid);
				
				$this->data['sessiondate'] = "Date: ".$result["session_date"];
				$this->data['sessiontime'] = "Session time: ".$result["time_start"]." - ".$result["time_end"];
				$this->data['location'] = 'the migration room';
				$this->data['source'] = $this->session->userdata('MP_source');	
				$this->data['sims'] = $sims;				
								
				$this->template->write_view('content','users/overview_user', $this->data);
				$this->template->render();
				
				
			} else {
				//We are not logged in redirect back to login form
				redirect('/auth/');
			}
		}
		
	function finish () {
				
				
		//	get information input/session
		$id = $this->session->userdata('MP_session');
		$rolloutid = $this->session->userdata('mig_id');	
		$mode = $this->session->userdata('MP_source');	
		$migrationUser = $this->session->userdata('MP_user');	
				
		//	prep basic data
		//	check if session still has spots left
		$where = 'WHERE rollout_sessions.id = "'.$id.'"';
		$query = $this->db->query('	SELECT * FROM rollout_sessions '.$where.' ');
		$session = $query->row_array();
				
		$where = 'WHERE rollout_sims.Subscriber_aurora = "'.$migrationUser.'"';
		$query = $this->db->query('
			SELECT * 
			FROM rollout_sims inner join disc_userpool on Subscriber_aurora = ad_account 
			'.$where.'
		');
		$rollout = $query->row_array();
				
		$q = '
			SELECT 
			COUNT(ro_ID) 
			AS num 
			FROM rollout_sims 
			WHERE session_ID = "'. $id .'"
		';
		$query2 = $this->db->query($q);
		$rollout_used = $query2->row_array();
		$spots_left = $session["spots"] - $rollout_used["num"];
				
		if ($spots_left > 0) {					
			$update = array(
				'Mig_status' => "Planned",
				'plan_status' => "1",
				'plan_date' => $session["session_date"],
				'session_id' => $id
			);
					
		$this->db->where('Subscriber_aurora', $migrationUser);
		$this->db->update('rollout_sims', $update);
		
		
		//prepare comment
				
		$dataset['rolloutid'] = $rolloutid;
		$dataset['status'] = 'closed';		
		$dataset['category'] = 'comment';
		$dataset['user_name'] = $this->session->userdata('username');	
		
		//	set information to the view
		if ($mode == 'admin') {			
			$dataset['topic'] = 'Migration planned for '.$session["session_date"].' by administrator';
			$this->insertRolloutComment($dataset);
			redirect('/headquarters/editView?id='.$rolloutid.'');
		} else {			
			$dataset['topic'] = 'Migration planned for '.$session["session_date"].' by user';
			$this->insertRolloutPlanning($dataset);
			$this->data["username"] = $this->session->userdata('username');
		}
		
		
		$this->data["system"] = $rollout["sys_name"];
		
		//	present page ICAL + Logout
		$this->template->write_view('content','users/finish', $this->data);
		$this->template->render();
		
		$this-> makeAppointment();
		
			$Result = True;
					 
		} else {
			redirect('/portal/calendar?error=1');
					
			$Result = False;
		}
			//return $Result;
				
			
			
		}
		
	function makeAppointment() 		{
			
			
			//get information
			$sessionID = $this->session->userdata('MP_session');
			$rolloutID = $this->session->userdata('mig_id');
			
			//retrieve session information from datatbase
			$query = $this->db->query('	SELECT * FROM rollout_sessions WHERE rollout_sessions.id = "'. $sessionID.'" ');
			$result = $query->result();
			
			
						
			
			#
			$UID = "Migrationbooking_vodafone";
			$icsfilename = $UID . ".ics";
			header("Content-Type: text/x-vCalendar");
			header("Content-Disposition: inline; filename=$icsfilename");
	
?>
BEGIN:VCALENDAR
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:REQUEST
BEGIN:VEVENT
DTSTART:<?php echo date('Ymd\THi00', strtotime($result[0]->session_date ." " .$result[0]->time_start)); ?>

DTEND:<?php echo date('Ymd\THi00', strtotime($result[0]->session_date ." " .$result[0]->time_end)); ?>

UID:<?php echo $UID; ?>
DESCRIPTION: The migration of your Vodafone mobile telephone(s)

ORGANIZER;CN="MyMigration":MAILTO:my.migration@dsm.com
SEQUENCE:0
STATUS:CONFIRMED
SUMMARY:The migration of your Vodafone mobile telephone(s)

LOCATION:The migration room mentioned in your invitation

TRANSP:OPAQUE
END:VEVENT
END:VCALENDAR
<?php


		# send with attachement
/*
$file="BEGIN:VCALENDAR
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:REQUEST
BEGIN:VEVENT
DTSTART:" .date('Ymd\THi00', strtotime($result[0]->session_date .$result[0]->time_start)) ."Z
DTEND:" .date('Ymd\THi00', strtotime($result[0]->session_date .$result[0]->time_end)) ."Z
UID:Migratiebooking
ATTENDEE;PARTSTAT=NEEDS-ACTION;RSVP= TRUE;CN=Sample:mailto:voorbeeld@mymigration.com
DESCRIPTION:" .$result[0]->desc ."
LOCATION: Location
SEQUENCE:0
STATUS:CONFIRMED
SUMMARY:" .$result[0]->desc ."
TRANSP:OPAQUE
END:VEVENT
END:VCALENDAR";

		$subject = "Meeting Subject";
		
		$this->load->library('email');

		$mime = "text/x-vCalendar";
		$this->email->string_attach($file, "Migrationbooking.ics", $mime, "attachment"); 

		$this->email->from('kevingorjan@gmail.com', 'Kevin e-Merce');
		$this->email->to('luk.dyck-van@dsm.com'); 

		$this->email->subject($result[0]->desc);
		$this->email->message('ICS test');	

		$this->email->send();

		echo $this->email->print_debugger();
*/
			
		}
		
	function buildCalendar ($iMonth, $iYear) {
			$iFirstDayTimeStamp = mktime(0, 0, 0, $iMonth, 1, $iYear);
			$iFirstDayNum = date('w', $iFirstDayTimeStamp);
			$iFirstDayNum++;
			$iDayCount = date('t', $iFirstDayTimeStamp);
			$aCalendar = array();
			if ($iFirstDayNum > 1) {
				$aCalendar[1][''] = $iFirstDayNum - 1;
			}
			$i = 1;
			$j = 1;
			while ($j <= $iDayCount) {
				$aCalendar[$i][$j] = $j;
				if (floor(($j + $iFirstDayNum - 1) / 7) >= $i) {
					$i++;
				}
				$j++;
			}
			if ((isset($aCalendar[$i])) AND ($iM = count($aCalendar[$i])) < 7) {
				$aCalendar[$i][''] = 7 - $iM;
			}
			return $aCalendar;
		}
		
	function nextMonth ($iMonth, $iYear) {
			if ($iMonth == 12) {
				$iMonth = 1;
				$iYear++;
			} else {
				$iMonth++;
			}
			return array($iMonth, $iYear);
		}
		
	function nextDay ($iDay, $iMonth, $iYear) {
			$iDayTimestamp = mktime(0, 0, 0, $iMonth, $iDay, $iYear);
			$iNextDayTimestamp = strtotime('+1 day', $iDayTimestamp);
			return $iNextDayTimestamp;
		}
		
	function prevDay ($iDay, $iMonth, $iYear) {
			$iDayTimestamp = mktime(0, 0, 0, $iMonth, $iDay, $iYear);
			$iPrevDayTimestamp = strtotime('-1 day', $iDayTimestamp);
			return $iPrevDayTimestamp;
		}
		
	function prevMonth ($iMonth, $iYear) {
			if ($iMonth == 1) {
				$iMonth = 12;
				$iYear--;
			} else {
				$iMonth--;
			}
			return array($iMonth, $iYear);
		}
		
		
	function getMaxSecondsForThisDay ($day) {
			$tt = 0;
			$from = explode(":","09:00"); 
			$to = explode(":","17:00");
			$tt = (($to[0]-$from[0])*60)*60;
			return $tt;
		}
		
	function getNameFromLocId ($id) {
			$name = "FAKELOCNAME";
			return $name;
		}
		
	function ics () {
			$rolloutid = $this->session->userdata('rollout_id');
			$where = 'WHERE rollout_systems.ro_ID = "'.$rolloutid.'"';
			$query = $this->db->query('
				SELECT * 
				FROM rollout_systems 
				'.$where.' 
			');
			$rollout = $query->row_array();	
			$this->load->model('ics');
			$this->load->helper('download');
			$data["date"] = $session["session_date"];
			$data["time_start"] = $session["time_start"];
			$data["time_end"] = $session["time_end"];
			$data["desc"] = "Migration to Windows 7 for the following system -". $rollout["sys_name"];
			
			$ical_data = $this->ics->create($data);
			force_download("appointment.ics",$ical_data);
		}
		
	public function personal () {
		$this->load->model('mDiscUserPool','UserPool');
			$this->data['info'] = $this->UserPool->getData();
			
			// write view with title
			$this->data['title'] = 'Migration Portal | Personal Information';
			$this->template->write_view('content', 'users/personal', $this->data);
			$this->template->render();
		}
		
		public function hardware () {
			// 	write view with title
			$this->data['title'] = 'Migration Portal | Hardware Information';
			$this->template->write_view('content','users/hardware', $this->data);
			$this->template->render();
		}
		
		public function software () {
			//	write view with title
			$this->data['title'] = 'Migration Portal | Software Information';
			$this->template->write_view('content', 'users/software', $this->data);
			$this->template->render();
		}
		
		public function insertRolloutComment ($dataset) {
			
			//Load models and helpers
			$this->load->model('mRolloutComments', 'RolloutComment');				
			
			$this->RolloutComment->InsertComment($dataset);			
					
		}
		
		public function insertRolloutPlanning ($dataset) {
			
			//Load models and helpers
			$this->load->model('mRolloutComments', 'RolloutComment');
			
			$query = $this->db->query('	SELECT * FROM rollout_sims WHERE subscriber_aurora = "'. $this->session->userdata('username') .'" ');
							
			foreach ($query->result() as $row) {
				
				$dataset['rolloutid'] = $row->ro_ID;
				$this->insertRolloutComment($dataset);					
					
			}			
			
			$this->RolloutComment->InsertComment($dataset);			
					
		}
		
		public function userGuide () {
			//	write view with title
			$this->data['title'] = 'Migration Portal | User Guide';
			$this->template->write_view('content','users/userGuide', $this->data);
			$this->template->render();
		}
		
		public function contact () {
			//	write view with title
			$this->data['title'] = 'Migration Portal | Contact';
			$this->template->write_view('content', 'users/contact', $this->data);
			$this->template->render();
		}
		
		public function softwareDetails () {
			//	write view with title
			$this->data['title'] = 'Migration Portal | Software Details';
			$this->template->write_view('content','users/softwareDetails', $this->data);
			$this->template->render();
		}
		
		public function hardwareDetails () {
			//	write view with title
			$this->data['title'] = 'Migration Portal | hardware Details ';
			$this->template->write_view('content','users/hardwareDetails', $this->data);
			$this->template->render();
		}
		
	}
	
?>