<?php
/**
 * Core community visibility functions.
 *
 * @package BuddyPress
 * @subpackage CommunityVisibility
 * @since 12.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The main change on a private site is that visitors who are not
 * logged in may not have the `bp_view` capability.
 *
 * @since 12.0.0
 *
 * @param bool   $retval     Whether or not the current user has the capability.
 * @param int    $user_id
 * @param string $capability The capability being checked for.
 * @param int    $site_id    Site ID. Defaults to the BP root blog.
 * @param array  $args       Array of extra arguments passed.
 *
 * @return bool
 */
function bp_community_visibility_user_can_filter( $retval, $user_id, $capability, $site_id, $args ) {
	switch ( $capability ) {
		case 'bp_view':
			if ( ! $user_id ) {
				$component = $args['bp_component'] ?? '';

				if ( $component && 'members' === bp_community_visibility_get_visibility( $component ) ) {
					$retval = false;
				}

				/**
				 * Filters the private site capability.
				 *
				 * @since 12.0.0
				 *
				 * @param bool   $retval     Whether or not the current user has the capability.
				 * @param int    $user_id
				 * @param string $capability The capability being checked for.
				 * @param int    $site_id    Site ID. Defaults to the BP root blog.
				 * @param array  $args       Array of extra arguments passed.
				 */
				$retval = apply_filters( 'bp_private_site_user_can_filter', $retval, $user_id, $capability, $site_id, $args );
			}
			break;
	}

	return $retval;
}
add_filter( 'bp_user_can', 'bp_community_visibility_user_can_filter', 10, 5 );

/**
 * Set default permissions for the BP REST API.
 *
 * @since 12.0.0
 */
function bp_community_visibility_rest_set_default_permission_checks() {
	$visibility_settings = bp_community_visibility_get_visibility();

	foreach ( $visibility_settings as $component_id => $setting ) {
		if ( 'global' === $component_id || 'anyone' === $setting ) {
			continue;
		}

		if ( 'xprofiles' === $component_id ) {
			add_filter( 'bp_rest_xprofile_field_groups_get_items_permissions_check', 'bp_community_visibility_rest_check_default_permission', 1, 2 );
			add_filter( 'bp_rest_xprofile_fields_get_items_permissions_check', 'bp_community_visibility_rest_check_default_permission', 1, 2 );
		} else {
			add_filter( "bp_rest_{$component_id}_get_items_permissions_check", 'bp_community_visibility_rest_check_default_permission', 1, 2 );
		}
	}
}
add_action( 'bp_rest_api_init', 'bp_community_visibility_rest_set_default_permission_checks', 1 );

/**
 * Checks if a natively "public" BP REST request can be performed.
 *
 * @since 12.0.0
 *
 * @param true            $retval  Returned value.
 * @param WP_REST_Request $request The request sent to the API.
 * @return bool True if the user has access. False otherwise.
 */
function bp_community_visibility_rest_check_default_permission( $retval, $request ) {
	$path         = wp_parse_url( $request->get_route(), PHP_URL_PATH );
	$component_id = trim( str_replace( bp_rest_namespace() . '/' . bp_rest_version(), '', trim( $path, '/' ) ), '/' );
	$args         = array();

	if ( $component_id ) {
		$args['bp_component'] = $component_id;
	}

	return bp_current_user_can( 'bp_view', $args );
}

/**
 * Should RSS feeds for activity be enabled?
 *
 * @since 12.0.0
 *
 * @param bool   $feed_enabled True if feeds are enabled. Default true.
 * @param string $feed_id      The feed identifier.
 */
function bp_community_visibility_rss_feed_access_protection( $feed_enabled, $feed_id ) {
	// @TODO: I'm not sure this is adequate, since feeds are about other components, too.
	// From the hook, "possible feed_ids are 'sitewide', 'personal', 'friends', 'mygroups', 'mentions', 'favorites'"
	// Which component should those other items refer to?
	if ( ! bp_current_user_can( 'bp_view', array( 'bp_component' => 'activity' ) ) ) {
		/**
		 * Allow plugins to allow specific feeds even when community visibility is limited.
		 *
		 * @since 12.0.0
		 *
		 * @param bool  $feed_enabled True to allow access to the feed.
		 * @param array $feed_id      The feed identifier.
		 */
		$feed_enabled = apply_filters( 'bp_community_visibility_rss_feed_access_protection', false, $feed_id );
	}
	return $feed_enabled;
}
add_filter( 'bp_activity_enable_feeds', 'bp_community_visibility_rss_feed_access_protection', 10, 2 );

/**
 * Get the community visibility value calculated from the
 * saved visibility setting.
 *
 * @since 12.0.0
 *
 * @param string $component Whether we want the visibility for a component
 *                          or for all components.
 *
 * @return arrary|string $retval The calculated visbility settings for the site.
 */
function bp_community_visibility_get_visibility( $component = 'all' ) {
	$retval      = 'anyone';
	$saved_value = (array) get_option( '_bp_community_visibility', array() );

	// If the global value has not been set, we assume that the site is open.
	if ( ! isset( $saved_value['global'] ) ) {
		$saved_value['global'] = 'anyone';
	}

	if ( 'all' === $component ) {
		// Build the component list.
		$retval = array(
			'global' => $saved_value['global']
		);
		$directory_pages = bp_core_get_directory_pages();
		foreach ( $directory_pages as $component_id => $component_page ) {
			if ( in_array( $component_id, array( 'register', 'activate' ), true ) ) {
				continue;
			}
			$retval[ $component_id ] = $saved_value[ $component_id ] ?? $saved_value['global'];
		}
	} else {
		// We are checking a particular component.
		// Fall back to the global value if not set.
		$retval = $saved_value[ $component ] ?? $saved_value['global'];
	}

	/**
	 * Filter the community visibility value calculated from the
	 * saved visibility setting.
	 *
	 * @since 12.0.0
	 *
	 * @param arrary|string $retval    The calculated visbility settings for the site.
	 * @param string        $component The component value to get the visibility for.
	 */
	return apply_filters( 'bp_community_visibility_get_visibility', $retval, $component );
}

/**
 * Sanitize the visibility setting when it is saved.
 *
 * @since 12.0.0
 *
 * @param mixed $saved_value The value passed to the save function.
 */
function bp_community_visibility_sanitize_setting( $saved_value ) {
	$retval = array();

	// Use the global setting, if it has been passed.
	$retval['global'] = $saved_value['global'] ?? 'anyone';
	// Ensure the global value is a valid option. Else, assume that the site is open.
	if ( ! in_array( $retval['global'], array( 'anyone', 'members' ), true ) ) {
		$retval['global'] = 'anyone';
	}

	// Keys must be either 'global' or a component ID, but not register or activate.
	$directory_pages = bp_core_get_directory_pages();
	foreach ( $directory_pages as $component_id => $component_page ) {
		if ( in_array( $component_id, array( 'register', 'activate' ), true ) ) {
			continue;
		}

		// Use the global value if a specific value hasn't been set.
		$component_value = $saved_value[ $component_id ] ?? $retval['global'];

		// Valid values are 'anyone' or 'memebers'.
		if ( ! in_array( $component_value, array( 'anyone', 'members' ), true ) ) {
			$component_value = $retval['global'];
		}
		$retval[ $component_id ] = $component_value;
	}

	return $saved_value;
}
