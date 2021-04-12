<?php
/**
 * BuddyPress - Sent Membership Invitations
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 * @version 8.0.0
 */
?>
<h2 class="bp-screen-reader-text">
	<?php
	/* translators: accessibility text */
	esc_html_e( 'Send Invitations', 'buddypress' );
	?>
</h2>

<form class="standard-form network-invitation-form" id="network-invitation-form" method="post">
	<label for="bp_network_invitation_invitee_email"><?php esc_html_e( 'Email address of new user', 'buddypress' ); ?></label>
	<input id="bp_network_invitation_invitee_email" type="email" name="invitee_email" required="required">

	<label for="bp_network_invitation_message"><?php esc_html_e( 'Add a personalized message to the invitation (optional)', 'buddypress' ); ?></label>
	<textarea id="bp_network_invitation_message" name="invite_message"></textarea>

	<input type="hidden" name="action" value="send-invite">

	<?php wp_nonce_field( 'bp_network_invitation_send_' . bp_displayed_user_id() ) ?>
	<p>
		<input id="submit" type="submit" name="submit" class="submit" value="<?php esc_attr_e( 'Send Invitation', 'buddypress' ) ?>" />
	</p>
</form>
