<?php
/**
 * BuddyPress - Membership invitations
 *
 * @since 8.0.0
 * @version 8.0.0
 */
// @TODO
?>

<nav class="<?php bp_nouveau_single_item_subnav_classes(); ?>" id="subnav" role="navigation" aria-label="<?php esc_attr_e( 'Groups menu', 'buddypress' ); ?>">
	<ul class="subnav">

		<?php if ( bp_is_my_profile() ) : ?>

			<?php bp_get_template_part( 'members/single/parts/item-subnav' ); ?>

		<?php endif; ?>

	</ul>
</nav><!-- .bp-navs -->

<?php bp_get_template_part( 'common/search-and-filters-bar' ); ?>
eh?
<?php
if ( 'sent-invites' === bp_current_action() ) {
	echo "send invites";
} else {
	echo "default";
}

