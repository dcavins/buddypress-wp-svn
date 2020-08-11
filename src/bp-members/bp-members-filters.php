<?php
/**
 * BuddyPress Members Filters.
 *
 * Filters specific to the Members component.
 *
 * @package BuddyPress
 * @subpackage MembersFilters
 * @since 1.5.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Escape commonly used fullname output functions.
 */
add_filter( 'bp_displayed_user_fullname',    'esc_html' );
add_filter( 'bp_get_loggedin_user_fullname', 'esc_html' );

// Filter the user registration URL to point to BuddyPress's registration page.
add_filter( 'register_url', 'bp_get_signup_page' );

/**
 * Load additional sign-up sanitization filters on bp_loaded.
 *
 * These are used to prevent XSS in the BuddyPress sign-up process. You can
 * unhook these to allow for customization of your registration fields;
 * however, it is highly recommended that you leave these in place for the
 * safety of your network.
 *
 * @since 1.5.0
 */
function bp_members_signup_sanitization() {

	// Filters on sign-up fields.
	$fields = array (
		'bp_get_signup_username_value',
		'bp_get_signup_email_value',
		'bp_get_signup_with_blog_value',
		'bp_get_signup_blog_url_value',
		'bp_get_signup_blog_title_value',
		'bp_get_signup_blog_privacy_value',
		'bp_get_signup_avatar_dir_value',
	);

	// Add the filters to each field.
	foreach( $fields as $filter ) {
		add_filter( $filter, 'esc_html',       1 );
		add_filter( $filter, 'wp_filter_kses', 2 );
		add_filter( $filter, 'stripslashes',   3 );
	}

	// Sanitize email.
	add_filter( 'bp_get_signup_email_value', 'sanitize_email' );
}
add_action( 'bp_loaded', 'bp_members_signup_sanitization' );

/**
 * Make sure the username is not the blog slug in case of root profile & subdirectory blog.
 *
 * If BP_ENABLE_ROOT_PROFILES is defined & multisite config is set to subdirectories,
 * then there is a chance site.url/username == site.url/blogslug. If so, user's profile
 * is not reachable, instead the blog is displayed. This filter makes sure the signup username
 * is not the same than the blog slug for this particular config.
 *
 * @since 2.1.0
 *
 * @param array $illegal_names Array of illiegal names.
 * @return array $illegal_names
 */
function bp_members_signup_with_subdirectory_blog( $illegal_names = array() ) {
	if ( ! bp_core_enable_root_profiles() ) {
		return $illegal_names;
	}

	if ( is_network_admin() && isset( $_POST['blog'] ) ) {
		$blog = $_POST['blog'];
		$domain = '';

		if ( preg_match( '|^([a-zA-Z0-9-])$|', $blog['domain'] ) ) {
			$domain = strtolower( $blog['domain'] );
		}

		if ( username_exists( $domain ) ) {
			$illegal_names[] = $domain;
		}

	} else {
		$illegal_names[] = buddypress()->signup->username;
	}

	return $illegal_names;
}
add_filter( 'subdirectory_reserved_names', 'bp_members_signup_with_subdirectory_blog', 10, 1 );

/**
 * Filter the user profile URL to point to BuddyPress profile edit.
 *
 * @since 1.6.0
 *
 * @param string $url     WP profile edit URL.
 * @param int    $user_id ID of the user.
 * @param string $scheme  Scheme to use.
 * @return string
 */
function bp_members_edit_profile_url( $url, $user_id, $scheme = 'admin' ) {

	// If xprofile is active, use profile domain link.
	if ( ! is_admin() && bp_is_active( 'xprofile' ) ) {
		$profile_link = trailingslashit( bp_core_get_user_domain( $user_id ) . bp_get_profile_slug() . '/edit' );

	} else {
		// Default to $url.
		$profile_link = $url;
	}

	/**
	 * Filters the user profile URL to point to BuddyPress profile edit.
	 *
	 * @since 1.5.2
	 *
	 * @param string $url WP profile edit URL.
	 * @param int    $user_id ID of the user.
	 * @param string $scheme Scheme to use.
	 */
	return apply_filters( 'bp_members_edit_profile_url', $profile_link, $url, $user_id, $scheme );
}
add_filter( 'edit_profile_url', 'bp_members_edit_profile_url', 10, 3 );

/**
 * Filter the bp_user_can value to determine what the user can do in the members component.
 *
 * @since 3.0.0
 *
 * @param bool   $retval     Whether or not the current user has the capability.
 * @param int    $user_id
 * @param string $capability The capability being checked for.
 * @param int    $site_id    Site ID. Defaults to the BP root blog.
 * @param array  $args       Array of extra arguments passed.
 *
 * @return bool
 */
function bp_members_user_can_filter( $retval, $user_id, $capability, $site_id, $args ) {

	switch ( $capability ) {
		case 'manage_network_membership_requests':
			$retval = bp_user_can( $user_id, 'bp_moderate' );
			break;
		case 'bp_network_send_invitation':
			$retval = true;
			break;
		case 'bp_network_receive_invitation':
			$retval = true;
			// The invited user must not already be a member of the network.
			if ( empty( $args['invitee_email'] ) || false !== get_user_by( 'email', $args['invitee_email'] ) ) {
				$retval = false;
			}
			// The invited user must not have opted out from receiving invitations.
			// @TODO:


			break;
	}

	return $retval;

}
add_filter( 'bp_user_can', 'bp_members_user_can_filter', 10, 5 );

function maybe_prevent_activation_emails( $usermeta ) {
	// Stop the activation email from being sent if registration is by request only.
	// $if "anyone can join is not true,"
	if ( true ) {

	}
	return $usermeta;
}
add_filter( 'bp_signup_usermeta', 'maybe_prevent_activation_emails', 10, 1 );

/**
 * Do not allow the new user to change the email address
 * if they are accepting a network invitation.
 *
 * @since 7.0.0
 *
 * @param array  $attributes The field attributes.
 * @param string $name       The field name.
 *
 * @return array $attributes The field attributes.
 */
function maybe_make_registration_email_input_readonly( $attributes, $name ) {
	if ( 'email' === $name && bp_get_network_invitations_allowed() ) {
		$invite = bp_get_network_invitation_from_request();
		if ( $invite->id ) {
			$attributes['readonly'] = 'readonly';
		}
	}
	return $attributes;
}
add_filter( 'bp_get_form_field_attributes', 'maybe_make_registration_email_input_readonly', 10, 2 );

/**
 * Provide a more-specific welcome message if the new user
 * is accepting a network invitation.
 *
 * @since 7.0.0
 *
 * @return string $message The message text.
 */
function bp_network_invitations_get_registration_welcome_message() {
	$message = '';
	if ( ! bp_get_network_invitations_allowed() ) {
		return $message;
	}
	$invite = bp_get_network_invitation_from_request();
	if ( ! $invite->id ) {
		return $message;
	}

	// Fetch the display names of all inviters to personalize the welcome message.
	$all_invites = bp_network_get_invites( array( 'invitee_email' => $invite->invitee_email ) );
	$inviters = array();
	foreach ( $all_invites as $inv ) {
		$inviters[] = bp_core_get_user_displayname( $inv->inviter_id );
	}

	if ( ! empty( $inviters ) ) {
		$message = sprintf( _n( 'Welcome! You&#8217;ve been invited to join the site by the following user: %s. ', 'Welcome! You&#8217;ve been invited to join the site by the following users: %s. ', count( $inviters ), 'buddypress' ), implode( ', ', $inviters ) );
	} else {
		$message = __( 'Welcome! You&#8217;ve been invited to join the site. ', 'buddypress' );
	}
	return $message;
}

/**
 * Provide a more-specific "registration is disabled" message
 * if registration is available by invitation only.
 * Also provide failure note if new user is trying to accept
 * a network invitation but there's a problem.
 *
 * @since 7.0.0
 *
 * @return string $message The message text.
 */
function bp_network_invitations_get_modified_registration_disabled_message() {
	$message = '';
	if ( bp_get_network_invitations_allowed() ) {
		$message = __( 'Member registration is allowed by invitation only.', 'buddypress' );
		// Is the user trying to accept an invitation but something is wrong?
		if ( ! empty( $_GET['inv'] ) ) {
			$message .= __( ' It looks like there is a problem with your invitation. Please try again.', 'buddypress' );
		}
	}
	return $message;
}

/**
 * Modify welcome message in Legacy template pack.
 *
 * @since 7.0.0
 *
 * @return string $message The message text.
 */
function bp_network_invitations_add_legacy_welcome_message() {
	if ( 'legacy' !== bp_get_theme_package_id() ) {
		return;
	}
	$message = bp_network_invitations_get_registration_welcome_message();
	if ( $message ) {
		echo '<p>' . esc_html( $message ) . '</p>';
	}
}
add_action( 'bp_before_register_page', 'bp_network_invitations_add_legacy_welcome_message' );

/**
 * Modify "registration disabled" message in Legacy template pack.
 *
 * @since 7.0.0
 *
 * @return string $message The message text.
 */
function bp_network_invitations_add_legacy_registration_disabled_message() {
	if ( 'legacy' !== bp_get_theme_package_id() ) {
		return;
	}
	$message = bp_network_invitations_get_modified_registration_disabled_message();
	if ( $message ) {
		echo "<p>{$message}</p>";
	}
}
add_action( 'bp_after_registration_disabled', 'bp_network_invitations_add_legacy_registration_disabled_message' );

/**
 * Modify "registration disabled" message in Nouveau template pack.
 * Modify welcome message in Nouveau template pack.
 *
 * @since 7.0.0
 *
 * @param array $messages The list of feedback messages.
 *
 * @return array $messages
 */
function bp_network_invitations_filter_nouveau_registration_messages( $messages ) {
	// Change the "registration is disabled" message.
	$disallowed_message = bp_network_invitations_get_modified_registration_disabled_message();
	if ( $disallowed_message ) {
		$messages['registration-disabled']['message'] = $disallowed_message;
	}
	// Add information about invitations to the welcome block.
	$welcome_message = bp_network_invitations_get_registration_welcome_message();
	if ( $welcome_message ) {
		$messages['request-details']['message'] = $welcome_message . $messages['request-details']['message'];
	}
	return $messages;
}
add_action( 'bp_nouveau_feedback_messages', 'bp_network_invitations_filter_nouveau_registration_messages', 99 );
