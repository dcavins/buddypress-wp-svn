<?php
/**
 * @group core
 * @group nav
 */
class BP_Tests_Core_Nav_BpCoreNewSubnavItem extends BP_UnitTestCase {
	protected $permalink_structure = '';

	public function set_up() {
		parent::set_up();
		$this->permalink_structure = get_option( 'permalink_structure', '' );
	}

	public function tear_down() {
		$this->set_permalink_structure( $this->permalink_structure );

		parent::tear_down();
	}

	/**
	 * @expectedIncorrectUsage bp_nav
	 */
	public function test_user_subnav() {
		$this->set_permalink_structure( '/%postname%/' );
		$bp_options_nav = buddypress()->bp_options_nav;

		$u = self::factory()->user->create();
		$old_current_user = get_current_user_id();
		$this->set_current_user( $u );

		$this->go_to( bp_members_get_user_url( $u ) );

		bp_core_new_nav_item( array(
			'name'            => 'Foo Parent',
			'slug'            => 'foo-parent',
			'screen_function' => 'foo_screen_function',
			'position'        => 10,
		) );

		bp_core_new_subnav_item( array(
			'name'            => 'Foo',
			'slug'            => 'foo',
			'parent_url'      => bp_members_get_user_url(
				$u,
				array(
					'single_item_component' => 'foo-parent',
				)
			),
			'parent_slug'     => 'foo-parent',
			'screen_function' => 'foo_screen_function',
			'position'        => 10
		) );

		$expected = array(
			'name'              => 'Foo',
			'link'              => bp_members_get_user_url(
				$u,
				array(
					'single_item_component' => 'foo-parent',
					'single_item_action'    => 'foo',
				)
			),
			'slug'              => 'foo',
			'css_id'            => 'foo',
			'position'          => 10,
			'user_has_access'   => true,
			'no_access_url'     => '',
			'screen_function'   => 'foo_screen_function',
			'show_in_admin_bar' => false,
		);

		foreach ( $expected as $k => $v ) {
			$this->assertSame( $v, buddypress()->bp_options_nav['foo-parent']['foo'][ $k ] );
		}

		// Clean up
		buddypress()->bp_options_nav = $bp_options_nav;
		$this->set_current_user( $old_current_user );
	}

	public function test_required_params() {
		// 'name'
		$this->assertFalse( bp_core_new_subnav_item( array(
			'slug' => 'foo',
			'parent_slug' => 'foo',
			'screen_function' => 'foo',
		) ) );

		// 'slug'
		$this->assertFalse( bp_core_new_subnav_item( array(
			'name' => 'foo',
			'parent_slug' => 'foo',
			'screen_function' => 'foo',
		) ) );

		// 'parent_slug'
		$this->assertFalse( bp_core_new_subnav_item( array(
			'name' => 'foo',
			'slug' => 'foo',
			'screen_function' => 'foo',
		) ) );

		// 'screen_function'
		$this->assertFalse( bp_core_new_subnav_item( array(
			'name' => 'foo',
			'slug' => 'foo',
			'parent_slug' => 'foo',
		) ) );
	}

	public function test_site_admin_only() {
		$old_current_user = get_current_user_id();
		$this->set_current_user( 0 );

		$this->assertFalse( bp_core_new_subnav_item( array(
			'name' => 'foo',
			'slug' => 'foo',
			'parent_slug' => 'foo',
			'parent_url' => 'foo',
			'screen_function' => 'foo',
			'site_admin_only' => true,
		) ) );

		$this->set_current_user( $old_current_user );
	}

	/**
	 * @expectedIncorrectUsage bp_nav
	 */
	public function test_link_provided() {
		$bp_options_nav = buddypress()->bp_options_nav;

		bp_core_new_nav_item( array(
			'name' => 'Foo',
			'slug' => 'foo',
			'screen_function' => 'foo',
			'link' => 'https://buddypress.org/',
		) );

		bp_core_new_subnav_item( array(
			'name' => 'bar',
			'slug' => 'bar',
			'parent_slug' => 'foo',
			'parent_url' => 'foo',
			'screen_function' => 'foo',
			'link' => 'https://buddypress.org/',
		) );

		$this->assertSame( 'https://buddypress.org/', buddypress()->bp_options_nav['foo']['bar']['link'] );

		buddypress()->bp_options_nav = $bp_options_nav;
	}

	/**
	 * @expectedIncorrectUsage bp_nav
	 */
	public function test_link_built_from_parent_url_and_slug() {
		$bp_options_nav = buddypress()->bp_options_nav;

		bp_core_new_nav_item( array(
			'name' => 'Foo',
			'slug' => 'foo',
			'screen_function' => 'foo',
			'link' => 'https://buddypress.org/',
		) );

		bp_core_new_subnav_item( array(
			'name' => 'bar',
			'slug' => 'bar',
			'parent_slug' => 'foo',
			'parent_url' => 'http://example.com/foo/',
			'screen_function' => 'foo',
		) );

		$this->assertSame( 'http://example.com/foo/bar/', buddypress()->bp_options_nav['foo']['bar']['link'] );

		buddypress()->bp_options_nav = $bp_options_nav;
	}

	/**
	 * @expectedIncorrectUsage bp_nav
	 */
	public function test_link_built_from_parent_url_and_slug_where_slug_is_default() {
		$bp_nav = buddypress()->bp_nav;
		$bp_options_nav = buddypress()->bp_options_nav;

		bp_core_new_nav_item( array(
			'name' => 'Foo',
			'slug' => 'foo',
			'url' => 'http://example.com/foo/',
			'screen_function' => 'foo',
			'default_subnav_slug' => 'bar',
		) );

		bp_core_new_subnav_item( array(
			'name' => 'bar',
			'slug' => 'bar',
			'parent_slug' => 'foo',
			'parent_url' => 'http://example.com/foo/',
			'screen_function' => 'foo',
		) );

		$this->assertSame( 'http://example.com/foo/bar/', buddypress()->bp_options_nav['foo']['bar']['link'] );

		// clean up
		buddypress()->bp_nav = $bp_nav;
		buddypress()->bp_options_nav = $bp_options_nav;
	}

	/**
	 * @expectedIncorrectUsage bp_nav
	 */
	public function test_should_trailingslash_link_when_link_is_autogenerated_using_slug() {
		$this->set_permalink_structure( '/%postname%/' );
		bp_core_new_nav_item( array(
			'name' => 'Foo',
			'slug' => 'foo',
			'screen_function' => 'foo',
			'link' => 'https://buddypress.org/',
		) );

		bp_core_new_subnav_item( array(
			'name' => 'bar',
			'slug' => 'bar',
			'parent_slug' => 'foo',
			'parent_url' => bp_get_root_url() . 'foo/',
			'screen_function' => 'foo',
		) );

		$expected = bp_get_root_url() . 'foo/bar/';
		$this->assertSame( $expected, buddypress()->bp_options_nav['foo']['bar']['link'] );
	}

	/**
	 * @expectedIncorrectUsage bp_nav
	 */
	public function test_should_trailingslash_link_when_link_is_autogenerated_not_using_slug() {
		$this->set_permalink_structure( '/%postname%/' );
		bp_core_new_nav_item( array(
			'name' => 'foo',
			'slug' => 'foo-parent',
			'link' => bp_get_root_url() . 'foo-parent/',
			'default_subnav_slug' => 'bar',
			'screen_function' => 'foo',
		) );

		bp_core_new_subnav_item( array(
			'name' => 'bar',
			'slug' => 'bar',
			'parent_slug' => 'foo-parent',
			'parent_url' => bp_get_root_url() . '/foo-parent/',
			'screen_function' => 'bar',
		) );

		$expected = bp_get_root_url() . '/foo-parent/bar/';
		$this->assertSame( $expected, buddypress()->bp_options_nav['foo-parent']['bar']['link'] );
	}

	/**
	 * @ticket BP6353
	 * @expectedIncorrectUsage bp_nav
	 */
	public function test_link_should_not_trailingslash_link_explicit_link() {
		$link = 'http://example.com/foo/bar/blah/?action=edit&id=30';

		bp_core_new_nav_item( array(
			'name' => 'Foo',
			'slug' => 'foo',
			'screen_function' => 'foo',
			'link' => 'http://example.com/foo/',
		) );

		bp_core_new_subnav_item( array(
			'name' => 'bar',
			'slug' => 'bar',
			'parent_slug' => 'foo',
			'parent_url' => 'http://example.com/foo/',
			'screen_function' => 'foo',
			'link' => $link,
		) );

		$this->assertSame( $link, buddypress()->bp_options_nav['foo']['bar']['link'] );
	}

	public function test_should_return_false_if_site_admin_only_and_current_user_cannot_bp_moderate() {
		bp_core_new_nav_item( array(
			'name' => 'Foo',
			'slug' => 'foo',
			'screen_function' => 'foo',
		) );

		// Should already be set to a 0 user.
		$this->assertFalse( bp_current_user_can( 'bp_moderate' ) );
		$args = array(
			'name' => 'Foo',
			'slug' => 'foo',
			'parent_slug' => 'parent',
			'parent_url' => bp_get_root_url() . '/parent/',
			'screen_function' => 'foo',
			'site_admin_only' => true,
		);

		$this->assertFalse( bp_core_new_subnav_item( $args ) );
	}

	/**
	 * @expectedIncorrectUsage bp_nav
	 */
	public function test_css_id_should_fall_back_on_slug() {
		bp_core_new_nav_item( array(
			'name' => 'Parent',
			'slug' => 'parent',
			'screen_function' => 'foo',
		) );

		$args = array(
			'name' => 'Foo',
			'slug' => 'foo',
			'parent_slug' => 'parent',
			'parent_url' => bp_get_root_url() . '/parent/',
			'screen_function' => 'foo',
		);
		bp_core_new_subnav_item( $args );

		$this->assertSame( 'foo', buddypress()->bp_options_nav['parent']['foo']['css_id'] );
	}

	/**
	 * @expectedIncorrectUsage bp_nav
	 */
	public function test_css_id_should_be_respected() {
		bp_core_new_nav_item( array(
			'name' => 'Parent',
			'slug' => 'parent',
			'screen_function' => 'foo',
		) );

		$args = array(
			'name' => 'Foo',
			'slug' => 'foo',
			'parent_slug' => 'parent',
			'parent_url' => bp_get_root_url() . '/parent/',
			'screen_function' => 'foo',
			'item_css_id' => 'bar',
		);
		bp_core_new_subnav_item( $args );

		$this->assertSame( 'bar', buddypress()->bp_options_nav['parent']['foo']['css_id'] );
	}

	public function screen_callback() {
		bp_core_load_template( 'members/single/plugins' );
	}

	public function new_nav_hook() {
		bp_core_new_subnav_item(
			array(
				'name'            => 'Testing',
				'slug'            => 'testing',
				'parent_slug'     => bp_get_profile_slug(),
				'screen_function' => array( $this, 'screen_callback' ),
				'position'        => 20
			)
		);
	}

	/**
	 * @ticket BP7931
	 */
	public function test_subnav_should_not_404_on_early_bp_setup_nav_priority() {
		// Register a subnav on 'bp_setup_nav' hook early (at priority zero).
		add_action( 'bp_setup_nav', array( $this, 'screen_callback' ), 0 );

		$u = self::factory()->user->create();
		$old_current_user = get_current_user_id();
		$this->set_current_user( $u );

		$url = bp_members_get_user_url(
			$u,
			array(
				'single_item_component' => bp_get_profile_slug(),
				'single_item_action'    => 'testing',
			)
		);

		// Emulate visit to our new subnav page.
		$this->go_to( $url );

		// Assert that subnav page does not 404.
		$this->assertFalse( is_404() );

		remove_action( 'bp_setup_nav', array( $this, 'screen_callback' ), 0 );

		$this->set_current_user( $old_current_user );
	}
}
