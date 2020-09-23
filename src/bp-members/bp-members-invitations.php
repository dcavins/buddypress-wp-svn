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

function bp_members_invitations_setup_nav() {
	if ( ! bp_get_members_invitations_allowed() ) {
		return;
	}

	$user_has_access = bp_user_has_access() && bp_user_can( bp_displayed_user_id(), 'bp_members_send_invitation' );

	/* Add 'Invitations' to the main user profile navigation */
	bp_core_new_nav_item(
		array(
			'name'                    => __( 'Invitations', 'buddypress' ),
			'slug'                    => bp_get_members_invitations_slug(),
			'position'                => 80,
			'screen_function'         => 'members_screen_send_invites',
			'default_subnav_slug'     => 'list-invites',
			'show_for_displayed_user' => $user_has_access
		)
	);

	$parent_link = trailingslashit( bp_displayed_user_domain() . bp_get_members_invitations_slug() );

	/* Create two subnav items for community invitations */
	bp_core_new_subnav_item(
		array(
			'name'            => __( 'Sent Invites', 'buddypress' ),
			'slug'            => 'list-invites',
			'parent_slug'     => bp_get_members_invitations_slug(),
			'parent_url'      => $parent_link,
			'screen_function' => 'members_screen_list_sent_invites',
			'position'        => 10,
			'user_has_access' => $user_has_access
		)
	);

	bp_core_new_subnav_item(
		array(
			'name'            => __( 'Invite New Members', 'buddypress' ),
			'slug'            => 'send-invites',
			'parent_slug'     => bp_get_members_invitations_slug(),
			'parent_url'      => $parent_link,
			'screen_function' => 'members_screen_send_invites',
			'position'        => 20,
			'user_has_access' => $user_has_access && bp_is_my_profile()
		)
	);
}
add_action( 'bp_setup_nav', 'bp_members_invitations_setup_nav' );

/**
 * When a user joins the network via an invitation, skip sending the activation email.
 *
 * @param bool   $send           Whether or not to send the activation key.
 * @param int    $user_id        User ID to send activation key to.
 * @param string $user_email     User email to send activation key to.
 * @param string $activation_key Activation key to be sent.
 * @param array  $usermeta       Miscellaneous metadata about the user (blog-specific
 *                               signup data, xprofile data, etc).
 */
function bp_members_invitations_cancel_activation_email( $send, $user_id, $user_email, $activation_key, $usermeta ) {

	$invite = bp_members_invitations_get_invites(
		array(
			'invitee_email' => $user_email,
			'invite_sent'   => 'sent'
		)
	);

	if ( $invite ) {
		$send = false;
	}

	return $send;
}
add_filter( 'bp_core_signup_send_activation_key', 'bp_members_invitations_cancel_activation_email', 10, 5 );


/**
 * When a user joins the network via an invitation:
 * - mark all invitations and requests as accepted
 * - activate the user upon signup
 *
 * @param bool|WP_Error   $user_id       True on success, WP_Error on failure.
 * @param string          $user_login    Login name requested by the user.
 * @param string          $user_password Password requested by the user.
 * @param string          $user_email    Email address requested by the user.
 */
function bp_members_invitations_complete_signup( $user_id, $user_login, $user_password, $user_email ) {
	if ( ! $user_id ) {
		return;
	}

	// Is this new signup the result of an invitation?
	$invite = bp_members_invitations_get_invites(
		array(
			'invitee_email' => $user_email,
			'invite_sent'   => 'sent'
		)
	);


	if ( ! $invite ) {
		return;
	}

	$invites_class = new BP_Members_Invitation_Manager();
	$args = array(
		'invitee_email' => $user_email,
		'item_id'       => get_current_network_id(),
		'type'          => 'all'
	);
	$invites_class->mark_accepted( $args );

	// User has already verified their email by responding to the invitation, so we can activate.
	$key = bp_get_user_meta( $user_id, 'activation_key', true );
	if ( $key ) {
		/**
		 * Filters the activation signup.
		 *
		 * @since 1.1.0
		 *
		 * @param bool|int $value Value returned by activation.
		 *                        Integer on success, boolean on failure.
		 */
		$user = apply_filters( 'bp_core_activate_account', bp_core_activate_signup( $key ) );

		// If there were errors, add a message and redirect.
		if ( ! empty( $user->errors ) ) {
			bp_core_add_message( $user->get_error_message(), 'error' );
			bp_core_redirect( trailingslashit( bp_get_root_domain() . '/' . $bp->pages->activate->slug ) );
		}

		bp_core_add_message( __( 'Your account is now active!', 'buddypress' ) );
		bp_core_redirect( add_query_arg( 'activated', '1', bp_get_activation_page() ) );
	}
}
add_action( 'bp_core_signup_user', 'bp_members_invitations_complete_signup', 10, 4 );