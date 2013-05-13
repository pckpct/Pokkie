<div id="login-form">
   	
		<form action="<?=site_url('auth/login');?>" method="POST">
		
		<table id="edit">
			<tr>
				<td><span>Role:</span>
					<select name="user_role">
						<option value="1">User</option>
						<option value="2">Site Admin</option>
						<option value="3">Migration member</option>
						<option value="4">Migration lead</option>
						<option value="5" selected>Administrator</option>
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
