<?php
/**
 * Members: Sent Invitations Status
 *
 * @package BuddyPress
 * @subpackage MembersScreens
 * @since 6.0.0
 */

/**
 * Catch and process the Send Invites page.
 *
 * @since 1.0.0
 */
function members_screen_list_sent_invites() {

	/**
	 * Fires before the loading of template for the My Friends page.
	 *
	 * @since 1.0.0
	 */
	do_action( 'members_screen_list_sent_invites' );
echo "members_screen_list_sent_invites";
var_dump( bp_is_invitations_component() );
	/**
	 * Filters the template used to display the My Friends page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template Path to the my friends template to load.
	 */
	bp_core_load_template( apply_filters( 'members_template_list_sent_invites', 'members/single/invitations' ) );
}
