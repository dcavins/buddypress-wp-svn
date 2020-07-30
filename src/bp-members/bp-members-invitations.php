<?php
/**
 * BuddyPress Member Activity
 *
 * @package BuddyPress
 * @subpackage MembersActivity
 * @since 2.2.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function bp_invitations_setup_nav() {
	error_log( "running setup_invitations_nav"  );

	/* Add 'Send Invites' to the main user profile navigation */
	bp_core_new_nav_item( array(
		'name' => __( 'Invitations', 'buddypress' ),
		'slug' => bp_get_members_invitations_slug(),
		'position' => 80,
		'screen_function' => 'members_screen_send_invites',
		'default_subnav_slug' => 'invite-new-members',
		'show_for_displayed_user' => true
	) );

	$parent_link = trailingslashit( bp_loggedin_user_domain() . bp_get_members_invitations_slug() );

	/* Create two sub nav items for this component */
	bp_core_new_subnav_item( array(
		'name' => __( 'Invite New Members', 'buddypress' ),
		'slug' => 'invite-new-members',
		'parent_slug' => bp_get_members_invitations_slug(),
		'parent_url' => $parent_link,
		'screen_function' => 'members_screen_send_invites',
		'position' => 10,
		'user_has_access' => true
	) );

	bp_core_new_subnav_item( array(
		'name' => __( 'Sent Invites', 'invite-anyone' ),
		'slug' => 'sent-invites',
		'parent_slug' => bp_get_members_invitations_slug(),
		'parent_url' => $parent_link,
		'screen_function' => 'members_screen_list_sent_invites',
		'position' => 20,
		'user_has_access' => true
	) );
}
add_action( 'bp_setup_nav', 'bp_invitations_setup_nav' );

/**
 *
 *
 * @param bool|WP_Error   $user_id       True on success, WP_Error on failure.
 * @param string          $user_login    Login name requested by the user.
 * @param string          $user_password Password requested by the user.
 * @param string          $user_email    Email address requested by the user.
 */
function bp_network_invitations_mark_complete_on_signup( $user_id, $user_login, $user_password, $user_email ) {
	if ( ! $user_id ) {
		return;
	}
	// @TODO: Find network ID to pass?
	$invites_class = new BP_Network_Invitation_Manager();
	$args = array(
		'invitee_email' => $user_email,
		'item_id'       => get_current_network_id(),
		'type'          => 'all'
	);
	$invites_class->mark_accepted( $args );
}
add_action( 'bp_core_signup_user', 'bp_network_invitations_mark_complete_on_signup', 10, 4 );
