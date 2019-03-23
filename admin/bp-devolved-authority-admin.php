<?php 
if(!defined('ABSPATH')) {
	exit;
}

function bpda_bp_devolved_authority_admin() {

	/* If the form has been submitted and the admin referrer checks out, save the settings */
	if ( isset( $_POST['submit'] ) && check_admin_referer('bpda_bp_devolved_authority_admin') ) {
	
		$new = array();
		$sites_roles = get_editable_roles();
		$sites_roles['not-set'] = 'Not Set';
		$bp_mod_controls = array();
		if ( bp_is_active( 'groups' ) )
			$bp_mod_controls['groups']	= 'Groups';
		if ( bp_is_active( 'members' ) )
			$bp_mod_controls['members']	= 'Members';
		if ( bp_is_active( 'activity' ) )
			$bp_mod_controls['activity'] = 'Activity';
		$bp_mod_controls['emails'] = 'Emails';
		
		
		foreach ( $bp_mod_controls as $control => $control_name ) {
			
			$new[$control]['role'] = sanitize_text_field( $_POST[ 'bpda-role-option-' . $control ] );
			$new[$control]['role_select'] = sanitize_text_field( $_POST[ 'bpda-role-select-' . $control ] );
			
		}
				
		update_option( 'bpda_bp_devolved_authority', $new );
		
		$updated = true;
	}
	
	$data = maybe_unserialize( get_option( 'bpda_bp_devolved_authority') );
	

	// Get the proper URL for submitting the settings form. (Settings API workaround) - boone
	$url_base = function_exists( 'is_network_admin' ) && is_network_admin() ? network_admin_url( 'admin.php?page=bp-devolved-authority-settings' ) : admin_url( 'admin.php?page=bp-devolved-authority-settings' );
	
	$bp_group_options = array(
		'not-set'		=> sanitize_text_field( __( 'Not set', 'bp-devolved-authority' ) ),
		'groups'		=> sanitize_text_field( __( 'Manage Groups', 'bp-devolved-authority' ) )
	);
	$bp_members_options = array(
		'not-set'		=> sanitize_text_field( __( 'Not set', 'bp-devolved-authority' ) ),
		'members'		=> sanitize_text_field( __( 'Manage Members', 'bp-devolved-authority' ) )
	);
	$bp_activity_options = array(
		'not-set'		=> sanitize_text_field( __( 'Not set', 'bp-devolved-authority' ) ),
		'activity'		=> sanitize_text_field( __( 'Manage Activity', 'bp-devolved-authority' ) )
	);
	$bp_emails_options = array(
		'not-set'		=> sanitize_text_field( __( 'Not set', 'bp-devolved-authority' ) ),
		'emails'		=> sanitize_text_field( __( 'Manage Emails', 'bp-devolved-authority' ) )
	);
	$bp_moderate_controls = array();
	if ( bp_is_active( 'groups' ) )
		$bp_moderate_controls['groups']	= 'Groups';
	if ( bp_is_active( 'members' ) )
		$bp_moderate_controls['members']	= 'Members';
	if ( bp_is_active( 'activity' ) )
		$bp_moderate_controls['activity'] = 'Activity';
	$bp_moderate_controls['emails'] = 'Emails';
	$site_roles = get_editable_roles();
	$site_roles['not-set'] = 'Not set';
	?>	
	<div class="wrap">
		<h2><?php echo sanitize_text_field( __( 'BP Devolved Authority Settings', 'bp-devolved-authority' ) ); ?></h2>

		<?php if ( isset($updated) ) : echo "<div id='message' class='updated fade'><p>" . __( 'Settings Updated.', 'bp-devolved-authority' ) . "</p></div>"; endif; ?>

		<form action="<?php echo $url_base ?>" name="bp-devolved-authority-settings-form" id="bp-devolved-authority-settings-form" method="post">			

			<h2><?php echo sanitize_text_field( __( 'Role based Devolved Authority', 'bp-devolved-authority' ) ); ?></h2>

			<div>
				<?php foreach ( $bp_moderate_controls as $control_key => $control_description ) : ?>
				
						<h4><?php echo $control_description . ' ' .sanitize_text_field( __( 'devolved authority', 'bp-devolved-authority' ) ); ?></h4>
						
						<select name="bpda-role-option-<?php echo $control_key; ?>" id="">
							
							<?php foreach ( $site_roles as $role => $role_capabilities ) : ?>
								
								<?php $setting = isset( $data[$control_key]['role'] ) ? $data[$control_key]['role'] : 'not-set'; ?>  

								<?php if ( $role == 'administrator' ) continue; ?>
						
								<option name="" value="<?php echo $role; ?>" <?php if ( $role == $setting ) echo 'selected'; ?>><?php echo $role; ?></option>
							
							<?php endforeach; ?>
						
						</select>
						
						<label><?php echo sanitize_text_field( __( ' can ', 'bp-devolved-authority' ) ); ?></label>

						<select name="bpda-role-select-<?php echo $control_key; ?>" id="">
							
						
							<?php if ( $control_key == 'groups' ) : ?>
							
								<?php foreach ( $bp_group_options as $option => $option_description ) : ?>
									
									<?php $control_setting = isset( $data[$control_key]['role_select'] ) ? $data[$control_key]['role_select'] : 'not-set'; ?>
							
									<option name="" value="<?php echo $option; ?>" <?php if ( $option == $control_setting ) echo 'selected'; ?>><?php echo $option_description; ?></option>
								
								<?php endforeach; ?>
								
							<?php endif; ?>
						
							<?php if ( $control_key == 'members' ) : ?>
							
								<?php foreach ( $bp_members_options as $option => $option_description ) : ?>
									
									<?php $control_setting = isset( $data[$control_key]['role_select'] ) ? $data[$control_key]['role_select'] : 'not-set'; ?>
							
									<option name="" value="<?php echo $option; ?>" <?php if ( $option == $control_setting ) echo 'selected'; ?>><?php echo $option_description; ?></option>
								
								<?php endforeach; ?>
								
							<?php endif; ?>
						
							<?php if ( $control_key == 'activity' ) : ?>
							
								<?php foreach ( $bp_activity_options as $option => $option_description ) : ?>
									
									<?php $control_setting = isset( $data[$control_key]['role_select'] ) ? $data[$control_key]['role_select'] : 'not-set'; ?>
							
									<option name="" value="<?php echo $option; ?>" <?php if ( $option == $control_setting ) echo 'selected'; ?>><?php echo $option_description; ?></option>
								
								<?php endforeach; ?>
								
							<?php endif; ?>
						
							<?php if ( $control_key == 'emails' ) : ?>
							
								<?php foreach ( $bp_emails_options as $option => $option_description ) : ?>
									
									<?php $control_setting = isset( $data[$control_key]['role_select'] ) ? $data[$control_key]['role_select'] : 'not-set'; ?>
							
									<option name="" value="<?php echo $option; ?>" <?php if ( $option == $control_setting ) echo 'selected'; ?>><?php echo $option_description; ?></option>
								
								<?php endforeach; ?>
								
							<?php endif; ?>
						
						</select>

				<?php endforeach; ?>
				
			</div>

			<?php wp_nonce_field( 'bpda_bp_devolved_authority_admin' ); ?>
			
			<p class="submit"><input type="submit" name="submit" value="Save Settings"/></p>
			
		</form>
		
	</div>
<?php
}

?>
