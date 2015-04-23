<?php
/**
 * BuddyPress Invitation Functions.
 *
 * @package BuddyPress
 * @subpackage Core
 * @since BuddyPress (2.3.0)
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Table of Contents
 * 1. Create
 * 2. Retrieve
 * 3. Update
 * 4. Delete
 * 5. Helpers
 * 6. Caching
 */

/** Create ********************************************************************/

/**
 * Add an invitation to a specific user, from a specific user, related to a
 * specific component.
 *
 * @since BuddyPress (2.3.0)
 *
 * @param array $args {
 *     Array of arguments describing the invitation. All are optional.
 *	   @type int    $user_id ID of the invited user.
 *	   @type int    $inviter_id ID of the user who created the invitation.
 *	   @type string $invitee_email Email address of the invited user.
 * 	   @type string $component_name Name of the related component.
 *	   @type string $component_action Name of the related component action.
 * 	   @type int    $item_id ID associated with the invitation and component.
 * 	   @type int    $secondary_item_id secondary ID associated with the
 *			        invitation and component.
 * 	   @type string $content Extra information provided by the requester
 *			        or inviter.
 * 	   @type string $date_modified Date the invitation was last modified.
 * 	   @type int    $invite_sent Has the invitation been sent, or is it a
 *			 draft invite?
 * }
 * @return int|bool ID of the newly created invitation on success, false
 *         on failure.
 */
function bp_invitations_add_invitation( $args = array() ) {

	$r = wp_parse_args( $args, array(
		'user_id'           => 0,
		'inviter_id'		=> 0,
		'invitee_email'		=> '',
		'component_name'    => '',
		'component_action'  => '',
		'item_id'           => 0,
		'secondary_item_id' => 0,
		'type'				=> false,
		'content'			=> '',
		'date_modified'     => bp_core_current_time(),
		'invite_sent'       => 0,
	) );

	// If there is no invitee, bail.
	if ( empty( $r['user_id'] ) && empty( $r['invitee_email'] ) ) {
		return false;
	}

	if ( ! empty( $r['inviter_id'] ) ) {
		/**
		 * Is this user allowed to extend invitations from this component/item?
		 *
		 * @since BuddyPress (2.3.0)
		 *
		 * @param array $r Describes the invitation to be added.
		 */
		if ( ! apply_filters( 'bp_invitations_inviter_can_send_invites', true, $r ) ) {
			return false;
		}
	} else {
		/**
		 * In the case of a request, is the item accepting requests?
		 *
		 * @since BuddyPress (2.3.0)
		 *
		 * @param array $r Describes the invitation to be added.
		 */
		if ( ! apply_filters( 'bp_invitations_item_is_accepting_requests', true, $r ) ) {
			return false;
		}
	}

	// Check for existing duplicate invitations
	$existing = bp_invitations_get_invitations( array(
		'user_id'           => $r['user_id'],
		'inviter_id'        => $r['inviter_id'],
		'invitee_email'     => $r['invitee_email'],
		'component_name'    => $r['component_name'],
		'component_action'  => $r['component_action'],
		'item_id'           => $r['item_id'],
		'secondary_item_id' => $r['secondary_item_id'],
	) );

	if ( ! empty( $existing ) ) {
		return false;
	}

	// Set up the new invitation
	$invitation                    = new BP_Invitations_Invitation;
	$invitation->user_id           = $r['user_id'];
	$invitation->inviter_id        = $r['inviter_id'];
	$invitation->invitee_email     = $r['invitee_email'];
	$invitation->component_name    = $r['component_name'];
	$invitation->component_action  = $r['component_action'];
	$invitation->item_id           = $r['item_id'];
	$invitation->secondary_item_id = $r['secondary_item_id'];
	$invitation->type              = $r['type'];
	$invitation->date_modified     = $r['date_modified'];
	$invitation->invite_sent       = $r['invite_sent'];

	// Check for outstanding requests to the same item.
	// An invitation + a request = acceptance.
	$request = bp_invitations_get_requests( array(
		'user_id'           => $r['user_id'],
		'invitee_email'     => $r['invitee_email'],
		'component_name'    => $r['component_name'],
		'component_action'  => $r['component_action'],
		'item_id'           => $r['item_id'],
		'secondary_item_id' => $r['secondary_item_id'],
	) );

	if ( ! empty( $request ) ) {
		// Accept the invitation.
		return bp_invitations_accept_request( $request );
	} else {
		// Save the new invitation.
		return $invitation->save();
	}
}


/** Retrieve ******************************************************************/

/**
 * Get a specific invitation by its ID.
 *
 * @since BuddyPress (2.3.0)
 *
 * @param int $id ID of the invitation.
 * @return BP_Invitations_Invitation object
 */
function bp_invitations_get_invitation_by_id( $id ) {
	return new BP_Invitations_Invitation( $id );
}

/**
 * Get invitations, based on provided filter parameters.
 *
 * @since BuddyPress (2.3.0)
 *
 * @param array $args {
 *     Associative array of arguments. All arguments but $page and
 *     $per_page can be treated as filter values for get_where_sql()
 *     and get_query_clauses(). All items are optional.
 *     @type int|array    $id ID of invitation being updated. Can be an
 *                        array of IDs.
 *     @type int|array    $user_id ID of user being queried. Can be an
 *                        array of user IDs.
 *     @type int|array    $inviter_id ID of user who created the
 *                        invitation. Can be an array of user IDs.
 *                        Special cases
 *     @type string       $type Type of item. An "invite" is sent from one
 *                        user to another. A "request" is submitted by a
 *                        user and no inviter is required.
 *                        Default: 'invite'.
 *     @type string|array $invitee_email Email address of invited users
 *			              being queried. Can be an array of addresses.
 *     @type string|array $component_name Name of the component to
 *                        filter by. Can be an array of component names.
 *     @type string|array $component_action Name of the action to
 *                        filter by. Can be an array of actions.
 *     @type int|array    $item_id ID of associated item. Can be an array
 *                        of multiple item IDs.
 *     @type int|array    $secondary_item_id ID of secondary associated
 *                        item. Can be an array of multiple IDs.
 *     @type string|array $type Invite or request.
 *     @type string       $invite_sent Limit to draft, sent or all
 *                        invitations. 'draft' returns only unsent
 *                        invitations, 'sent' returns only sent
 *                        invitations, 'all' returns all. Default: 'all'.
 *     @type string       $search_terms Term to match against
 *                        component_name or component_action fields.
 *     @type string       $order_by Database column to order by.
 *     @type string       $sort_order Either 'ASC' or 'DESC'.
 *     @type string       $order_by Field to order results by.
 *     @type string       $sort_order ASC or DESC.
 *     @type int          $page Number of the current page of results.
 *                        Default: false (no pagination - all items).
 *     @type int          $per_page Number of items to show per page.
 *                        Default: false (no pagination - all items).
 * }
 * @return array Located invitations.
 */
function bp_invitations_get_invitations( $args ) {
	return BP_Invitations_Invitation::get( $args );
}

/**
 * Get invitations, based on provided filter parameters. This is the
 * Swiss Army Knife function. When possible, use the filter_invitations
 * functions that take advantage of caching.
 *
 * @since BuddyPress (2.3.0)
 *
 * @param array $args {
 *     Associative array of arguments. All arguments but $page and
 *     $per_page can be treated as filter values for get_where_sql()
 *     and get_query_clauses(). All items are optional.
 *     @type int|array    $id ID of invitation. Can be an array of IDs.
 *     @type int|array    $user_id ID of user being queried. Can be an
 *                        array of user IDs.
 *     @type string|array $invitee_email Email address of invited users
 *			              being queried. Can be an array of addresses.
 *     @type string|array $component_name Name of the component to
 *                        filter by. Can be an array of component names.
 *     @type string|array $component_action Name of the action to
 *                        filter by. Can be an array of actions.
 *     @type int|array    $item_id ID of associated item. Can be an array
 *                        of multiple item IDs.
 *     @type int|array    $secondary_item_id ID of secondary associated
 *                        item. Can be an array of multiple IDs.
 *     @type string       $invite_sent Limit to draft, sent or all
 *                        invitations. 'draft' returns only unsent
 *                        invitations, 'sent' returns only sent
 *                        invitations, 'all' returns all. Default: 'all'.
 *     @type string       $search_terms Term to match against
 *                        component_name or component_action fields.
 *     @type string       $order_by Database column to order by.
 *     @type string       $sort_order Either 'ASC' or 'DESC'.
 *     @type string       $order_by Field to order results by.
 *     @type string       $sort_order ASC or DESC.
 *     @type int          $page Number of the current page of results.
 *                        Default: false (no pagination - all items).
 *     @type int          $per_page Number of items to show per page.
 *                        Default: false (no pagination - all items).
 * }
 * @return array Located invitations.
 */
function bp_invitations_get_requests( $args ) {
	// Set request-specific parameters.
	$args['type'] = 'request';
	$args['inviter_id'] = 0;
	$args['invite_sent'] = 'all';
	return BP_Invitations_Invitation::get( $args );
}

/**
 * @param array $args {
 *     Array of optional arguments.
 *     @type string $component_name Name of the component to
 *                        filter by.
 *     @type string $component_action Name of the action to
 *                        filter by.
 *     @type int   $item_id ID of associated item. Can be an array
 *                        of multiple item IDs.
 *     @type int    $secondary_item_id ID of secondary associated
 *                        item. Can be an array of multiple IDs.
 *     @type string       $invite_sent Limit to draft, sent or all
 *                        invitations. 'draft' returns only unsent
 *                        invitations, 'sent' returns only sent
 *                        invitations, 'all' returns all. Default: 'all'.
 *     @type string       $order_by Database column to order by.
 *     @type string       $sort_order Either 'ASC' or 'DESC'.
 * }
 */
function bp_get_user_invitations( $user_id = 0, $args = array(), $invitee_email = false ){
	$r = bp_parse_args( $args, array(
		'component_name'    => '',
		'component_action'  => '',
		'item_id'           => null,
		'secondary_item_id' => null,
 		'type'              => null,
		'invite_sent'       => null,
		'orderby'           => 'id',
		'order'             => 'ASC',
	), 'get_user_invitations' );
	$invitations = array();

	// Two cases: we're searching by email address or user ID.
	if ( ! empty( $invitee_email ) && is_email( $invitee_email ) ) {
		// Get invitations out of the cache, or query if necessary
		$encoded_email = rawurlencode( $invitee_email );
		$invitations = wp_cache_get( 'all_to_user_' . $encoded_email, 'bp_invitations' );
		if ( false === $invitations ) {
			$invitations = BP_Invitations_Invitation::get_all_to_user_email( $invitee_email );
			wp_cache_set( 'all_to_user_' . $encoded_email, $invitations, 'bp_invitations' );
		}
	} else {
		// Default to displayed user or logged-in user if no ID is passed
		if ( empty( $user_id ) ) {
			$user_id = ( bp_displayed_user_id() ) ? bp_displayed_user_id() : bp_loggedin_user_id();
		}
		// Get invitations out of the cache, or query if necessary
		$invitations = wp_cache_get( 'all_to_user_' . $user_id, 'bp_invitations' );
		if ( false === $invitations ) {
			$invitations = BP_Invitations_Invitation::get_all_to_user( $user_id );
			wp_cache_set( 'all_to_user_' . $user_id, $invitations, 'bp_invitations' );
		}
	}

    // Normalize group data.
	foreach ( $invitations as &$invitation ) {
		// Integer values.
		foreach ( array( 'item_id', 'secondary_item_id' ) as $index ) {
			$invitation->{$index} = intval( $invitation->{$index} );
		}
		// Boolean values.
		$invitation->invite_sent = (bool) $invitation->invite_sent;
	}

	// Filter the results
	// Assemble filter array for use in `wp_list_filter()`.
	$filters = wp_array_slice_assoc( $r, array( 'component_name', 'component_action', 'item_id', 'secondary_item_id', 'type', 'invite_sent' ) );
	foreach ( $filters as $filter_name => $filter_value ) {
		if ( is_null( $filter_value ) ) {
			unset( $filters[ $filter_name ] );
		}
	}

	if ( ! empty( $filters ) ) {
		$invitations = wp_list_filter( $invitations, $filters );
	}

	// Sort the results if necessary.
	if ( in_array( $r['orderby'], array( 'component_name', 'component_action', 'item_id', 'secondary_item_id' ) ) ) {
		$invitations = bp_sort_by_key( $invitations, $r['orderby'] );
	}

	// By default, results are ordered ASC.
	if ( 'DESC' === strtoupper( $r['order'] ) ) {
		// `true` to preserve keys.
		$invitations = array_reverse( $invitations, true );
	}

	// @TODO: document filter hook
	return apply_filters( 'bp_get_user_invitations', $invitations, $user_id, $args );
}

function bp_get_user_requests( $user_id = 0, $args = array() ){
	// Requests are a type of invitation, so we can use our main function.
	$args['type']        = 'request';
	// Passing false on the invite_sent will ensure that all statuses are returned.
	$args['invite_sent'] = false;

	// Requests can only be made by registered users, not by email address,
	// so we don't include an email address.
	$requests = bp_get_user_invitations( $user_id, $args );

	// @TODO: document filter hook
	return apply_filters( 'bp_get_user_requests', $requests, $user_id, $args );
}

/**
 * Get outgoing invitations from a user.
 * We get and cache all of the outgoing invitations from a user. We'll
 * filter the complete result set in PHP, in order to take advantage of
 * the cache.
 *
 * @since BuddyPress (2.3.0)
 *
 * @param array $args {
 *     Array of optional arguments.
 *     @type int|array    $user_id ID of user being queried. Can be an
 *                        array of user IDs.
 *     @type string|array $invitee_email Email address of invited users
 *			              being queried. Can be an array of addresses.
 *     @type string|array $component_name Name of the component to
 *                        filter by. Can be an array of component names.
 *     @type string|array $component_action Name of the action to
 *                        filter by. Can be an array of actions.
 *     @type int|array    $item_id ID of associated item. Can be an array
 *                        of multiple item IDs.
 *     @type int|array    $secondary_item_id ID of secondary associated
 *                        item. Can be an array of multiple IDs.
 *     @type string       $invite_sent Limit to draft, sent or all
 *                        invitations. 'draft' returns only unsent
 *                        invitations, 'sent' returns only sent
 *                        invitations, 'all' returns all. Default: 'all'.
 *     @type string       $order_by Database column to order by.
 *     @type string       $sort_order Either 'ASC' or 'DESC'.
 * }
 * @return array $invitations Array of invitation results.
 *               (Returns an empty array if none found.)
 */
function bp_get_invitations_from_user( $inviter_id = 0, $args = array() ) {
	$r = bp_parse_args( $args, array(
		'component_name'    => '',
		'component_action'  => '',
		'item_id'           => null,
		'secondary_item_id' => null,
 		'type'              => null,
		'invite_sent'       => null,
		'orderby'           => 'id',
		'order'             => 'ASC',
	), 'get_user_invitations' );
	$invitations = array();

	// Default to displayed user if no ID is passed
	if ( empty( $inviter_id ) ) {
		$inviter_id = ( bp_displayed_user_id() ) ? bp_displayed_user_id() : bp_loggedin_user_id();
	}

	// Get invitations out of the cache, or query if necessary
	$invitations = wp_cache_get( 'all_from_user_' . $inviter_id, 'bp_invitations' );
	if ( false === $invitations ) {
		$invitations = BP_Invitations_Invitation::get_all_from_user( $inviter_id );
		wp_cache_set( 'all_from_user_' . $inviter_id, $invitations, 'bp_$invitations' );
	}

    // Normalize group data.
	foreach ( $invitations as &$invitation ) {
		// Integer values.
		foreach ( array( 'item_id', 'secondary_item_id' ) as $index ) {
			$invitation->{$index} = intval( $invitation->{$index} );
		}
		// Boolean values.
		$invitation->invite_sent = (bool) $invitation->invite_sent;
	}

	// Filter the results
	// Assemble filter array for use in `wp_list_filter()`.
	$filters = wp_array_slice_assoc( $r, array( 'component_name', 'component_action', 'item_id', 'secondary_item_id', 'type', 'invite_sent' ) );
	foreach ( $filters as $filter_name => $filter_value ) {
		if ( is_null( $filter_value ) ) {
			unset( $filters[ $filter_name ] );
		}
	}

	if ( ! empty( $filters ) ) {
		$invitations = wp_list_filter( $invitations, $filters );
	}

	// Sort the results if necessary.
	if ( in_array( $r['orderby'], array( 'component_name', 'component_action', 'item_id', 'secondary_item_id' ) ) ) {
		$invitations = bp_sort_by_key( $invitations, $r['orderby'] );
	}

	// By default, results are ordered ASC.
	if ( 'DESC' === strtoupper( $r['order'] ) ) {
		// `true` to preserve keys.
		$invitations = array_reverse( $invitations, true );
	}

	// @TODO: document filter hook
	return apply_filters( 'bp_get_user_invitations', $invitations, $user_id, $args );
}

/** Update ********************************************************************/

/**
 * Update invitation, based on provided filter parameters.
 *
 * @since BuddyPress (2.3.0)
 *
 * @see BP_Invitations_Invitation::get() for a description of
 *      accepted update/where arguments.
 *
 * @param array $update_args Associative array of fields to update,
 *              and the values to update them to. Of the format
 *              array( 'user_id' => 4, 'component_name' => 'groups', )
 * @param array $where_args Associative array of columns/values, to
 *              determine which invitations should be updated. Formatted as
 *              array( 'item_id' => 7, 'component_action' => 'members', )
 * @return int|bool Number of rows updated on success, false on failure.
 */
function bp_invitations_update_invitation( $update_args = array(), $where_args = array() ) {
	//@TODO: access check
	return BP_Invitations_Invitation::update( $update_args, $where_args );
}

/**
 * Mark invitation as sent by invitation ID.
 *
 * @since BuddyPress (2.3.0)
 *
 * @param int $id The ID of the invitation to mark as sent.
 * @return bool True on success, false on failure.
 */
function bp_invitations_mark_as_sent_by_id( $id ) {
	//@TODO: access check
	return BP_Invitations_Invitation::mark_as_sent( $id );
}

/**
 * Mark invitations as sent that are found by user_id, inviter_id,
 * invitee_email, component name and action, optional item id,
 * optional secondary item id.
 *
 * @since BuddyPress (2.3.0)
 *
 * @param array $args {
 *     Associative array of arguments. All arguments but $page and
 *     $per_page can be treated as filter values for get_where_sql()
 *     and get_query_clauses(). All items are optional.
 *     @type int|array    $user_id ID of user being queried. Can be an
 *                        array of user IDs.
 *     @type int|array    $inviter_id ID of user who created the
 *                        invitation. Can be an array of user IDs.
 *                        Special cases
 *     @type string|array $invitee_email Email address of invited users
 *			              being queried. Can be an array of addresses.
 *     @type string|array $component_name Name of the component to
 *                        filter by. Can be an array of component names.
 *     @type string|array $component_action Name of the action to
 *                        filter by. Can be an array of actions.
 *     @type int|array    $item_id ID of associated item. Can be an array
 *                        of multiple item IDs.
 *     @type int|array    $secondary_item_id ID of secondary associated
 *                        item. Can be an array of multiple IDs.
 * }
 */
function bp_invitations_mark_as_sent( $args ) {
	//@TODO: access check
	return BP_Invitations_Invitation::mark_as_sent( $args );
}

/** Delete ********************************************************************/

/**
 * Delete a specific invitation by its ID.
 *
 * Used when rejecting invitations or membership requests.
 *
 * @since BuddyPress (2.3.0)
 *
 * @param int $id ID of the invitation to delete.
 * @return int|false Number of rows deleted on success, false on failure.
 */
function bp_invitations_delete_invitation_by_id( $id ) {
	//@TODO: access check
	return BP_Invitations_Invitation::delete_by_id( $id );
}

/**
 * Delete an invitation or invitations by query data.
 *
 * Used when declining invitations.
 *
 * @since BuddyPress (2.3.0)
 *
 * @see bp_invitations_get_invitations() for a description of
 *      accepted where arguments.
 *
 * @param array $args {
 *     Associative array of arguments. All arguments but $page and
 *     $per_page can be treated as filter values for get_where_sql()
 *     and get_query_clauses(). All items are optional.
 *     @type int|array    $user_id ID of user being queried. Can be an
 *                        array of user IDs.
 *     @type int|array    $inviter_id ID of user who created the
 *                        invitation. Can be an array of user IDs.
 *                        Special cases
 *     @type string|array $invitee_email Email address of invited users
 *			              being queried. Can be an array of addresses.
 *     @type string|array $component_name Name of the component to
 *                        filter by. Can be an array of component names.
 *     @type string|array $component_action Name of the action to
 *                        filter by. Can be an array of actions.
 *     @type int|array    $item_id ID of associated item. Can be an array
 *                        of multiple item IDs.
 *     @type int|array    $secondary_item_id ID of secondary associated
 *                        item. Can be an array of multiple IDs.
 *     @type string       $type Invite or request.
 * }
 * @return int|false Number of rows deleted on success, false on failure.
 */
function bp_invitations_delete_invitations( $args ) {
	//@TODO: access check
	return BP_Invitations_Invitation::delete( $args );
}

/**
 * Delete a request or requests by query data.
 *
 * Used when rejecting membership requests.
 *
 * @since BuddyPress (2.3.0)
 *
 * @see bp_invitations_get_invitations() for a description of
 *      accepted where arguments.
 *
 * @param array $args {
 *     Associative array of arguments. All arguments but $page and
 *     $per_page can be treated as filter values for get_where_sql()
 *     and get_query_clauses(). All items are optional.
 *     @type int|array    $user_id ID of user being queried. Can be an
 *                        array of user IDs.
 *     @type int|array    $inviter_id ID of user who created the
 *                        invitation. Can be an array of user IDs.
 *                        Special cases
 *     @type string|array $invitee_email Email address of invited users
 *			              being queried. Can be an array of addresses.
 *     @type string|array $component_name Name of the component to
 *                        filter by. Can be an array of component names.
 *     @type string|array $component_action Name of the action to
 *                        filter by. Can be an array of actions.
 *     @type int|array    $item_id ID of associated item. Can be an array
 *                        of multiple item IDs.
 *     @type int|array    $secondary_item_id ID of secondary associated
 *                        item. Can be an array of multiple IDs.
 * }
 * @return int|false Number of rows deleted on success, false on failure.
 */
function bp_invitations_delete_requests( $args ) {
	//@TODO: access check
	$args['type'] = 'request';
	return BP_Invitations_Invitation::delete( $args );
}

/**
 * Delete all invitations by component.
 *
 * Used when clearing out invitations for an entire component. Possibly used
 * when deactivating a component that created invitations.
 *
 * @since BuddyPress (2.3.0)
 *
 * @param string $component_name Name of the associated component.
 * @param string $component_action Optional. Name of the associated action.
 * @return int|false Number of rows deleted on success, false on failure.
 */
function bp_invitations_delete_all_invitations_by_component( $component_name, $component_action = false ) {
	//@TODO: access check
	return BP_Invitations_Invitation::delete( array(
		'component_name'    => $component_name,
		'component_action'  => $component_action,
	) );
}

/** Helpers *******************************************************************/

/**
 * Get a count of incoming invitations for a user.
 *
 * @since BuddyPress (2.3.0)
 *
 * @param int $user_id ID of the user whose incoming invitations are being
 *        counted.
 * @return int Incoming invitation count.
 */
function bp_invitations_get_incoming_invitation_count( $user_id = 0 ) {
	$invitations = bp_invitations_get_all_to_user( $user_id );
	$count       = ! empty( $invitations ) ? count( $invitations ) : 0;

	return apply_filters( 'bp_invitations_get_incoming_invitation_count', (int) $count );
}

/* Caching ********************************************************************/

/**
 * Invalidate 'all_from_user_' and 'all_to_user_' caches when saving.
 *
 * @since BuddyPress (2.3.0)
 *
 * @param BP_Invitations_Invitation $n Invitation object.
 */
function bp_invitations_clear_user_caches_after_save( BP_Invitations_Invitation $n ) {
	// User_id could be empty if a non-member is being invited via email.
	if ( ! empty( $n->user_id ) ) {
		wp_cache_delete( 'all_to_user_' . $n->user_id, 'bp_invitations' );
	}
	// Inviter_id could be empty if this is a request for membership.
	if ( ! empty( $n->inviter_id ) ) {
		wp_cache_delete( 'all_from_user_' . $n->inviter_id, 'bp_invitations' );
	}
}
add_action( 'bp_invitation_after_save', 'bp_invitations_clear_user_caches_after_save' );

/**
 * Invalidate 'all_from_user_' and 'all_to_user_' caches when
 * updating or deleting.
 *
 * @since BuddyPress (2.3.0)
 *
 * @param int $args Invitation deletion arguments.
 */
function bp_invitations_clear_user_caches_before_update( $args ) {
	// Pull up a list of invitations matching the args (those about te be updated or deleted)
	$invites = BP_Invitations::get( $args );

	$user_ids = array();
	$inviter_ids = array();
	foreach ( $invites as $i ) {
		$user_ids[] 	= $i->user_id;
		$inviter_ids[] 	= $i->inviter_id;
	}

	foreach ( array_unique( $user_ids ) as $user_id ) {
		wp_cache_delete( 'all_to_user_' . $user_id, 'bp_invitations' );
	}

	foreach ( array_unique( $inviter_ids ) as $inviter_id ) {
		wp_cache_delete( 'all_from_user_' . $inviter_id, 'bp_invitations' );
	}
}
add_action( 'bp_invitation_before_update', 'bp_invitations_clear_user_caches_before_update' );
add_action( 'bp_invitation_before_delete', 'bp_invitations_clear_user_caches_before_update' );

//@TODO: Actions for removing invitations when a user is deleted.