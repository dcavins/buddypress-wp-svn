<?php
/**
 * BuddyPress - Send a Membership Invitation.
 *
 * @since 8.0.0
 * @version 8.0.0
 */
?>
<h2 class="bp-screen-reader-text">
	<?php
	/* translators: accessibility text */
	esc_html_e( 'Send Invitation', 'buddypress' );
	?>
</h2>

<p class="bp-feedback info">
	<span class="bp-icon" aria-hidden="true"></span>
	<span class="bp-help-text">
		<?php esc_html_e( 'Submitting the form below will send a message to the person of your choice thanks to their email (as long as they are not yet a member of this site). By default the message contains an invitation link to join the site.', 'buddypress' ); ?>
		<?php esc_html_e( 'You can use the optional multiline text field to customize this message.', 'buddypress' ); ?>
	</span>
</p>

<form class="standard-form network-invitation-form" id="network-invitation-form" method="post">
	<label for="bp_network_invitation_invitee_email">
		<?php esc_html_e( 'Email', 'buddypress' ); ?>
		<span class="bp-required-field-label"><?php esc_html_e( '(required)', 'buddypress' ); ?></span>
	</label>
	<input id="bp_network_invitation_invitee_email" type="email" name="invitee_email" required="required">

	<label for="bp_network_invitation_message">
		<?php esc_html_e( 'Optional: add a message to your invite.', 'buddypress' ); ?>
	</label>
	<textarea id="bp_network_invitation_message" name="invite_message"></textarea>

	<input type="hidden" name="action" value="send-invite">

	<?php bp_nouveau_submit_button( 'member-send-invite' ); ?>
</form>
