/*
* @package bp-devolved-authority
*/

//

// Setup variables for button images
jQuery(document).ready(function($){

	$( '.toplevel_page_bp-activity' ).hide();
	$( '.toplevel_page_bp-groups' ).hide();
	$( '.menu-icon-bp-email' ).hide();

	$.ajax({
		url : ajax_object.ajaxurl,
		type : 'post',
		data : {
			security : ajax_object.check_nonce,
			action : "bpda_check_permissions"
		},
		success : function(data) {
			data=JSON.parse(data);
			if ( Array.isArray(data) ) {
				if ( data[0] == true ) {
					$( '.toplevel_page_bp-activity' ).show();
				}
				if ( data[1] == true ) {
					$( '.toplevel_page_bp-groups' ).show();
				}
				if ( data[2] == true ) {
					$( '.menu-icon-bp-email' ).show();
				}
			} else {
				console.log('Not array', data);
			}
			
		},
		error : function(data){
			console.log('not success',data);
		}
	});	
});