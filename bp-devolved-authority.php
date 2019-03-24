<?php
if ( !defined( 'ABSPATH' ) ) exit;

/*
- allow samegroups admin/mods
- flood protection for non admins/mods
* 
*/


Class BP_Devolved_Authority {
	
	private $data = null;
	private $id = null;
	private $can_manage_groups = false;
	private $can_manage_members = false;
	private $can_manage_activity = false;
	private $can_manage_emails = false;
	
	function bp_devolved_authority( $id = null ) {
		$this->__construct($id);
	}

	function __construct( $id = null ) {
		global $bp;

		//respect the keymaster
		//if ( $bp->loggedin_user->is_super_admin )
		//	return;
		
		if ( !$id && get_current_user_id() ) { 
			$this->id = get_current_user_id();
		} else if ( $id ) {
			$this->id = $id;
		}

		if ( !$this->id )
			return;

		//respect the keymaster
		if ( $this->has_cap( 'administrator' ) )
			return;

		//check for messaging restrictions
		$this->check();
		
	}

	protected function check() {
		
		$user = $this->id ? new WP_User( $this->id ) : wp_get_current_user();
		$user_roles_array = $user->roles ? $user->roles : array();
		$settings = maybe_unserialize( get_option( 'bpda_bp_devolved_authority' ) );
		
		
		// Sort role options into arrays
		$manage_groups_roles = array();
		$manage_members_roles = array();
		$manage_activity_roles = array();
		$manage_emails_roles = array();
		
		foreach( $settings as $role => $setting ) {
			if ( $role == 'groups' ) {
				$manage_groups_roles[] = $setting['role'];
			}
			if ( $role == 'members' ) {
				$manage_members_roles[] = $setting['role'];
			}
			if ( $role == 'activity' ) {
				$manage_activity_roles[] = $setting['role'];
			}
			if ( $role == 'emails' ) {
				$manage_activity_roles[] = $setting['role'];
			}

		}

		// Role check for group functions
		foreach ( $user_roles_array as $key => $role ) {
			if ( in_array( $role, $manage_groups_roles ) )
				$this->can_manage_groups = true;
		}

		// Role check for members functions
		foreach ( $user_roles_array as $key => $role ) {
			if ( in_array( $role, $manage_members_roles ) )
				$this->can_manage_members = true;
		}

		// Role check for activity functions
		foreach ( $user_roles_array as $key => $role ) {
			if ( in_array( $role, $manage_activity_roles ) )
				$this->can_manage_activity = true;
		}
		
		// Role check for email functions
		foreach ( $user_roles_array as $key => $role ) {
			if ( in_array( $role, $manage_emails_roles ) )
				$this->can_manage_emails = true;
		}
		
	}
	
	protected function has_cap( $cap ) {
		global $wpdb;
		
		if ( !$cap )
			return false;
				
		$displayedcaps = get_user_meta( $this->id, $wpdb->prefix.'capabilities', true );
		
		if ( !$displayedcaps || empty( $displayedcaps ) )
			return false;

		return array_key_exists( $cap, $displayedcaps );
	}
	
	public function user_can_manage_groups() {
		return $this->can_manage_groups;
	}

	public function user_can_manage_members() {
		return $this->can_manage_members;
	}

	public function user_can_manage_activity() {
		return $this->can_manage_activity;
	}

	public function user_can_manage_emails() {
		return $this->can_manage_emails;
	}

}



// Main function to over-ride default bp_moderate behaviour and grant it to selected roles.
function bpda_add_bp_moderate_cap_for_role( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

	global $pagenow;
	// Bail if not checking the 'bp_moderate' cap.
	if ( 'bp_moderate' !== $cap ) {
		return $caps;
	}

	// Bail if BuddyPress is not network activated.
	if ( bp_is_network_activated() ) {
		return $caps;
	}
	
	if( $user_id == 0 ) {
		$user_id = get_current_user_id();
	}
	
	// Never trust inactive users.
	if ( bp_is_user_inactive( $user_id ) ) {
		return $caps;
	}

	// Only users that with the right role on this site can 'bp_moderate'.
	if ( 'bp_moderate' == $cap ) {
		$request_url = sanitize_text_field( $_SERVER['REQUEST_URI'] );
		
		$current_user_control = New BP_Devolved_Authority( $user_id );
		if ( $request_url == '/wp-admin/admin.php?page=bp-groups' ) {
			if ( $current_user_control->user_can_manage_groups() ) {
				return array('edit_posts' );
			} else {
				return array( 'manage_options' );
			}
		} else if ( $request_url == '/wp-admin/admin.php?page=bp-activity' ) {
			if ( $current_user_control->user_can_manage_activity() ) {
				return array('edit_posts' );
			} else {
				return array( 'manage_options' );
			}
		} else if ( $pagenow == 'users.php' ) {
			if ( $current_user_control->user_can_manage_members() ) {
				return array('edit_posts' );
			} else {
				return array( 'manage_options' );
			}
		} else if ( $request_url == '/wp-admin/admin.php?page=bp-email' ) {
			if ( $current_user_control->user_can_manage_emails() ) {
				return array('edit_posts' );
			} else {
				return array( 'manage_options' );
			}
		} else if ( bp_is_group() ) {
			if ( $current_user_control->user_can_manage_groups() ) {
				return array('edit_posts' );
			} else {
				return array( 'manage_options' );
			}
		} else if ( bp_is_activity_component() ) {
			if ( $current_user_control->user_can_manage_activity() ) {
				return array('edit_posts' );
			} else {
				return array( 'manage_options' );
			}
		} else if ( bp_is_user_profile() ) {
			if ( $current_user_control->user_can_manage_members() ) {
				return array('edit_posts' );
			} else {
				return array( 'manage_options' );
			}
		} else if ( is_admin() ) {
			if ( $current_user_control->user_can_manage_groups() || $current_user_control->user_can_manage_activity() || $current_user_control->user_can_manage_emails() || $current_user_control->user_can_manage_members() ) {
				return array('edit_posts' );
			} else {
				return array( 'manage_options' );
			}
		} else {
			return $caps;
		}
	}
}
add_filter( 'map_meta_cap', 'bpda_add_bp_moderate_cap_for_role', 99, 4 );

//AJAX check permissions
function bpda_check_permissions(){
	
	check_ajax_referer( 'bpda-nonce', 'security' );
	
	$user_id = get_current_user_id();
	
	$user_control = new BP_Devolved_Authority( $user_id );
	
	$manage_activity = $user_control->user_can_manage_activity();
	$manage_emails = $user_control->user_can_manage_emails();
	$manage_groups = $user_control->user_can_manage_groups();
	
	if ( current_user_can( 'manage_options' ) ) {
		$manage_activity = $manage_emails = $manage_groups = true;
	}
	
	$response = array( $manage_activity,$manage_groups, $manage_emails);
	$response=json_encode($response);
	echo $response;

	die();
}

add_action( 'wp_ajax_bpda_check_permissions', 'bpda_check_permissions');
?>
