<?php
/**
 * Group invitations class.
 *
 * @package BuddyPress
 * @subpackage Core
 * @since 7.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Group invitations class.
 *
 * An extension of the core Invitations class that adapts the
 * core logic to accommodate group invitation behavior.
 *
 * @since 7.0.0
 */
class BP_Members_Invitation_Manager extends BP_Invitation_Manager {
	/**
	 * Construct parameters.
	 *
	 * @since 7.0.0
	 *
	 * @param array|string $args.
	 */
	public function __construct( $args = '' ) {
		parent::__construct();
	}

	/**
	 * This is where custom actions are added to run when notifications of an
	 * invitation or request need to be generated & sent.
	 *
	 * @since 7.0.0
	 *
	 * @param obj BP_Invitation $invitation The invitation to send.
	 * @return bool True on success, false on failure.
	 */
	public function run_send_action( BP_Invitation $invitation ) {
		// Notify site admins of the pending request
		if ( 'request' === $invitation->type ) {
			// @TODO
			return true;
		// Notify the invitee of the invitation.
		} else {
			$inviter_ud = bp_core_get_core_userdata( $invitation->inviter_id );

			// @TODO: Handle unsubscribes differently since these are not members?
			$unsubscribe_args = array(
				'user_id'           => 0,
				'notification_type' => 'bp-members-invitation',
			);
			$invite_url = esc_url(
				add_query_arg(
					array(
						'inv' => $invitation->id,
						'ih'  => bp_members_invitations_get_hash( $invitation ),
					), bp_get_signup_page()
				)
			);

			$args = array(
				'tokens' => array(
					'displayname'         => $invitation->invitee_email,
					'network.url'         => get_site_url( bp_get_root_blog_id() ),
					'network.name'        => get_bloginfo( 'name' ),
					'network.description' => get_bloginfo( 'description' ),
					'inviter.name'        => bp_core_get_userlink( $invitation->inviter_id, true, false, true ),
					'inviter.url'         => bp_core_get_user_domain( $invitation->inviter_id ),
					'inviter.id'          => $invitation->inviter_id,
					'invites.url'         => esc_url( $invite_url ),
					'invite.message'      => $invitation->content,
					// @TODO: add unsubscribe method that isn't reliant on user being a member of the site.
					// 'unsubscribe'         => esc_url( bp_email_get_unsubscribe_link( $unsubscribe_args ) ),
				),
			);

			return bp_send_email( 'bp-members-invitation', $invitation->invitee_email, $args );
		}
	}

	/**
	 * This is where custom actions are added to run when an invitation
	 * or request is accepted.
	 *
	 * @since 7.0.0
	 *
	 * @param string $type Are we accepting an invitation or request?
	 * @param array  $r    Parameters that describe the invitation being accepted.
	 * @return bool True on success, false on failure.
	 */
	public function run_acceptance_action( $type = 'invite', $r  ) {
		// If the user is already a member (because BP at one point allowed two invitations to
		// slip through), return early.

		if ( 'request' === $type ) {
			/**
			 * Fires after a network membership request has been accepted.
			 *
			 * @since 1.0.0
			 *
			 * @param int  $user_id  ID of the user who accepted membership.
			 * @param int  $group_id ID of the group that was accepted membership to.
			 */
			do_action( 'network_membership_request_accepted', $r['user_id'], $r['item_id'] );
		} else {
			/**
			 * Fires after a user has accepted a group invite.
			 *
			 * @since 1.0.0
			 * @since 2.8.0 The $inviter_id arg was added.
			 *
			 * @param int $user_id    ID of the user who accepted the membership invite.
			 * @param int $inviter_id ID of the user who invited this user to the group.
			 */
			do_action( 'network_membership_invite_accepted', $r['user_id'], $inviter_id );
		}


		return true;
	}

	/**
	 * Should this invitation be created?
	 *
	 * @since 7.0.0
	 *
	 * @param array $args.
	 * @return bool
	 */
	public function allow_invitation( $args ) {
		// Does the inviter have this capability?
		if ( ! bp_user_can( $args['inviter_id'], 'bp_members_send_invitation' ) ) {
			return false;
		}

		// Is the invited user eligible to receive an invitation? Hasn't opted out?
		if ( ! bp_user_can( 0, 'bp_members_receive_invitation', $args ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Should this request be created?
	 *
	 * @since 7.0.0
	 *
	 * @param array $args.
	 * @return bool.
	 */
	public function allow_request( $args ) {
		// Does the requester have this capability?
		if ( ! bp_user_can( 0, 'bp_network_request_membership', $args ) ) {
			return false;
		}

		return true;
	}
}
