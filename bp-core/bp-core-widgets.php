<?php

/* Register widgets for the core component */
function bp_core_register_widgets() {
	global $current_blog;
	
	/* Site welcome widget */
	register_sidebar_widget( __('Welcome', 'buddypress'), 'bp_core_widget_welcome');
	register_widget_control( __('Welcome', 'buddypress'), 'bp_core_widget_welcome_control' );
	
	/* Site members widget */
	register_sidebar_widget( __('Members', 'buddypress'), 'bp_core_widget_members');
	register_widget_control( __('Members', 'buddypress'), 'bp_core_widget_members_control' );
	
	/* Include the javascript needed for activated widgets only */
	if ( is_active_widget( 'bp_core_widget_members' ) )
		wp_enqueue_script( 'bp_core_widget_members-js', site_url( MUPLUGINDIR . '/bp-core/js/widget-members.js' ), array('jquery', 'jquery-livequery-pack') );		
	
	/* Widgets that can be enabled anywhere */
	register_sidebar_widget( __('Who\'s Online', 'buddypress'), 'bp_core_widget_whos_online');
	register_widget_control( __('Who\'s Online', 'buddypress'), 'bp_core_widget_whos_online_control' );	

}
add_action( 'plugins_loaded', 'bp_core_register_widgets' );


/*** WELCOME WIDGET *****************/

function bp_core_widget_welcome($args) {
	global $current_blog;
	
    extract($args);
	$options = get_blog_option( $current_blog->blog_id, 'bp_core_widget_welcome' );
?>
	<?php echo $before_widget; ?>
	<?php echo $before_title
		. $widget_name
		. $after_title; ?>

	<h3><?php echo $options['title'] ?></h3>
	<p><?php echo $options['text'] ?></p>

	<?php if ( !is_user_logged_in() ) { ?>
	<p class="create-account"><div class="visit generic-button"><a href="<?php bp_signup_page() ?>" title="<?php _e('Create Account', 'buddypress') ?>"><?php _e('Create Account', 'buddypress') ?></a></div></p>
	<?php } ?>
	
	<?php echo $after_widget; ?>
<?php
}

function bp_core_widget_welcome_control() {
	global $current_blog;
	
	$options = $newoptions = get_blog_option( $current_blog->blog_id, 'bp_core_widget_welcome' );

	if ( $_POST['bp-widget-welcome-submit'] ) {
		$newoptions['title'] = strip_tags( stripslashes( $_POST['bp-widget-welcome-title'] ) );
		$newoptions['text'] = stripslashes( wp_filter_post_kses( $_POST['bp-widget-welcome-text'] ) );
	}
	
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_blog_option( $current_blog->blog_id, 'bp_core_widget_welcome', $options );
	}
	
	$title = attribute_escape( $options['title'] );
	$text = attribute_escape( $options['text'] );
?>
		<p><label for="bp-widget-welcome-title"><?php _e('Title:', 'buddypress'); ?> <input class="widefat" id="bp-widget-welcome-title" name="bp-widget-welcome-title" type="text" value="<?php echo $title; ?>" /></label></p>
		<p>
			<label for="bp-widget-welcome-text"><?php _e( 'Welcome Text:' , 'buddypress'); ?>
				<textarea id="bp-widget-welcome-text" name="bp-widget-welcome-text" class="widefat" style="height: 100px"><?php echo $text; ?></textarea>
			</label>
		</p>
		<input type="hidden" id="bp-widget-welcome-submit" name="bp-widget-welcome-submit" value="1" />
<?php
}

/*** MEMBERS WIDGET *****************/

function bp_core_widget_members($args) {
	global $current_blog, $bp;
	
    extract($args);
	$options = get_blog_option( $current_blog->blog_id, 'bp_core_widget_members' );
?>
	<?php echo $before_widget; ?>
	<?php echo $before_title
		. $widget_name 
		. $after_title; ?>
	
	<?php $users = BP_Core_User::get_newest_users( $options['max_members'] ) ?>
	
	<?php if ( $users['users'] ) : ?>
		<div class="item-options" id="members-list-options">
			<img id="ajax-loader-members" src="<?php echo $bp->core->image_base ?>/ajax-loader.gif" height="7" alt="<?php _e( 'Loading', 'buddypress' ) ?>" style="display: none;" /> &nbsp;
			<a href="<?php echo site_url() . '/' . MEMBERS_SLUG ?>" id="newest-members" class="selected"><?php _e( 'Newest', 'buddypress' ) ?></a> | 
			<a href="<?php echo site_url() . '/' . MEMBERS_SLUG ?>" id="recently-active-members"><?php _e( 'Active', 'buddypress' ) ?></a> | 
			<a href="<?php echo site_url() . '/' . MEMBERS_SLUG ?>" id="popular-members"><?php _e( 'Popular', 'buddypress' ) ?></a>
		</div>
		<ul id="members-list" class="item-list">
			<?php foreach ( (array) $users['users'] as $user ) : ?>
				<li class="vcard">
					<div class="item-avatar">
						<a href="<?php echo bp_core_get_userlink( $user->user_id, false, true ) ?>"><?php echo bp_core_get_avatar( $user->user_id, 1 ) ?></a>
					</div>

					<div class="item">
						<div class="item-title fn"><?php echo bp_core_get_userlink( $user->user_id ) ?></div>
						<div class="item-meta"><span class="activity"><?php echo bp_core_get_last_activity( $user->user_registered, __( 'registered %s ago', 'buddypress' ) ) ?></span></div>
					</div>
				</li>
				<?php $counter++; ?>	
			<?php endforeach; ?>
		</ul>
		
		<?php 
		if ( function_exists('wp_nonce_field') )
			wp_nonce_field( 'bp_core_widget_members', '_wpnonce-members' );
		?>
		
		<input type="hidden" name="bp_core_widget_members_max" id="bp_core_widget_members_max" value="<?php echo $options['max_members'] ?>" />
		
	<?php else: ?>
		<div class="widget-error">
			<?php _e('No one has signed up yet!', 'buddypress') ?>
		</div>
	<?php endif; ?>
	
	<?php echo $after_widget; ?>
<?php
}

function bp_core_widget_members_control() {
	global $current_blog;
	
	$options = $newoptions = get_blog_option( $current_blog->blog_id, 'bp_core_widget_members');

	if ( $_POST['bp-core-widget-members-submit'] ) {
		$newoptions['max_members'] = strip_tags( stripslashes( $_POST['bp-core-widget-members-max'] ) );
	}
	
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_blog_option( $current_blog->blog_id, 'bp_core_widget_members', $options );
	}

	$max_members = attribute_escape( $options['max_members'] );
?>
		<p><label for="bp-core-widget-members-max"><?php _e('Max Members to show:', 'buddypress'); ?> <input class="widefat" id="bp-core-widget-members-max" name="bp-core-widget-members-max" type="text" value="<?php echo $max_members; ?>" style="width: 30%" /></label></p>
		<input type="hidden" id="bp-core-widget-members-submit" name="bp-core-widget-members-submit" value="1" />
<?php
}

/*** WHO'S ONLINE WIDGET *****************/

function bp_core_widget_whos_online($args) {
	global $current_blog;
	
    extract($args);
	$options = get_blog_option( $current_blog->blog_id, 'bp_core_widget_whos_online' );
?>
	<?php echo $before_widget; ?>
	<?php echo $before_title
		. $widget_name
		. $after_title; ?>

	<?php $users = BP_Core_User::get_online_users($options['max_members']) ?>

	<?php if ( $users['users'] ) : ?>
		<div class="avatar-block">
		<?php foreach ( (array) $users['users'] as $user ) : ?>
			<div class="item-avatar">
				<a href="<?php echo bp_core_get_userurl($user->user_id) ?>" title="<?php bp_fetch_user_fullname( $user->user_id, true ) ?>"><?php echo bp_core_get_avatar( $user->user_id, 1 ) ?></a>
			</div>
		<?php endforeach; ?>
		</div>
			
		<?php 
		if ( function_exists('wp_nonce_field') )
			wp_nonce_field( 'bp_core_widget_members', '_wpnonce-members' );
		?>

		<input type="hidden" name="bp_core_widget_members_max" id="bp_core_widget_members_max" value="<?php echo $options['max_members'] ?>" />

	<?php else: ?>
		<div class="widget-error">
			<?php _e('There are no users currently online.', 'buddypress') ?>
		</div>
	<?php endif; ?>

	<?php echo $after_widget; ?>
	
	<div class="clear" style="margin-bottom: 25px"></div>
	
<?php
}

function bp_core_widget_whos_online_control() {
	global $current_blog;
	
	$options = $newoptions = get_blog_option( $current_blog->blog_id, 'bp_core_widget_whos_online' );

	if ( $_POST['bp-widget-whos-online-submit'] ) {
		$newoptions['max_members'] = strip_tags( stripslashes( $_POST['bp-widget-whos-online-max-members'] ) );
	}
	
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_blog_option( $current_blog->blog_id, 'bp_core_widget_whos_online', $options );
	}
	
	$max_members = attribute_escape( $options['max_members'] );
?>
		<p><label for="bp-widget-whos-online-max-members"><?php _e('Maximum number of members to show:', 'buddypress'); ?><br /><input class="widefat" id="bp-widget-whos-online-max-members" name="bp-widget-whos-online-max-members" type="text" value="<?php echo $max_members; ?>" style="width: 30%" /></label></p>
		<input type="hidden" id="bp-widget-whos-online-submit" name="bp-widget-whos-online-submit" value="1" />
<?php
}
