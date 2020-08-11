<?php
/**
 * BuddyPress - Members Sent Network Invitations
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 * @version 3.0.0
 */
?>

<?php if ( bp_has_network_invitations() ) : ?>

	<h2 class="bp-screen-reader-text"><?php
		/* translators: accessibility text */
		_e( 'Invitations', 'buddypress' );
	?></h2>

	<div id="pag-top" class="pagination no-ajax">
		<div class="pag-count" id="invitations-count-top">
			<?php bp_network_invitations_pagination_count(); ?>
		</div>

		<div class="pagination-links" id="invitations-pag-top">
			<?php bp_network_invitations_pagination_links(); ?>
		</div>
	</div>

	<?php bp_get_template_part( 'members/single/invitations/invitations-loop' ); ?>

	<div id="pag-bottom" class="pagination no-ajax">
		<div class="pag-count" id="invitations-count-bottom">
			<?php bp_network_invitations_pagination_count(); ?>
		</div>

		<div class="pagination-links" id="invitations-pag-bottom">
			<?php bp_network_invitations_pagination_links(); ?>
		</div>
	</div>

<?php else : ?>

	<p><?php _e( 'There are no invitations to display.', 'buddypress' ); ?></p>

<?php endif;
