<div id="login-form">
   	
		<form action="<?=site_url('auth/index');?>" method="POST">
		
		<table id="edit">
			<tr>
				<td><span>Role:</span>
					<select name="user_role">
						<option value="1">User</option>
						<option value="2">Site Admin</option>
						<option value="3">Floorwalker</option>
						<option value="4">Migration Engineer</option>
						<option value="5">Teamlead</option>
						<option value="6">Teamlead full - Deployment Coordinator</option>
						<option value="7">Deployment Coordinator - full</option>						
						<option value="100" selected>Administrator</option>
						<option value="5">Migration Specialist</option>
					</select>
				</td>
			</tr>
			<tr>
				<td><span>Password:</span><input type="password" name="password" value="" /></td>			
			</tr>
		</table>
	
		<input type="submit" value="Submit">
	</form>
</div>
