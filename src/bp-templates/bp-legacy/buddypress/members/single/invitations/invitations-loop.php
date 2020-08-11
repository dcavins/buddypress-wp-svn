<?php
/**
 * BuddyPress - Members Invitations Loop
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 * @version 3.0.0
 */

?>
<form action="" method="post" id="invitations-bulk-management">
	<table class="invitations">
		<thead>
			<tr>
				<th class="icon"></th>
				<th class="bulk-select-all"><input id="select-all-invitations" type="checkbox"><label class="bp-screen-reader-text" for="select-all-invitations"><?php
					/* translators: accessibility text */
					_e( 'Select all', 'buddypress' );
				?></label></th>
				<th class="title"><?php _e( 'Invitee', 'buddypress' ); ?></th>
				<th class="content"><?php _e( 'Message', 'buddypress' ); ?></th>
				<th class="sent"><?php _e( 'Sent', 'buddypress' ); ?></th>
				<th class="accepted"><?php _e( 'Accepted', 'buddypress' ); ?></th>
				<th class="date"><?php _e( 'Date Modified', 'buddypress' ); ?></th>
				<th class="actions"><?php _e( 'Actions',    'buddypress' ); ?></th>
			</tr>
		</thead>

		<tbody>

			<?php while ( bp_the_network_invitations() ) : bp_the_network_invitation(); ?>

				<tr>
					<td></td>
					<td class="bulk-select-check"><label for="<?php bp_the_network_invitation_property( 'id' ); ?>"><input id="<?php bp_the_network_invitation_property( 'id' ); ?>" type="checkbox" name="network_invitations[]" value="<?php bp_the_network_invitation_property( 'id' ); ?>" class="invitation-check"><span class="bp-screen-reader-text"><?php
						/* translators: accessibility text */
						_e( 'Select this invitation', 'buddypress' );
					?></span></label></td>
					<td class="invitation-invitee"><?php bp_the_network_invitation_property( 'invitee_email' );  ?></td>
					<td class="invitation-content"><?php wptexturize( bp_the_network_invitation_property( 'content' ) );  ?></td>
					<td class="invitation-sent"><?php bp_the_network_invitation_property( 'invite_sent' );  ?></td>
					<td class="invitation-accepted"><?php bp_the_network_invitation_property( 'accepted' );  ?></td>
					<td class="invitation-date-modified"><?php bp_the_network_invitation_property( 'date_modified' );   ?></td>
					<td class="invitation-actions"><?php bp_the_network_invitation_action_links(); ?></td>
				</tr>

			<?php endwhile; ?>

		</tbody>
	</table>

	<div class="invitations-options-nav">
		<?php // @TODO //bp_invitations_bulk_management_dropdown(); ?>
	</div><!-- .invitations-options-nav -->

	<?php wp_nonce_field( 'invitations_bulk_nonce', 'invitations_bulk_nonce' ); ?>
</form>
