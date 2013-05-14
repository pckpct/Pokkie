<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<? $logged_in = 1; ?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8">
	<script type="text/javascript">var BASE = "<?php echo base_url(); ?>";</script>
	<?=$_scripts; ?>
	<?=$_styles; ?>
	<? if (isset($title)) { ?>
	<title><?=$title; ?></title>
	<? } ?>
	
</head>
<body>
<div id="body-container">
	<div id="wrap">
		<div id="logout">
			<?if (isset($role_level)) {?>
			<a href="<?=site_url('auth/logout'); ?>">Logout</a>
			<?} else {?>
			<a href="<?=site_url('auth/'); ?>">Log In</a>
			<?}?>
		</div>
		<div id="heading">
			<h1 id="logo">
				<img src="<?=base_url('image/my_mig.png'); ?>" />
			</h1>
			<div id="top-links-outer">
				<div id="top-links-inner">
					<div id="top-links">			
						<!-- Top menu according to logged on user role -->				
						<? if (isset($role_level) && $role_level == "2") { ?> <!-- Site Contact Person -->
						<ul>	
							<li class="top-menu-link"><a <?if (isset($module) && $module == "siteportal") {?> id="selected" <?}?> href="<?=site_url('siteportal/home')?>">Site Portal</a></li>
							<li class="spacer"></li>																	
							<li class="top-menu-link"><a <?if (isset($module) && $module == "portal") {?> id="selected" <?}?> href="<?=site_url('portal')?>">Migration Portal</a></li>												
						</ul>
						<? } else if (isset($role_level) && $role_level == "3") { ?> <!-- Floorwalker -->
						<ul>	
							<li class="top-menu-link"><a <?if (isset($module) && $module == "portal") {?> id="selected" <?}?> href="<?=site_url('portal')?>">Migration Portal</a></li>
							<li class="spacer"></li>
							<li class="top-menu-link"><a <?if (isset($module) && $module == "manager") {?> id="selected" <?}?> href="<?=site_url('manager/home')?>">Roll-Out</a></li>
							<li class="spacer"></li>
							<li class="top-menu-link"><a <?if (isset($module) && $module == "actionList") {?> id="selected" <?}?> href="<?=site_url('actionList/tasks')?>">Action List</a></li>
						</ul>
						<? } else if (isset($role_level) && $role_level == "4") { ?> <!-- Migration Engineer -->
						<ul>	
							<li class="top-menu-link"><a <?if (isset($module) && $module == "portal") {?> id="selected" <?}?> href="<?=site_url('portal')?>">Migration Portal</a></li>
							<li class="spacer"></li>
							<li class="top-menu-link"><a <?if (isset($module) && $module == "manager") {?> id="selected" <?}?> href="<?=site_url('manager/home')?>">Roll-Out</a></li>
						</ul>
						<? } else if (isset($role_level) && $role_level == "5") { ?> <!-- Team Lead -->										
						<ul>						
							<!-- <li class="top-menu-link"><a <?if (isset($module) && $module == "headquarters") {?> id="selected" <?}?> href="<?=site_url('headquarters/home')?>">Admin Console</a></li>
							<li class="spacer"></li>  -->
							<li class="top-menu-link"><a <?if (isset($module) && $module == "portal") {?> id="selected" <?}?> href="<?=site_url('portal')?>">Migration Portal</a></li>
							<li class="spacer"></li>
							<li class="top-menu-link"><a <?if (isset($module) && $module == "actionList") {?> id="selected" <?}?> href="<?=site_url('actionList/tasks')?>">Action List</a></li>
						</ul>
						<? } else if (isset($role_level) && $role_level == "6") { ?> <!-- Deplyment Coordinator -->	<!-- Full Teamlead -->								
						<ul>
							<li class="top-menu-link"><a <?if (isset($module) && $module == "landscape") {?> id="selected" <?}?> href="<?=site_url('landscape/home')?>">Landscape</a></li>
							<li class="spacer"><div></div></li>							
							<li class="top-menu-link"><a <?if (isset($module) && $module == "discovery") {?> id="selected" <?}?> href="<?=site_url('discovery/home')?>">Inventory</a></li>
							<li class="spacer"><div></div></li>							
							<li class="top-menu-link"><a <?if (isset($module) && $module == "headquarters") {?> id="selected" <?}?> href="<?=site_url('headquarters/home')?>">Preparation</a></li>
							<li class="spacer"></li>						
							<li class="top-menu-link"><a <?if (isset($module) && $module == "portal") {?> id="selected" <?}?> href="<?=site_url('portal')?>">Migration Portal</a></li>
							<li class="spacer"></li>
							<li class="top-menu-link"><a <?if (isset($module) && $module == "manager") {?> id="selected" <?}?> href="<?=site_url('manager/home')?>">Roll-Out</a></li>
						</ul>
						<? } else if (isset($role_level) && ($role_level == "7")) { ?> <!-- Deployment Coordinator / Data Analyst -->
						<ul>	
							<li class="top-menu-link"><a <?if (isset($module) && $module == "landscape") {?> id="selected" <?}?> href="<?=site_url('landscape/home')?>">Landscape</a></li>
							<li class="spacer"><div></div></li>																	
							<li class="top-menu-link"><a <?if (isset($module) && $module == "siteportal") {?> id="selected" <?}?> href="<?=site_url('siteportal/home')?>">Site Portal</a></li>
							<li class="spacer"><div></div></li>	
							<li class="top-menu-link"><a <?if (isset($module) && $module == "discovery") {?> id="selected" <?}?> href="<?=site_url('discovery/home')?>">Inventory</a></li>
							<li class="spacer"><div></div></li>						
							<li class="top-menu-link"><a <?if (isset($module) && $module == "headquarters") {?> id="selected" <?}?> href="<?=site_url('headquarters/home')?>">Preparation</a></li>
							<li class="spacer"><div></div></li>						
							<li class="top-menu-link"><a <?if (isset($module) && $module == "portal") {?> id="selected" <?}?> href="<?=site_url('portal')?>">Migration Portal</a></li>
							<li class="spacer"></li>
							<li class="top-menu-link"><a <?if (isset($module) && $module == "manager") {?> id="selected" <?}?> href="<?=site_url('manager/home')?>">Roll-Out</a></li>
						</ul>											
						<? } else if (isset($role_level) && ($role_level >= "10")) { ?> <!-- Overall MyMigration administrator -->
						<ul>	
							<? /*<li class="top-menu-link"><a <?if (isset($module) && $module == "landscape") {?> id="selected" <?}?> href="<?=site_url('landscape/home')?>">Landscape</a></li>
							<li class="spacer"><div></div></li>											
							<li class="top-menu-link"><a <?if (isset($module) && $module == "siteportal") {?> id="selected" <?}?> href="<?=site_url('siteportal/home')?>">Site Portal</a></li>
							<li class="spacer"><div></div></li>
							<li class="top-menu-link"><a <?if (isset($module) && $module == "discovery") {?> id="selected" <?}?> href="<?=site_url('discovery/home')?>">Inventory</a></li>
							<li class="spacer"><div></div></li>								*/?>				
							<li class="top-menu-link"><a <?if (isset($module) && $module == "headquarters") {?> id="selected" <?}?> href="<?=site_url('headquarters/home')?>">Preparation</a></li>
							<li class="spacer"><div></div></li>						
							<li class="top-menu-link"><a <?if (isset($module) && $module == "portal") {?> id="selected" <?}?> href="<?=site_url('portal')?>">Migation Portal</a></li>
							<? /* <li class="spacer"><div></div></li>
							<li class="top-menu-link"><a <?if (isset($module) && $module == "manager") {?> id="selected" <?}?> href="<?=site_url('manager/home')?>">RollOut</a></li> */?>
							<li class="spacer"></li>
							<li class="top-menu-link"><a <?if (isset($module) && $module == "actionList") {?> id="selected" <?}?> href="<?=site_url('actionList/tasks')?>">Action List</a></li>
							
						</ul>
						<? } ?>		
					</div>		
				</div>
			</div>
		</div>
		<div id="header-spacer"></div>
		<div id="page-container">
			<div id="left-bar">
				<?if (isset($module)) {?>
				<div id="module">
					<span id="module-heading">
						<?if ($module == 'portal') {?>
						Migration Portal
						<?} else if ($module == 'siteportal') {?> <!-- Windows 7 site portal -->
						Site Portal
						<?} else if ($module == 'landscape') {?> <!-- Windows 7 landscape -->
						Landscape
						<?} else if ($module == 'discovery') {?> <!-- Windows 7 inventory -->
						Inventory
						<?} else if ($module == 'headquarters') {?> <!-- Windows 7 preparation -->
						Admin Console
						<?} else if ($module == 'manager') {?> <!-- Windows 7 rollout -->
						Roll-Out
						<?} else if ($module == 'actionList') {?> <!-- Windows 7 action list -->
						Action List
						<?} else if ($module == 'reporting') {?> <!-- Windows 7 Reporting -->
						Reporting						
						<?}?>
					</span>
				</div>
				<?}?>
				<div id="links">
					<? if (isset($module) && $module == "portal") { ?>
					<ul>
						<!-- <li><a href="<?=site_url('portal/personal')?>">Personal Information</a></li> -->					
						<!-- <li><a href="<?=site_url('portal/hardware')?>">Hardware Information</a></li> -->
						<!-- <li><a href="<?=site_url('portal/software')?>">Software Information</a></li> -->
						<li><a href="<?=site_url('portal/planning')?>">Migration</a></li>
						<!-- <li><a href="<?=site_url('portal/userGuide')?>">User Guide</a></li> -->
						<!-- <li><a href="<?=site_url('portal/contact')?>">Contact</a></li> -->
					</ul>	
					<? } else if (isset($module) && $module == "siteportal") { ?>
					<ul>
						<li><a href="<?=site_url('siteportal/home');?>">Home</a></li>						
						<li><a href="<?=site_url('siteportal/workstations');?>">Workstations</a></li>
						<li><a href="<?=site_url('siteportal/users');?>">Users</a></li>
						<li><a href="<?=site_url('siteportal/software')?>">Software</a></li>
						<li><a href="<?=site_url('siteportal/hardware');?>">Hardware</a></li>
					</ul>
					
					<? } else if (isset($module) && $module == "landscape") { ?>
					<ul>
						<li><a href="<?=site_url('landscape/home');?>">Home</a></li>						
						<li><a href="<?=site_url('landscape/workstations');?>">Workstations</a></li>
						<li><a href="<?=site_url('landscape/users');?>">Users</a></li>
						<li><a href="<?=site_url('landscape/software')?>">Software</a></li>
						<li><a href="<?=site_url('landscape/hardware');?>">Hardware</a></li>
					</ul>
					<? } else if (isset($module) && $module == "discovery") { ?>
					<ul>
						<li><a href="<?=site_url('discovery/home');?>">Home</a></li>					
						<li><a href="<?=site_url('discovery/workstation');?>">Workstations</a></li>
						<li><a href="<?=site_url('discovery/user');?>">Users</a></li>
						<li><a href="<?=site_url('discovery/software')?>">Software</a></li>	
						<li><a href="<?=site_url('discovery/scopeReports');?>">Scope Content</a></li>						
						<li><a href="<?=site_url('discovery/scopeSummary');?>">Scope Summary</a></li>					
						
					</ul>
					<? } else if (isset($module) && $module == "scoping") { ?>
					<ul>
						<li><a href="<?=site_url('scoping/home');?>">Home</a></li>					
						<li><a href="<?=site_url('scoping/migration');?>">Migrations</a></li>
						<li><a href="<?=site_url('scoping/user');?>">Users</a></li>
						<li><a href="<?=site_url('scoping/sessions')?>">Sessions</a></li>
						<li><a href="<?=site_url('scoping/locations');?>">Locations</a></li>						
						<li><a href="<?=site_url('scoping/scopeContent');?>">Scope Content</a></li>						
					</ul>					
					<? } else if (isset($module) && $module == "headquarters") { ?>
					<ul>
						<li><a href="<?=site_url('headquarters/home');?>">Home</a></li>
						<li><a href="<?=site_url('headquarters/migrations');?>">Migrations</a></li>
						<li><a href="<?=site_url('headquarters/sessions?mode=all')?>">Sessions</a></li>
						<li><a href="<?=site_url('headquarters/planning');?>">Planning</a></li>
						<li><a href="<?=site_url('headquarters/scopeReports');?>">Reports</a></li>							
						<li><a href="<?=site_url('headquarters/taskManager');?>">Issues</a></li>
						<li><a href="<?=site_url('headquarters/scopeDetails');?>">Scope Details</a></li>
					</ul>
					<? } else if (isset($module) && $module == "manager") { ?>
					<ul>
						<li><a href="<?=site_url('manager/home');?>">Home</a></li>					
						<li><a href="<?=site_url('manager/migrations');?>">Migrations</a></li>
						<!-- <li><a href="<?=site_url('manager/user');?>">Users</a></li> -->
						<li><a href="<?=site_url('manager/planning');?>">Planning</a></li>
						<li><a href="<?=site_url('manager/taskManager');?>">Issues / Tasks</a></li>
						<!-- <li><a href="<?=site_url('manager/taskManager');?>">Task Manager</a></li> -->
						<!-- <li><a href="<?=site_url('manager/knowledgeBase');?>">Knowledge Base</a></li> -->
					</ul>										
					<? } else if (isset($module) && $module == "developer") { ?>
					<ul>
						<li><a href="<?=site_url('developer/workstation');?>">Workstations</a></li>
						<li><a href="<?=site_url('developer/user');?>">Users</a></li>
					</ul>
					<? } else if (isset($module) && $module == "actionList") { ?>
					<ul>
						<li><a href="<?=site_url('actionList/tasks');?>">Tasks</a></li>
					</ul>
					 <? } else { ?>
					<ul>
						<!-- <li><a href="#">Contact</a></li> -->
					</ul>
					<? } ?>
				</div>
				<div id="side-links-bottom"></div>
				<div id="dsm">
					<img src="<?=base_url('image/dsm.gif'); ?>" />
				</div>
				<? if (isset($rightcontent) && $rightcontent <> "") { ?>
					<div id="rightcontent">
						<?=$rightcontent?>
					</div>
				<? } ?>
			</div>
			<div id="content">
				<?=$content; ?>
			</div>
		</div>
	</div>
</div>
	<div id="footer">
		<span id="footer-content">@ copyright Creatingdreams 2012-2014</span>
	</div>
	<?php if($this->config->item('show_support_field') == true): ?>
	<div id="feedbackform" class="feedback-hover">
		<h3>Send Feedback</h3>
		<form>
			<div>				
				<label for="fb_name">Name: </label>
				<input type="text" name="fb_name" id="fb_name" disabled="disabled" value="277268">
			</div>
			<div>
				<label for="fb_description">Description: </label>
				<textarea name="fb_description" id="fb_description" placeholder="Describe your problem" cols="30" rows="10"></textarea>
			</div>
			<div>
				<button id="js-feedback">Send feedback</button>
			</div>
			<input type="hidden" name="fb_report_page" value="http://95.211.130.165:8080/index.php/portal">
		</form>
	</div>
	<?php endif; ?>
</body>
</html>