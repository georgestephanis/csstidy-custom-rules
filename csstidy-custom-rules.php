<?php

/*
 * Plugin Name: CSSTidy Custom Rules
 * Plugin URI: http://github.com/georgestephanis/csstidy-custom-rules
 * Description: Add custom CSSTidy rules for Jetpack's Custom CSS module
 * Author: George Stephanis
 * Version: 0.1
 * Author URI: http://stephanis.info/
 */

class CSSTidy_Custom_Rules {

	public static function go() {
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_post_save_csstidy_custom_rules', array( __CLASS__, 'save_csstidy_custom_rules' ) );
	}

	public static function admin_init() {
		$rules = get_option( 'csstidy-custom-rules', array() );
		foreach ( $rules as $rule ) {
			$GLOBALS['csstidy']['all_properties'][ $rule['rule'] ] = implode( ',', $rule['versions'] );
		}
	}

	public static function admin_menu() {
		add_management_page(
			__( 'CSSTidy Custom Rules', 'csstidy-custom-rules' ),
			__( 'CSSTidy Rules', 'csstidy-custom-rules' ),
			'manage_options',
			'csstidy-custom-rules',
			array( __CLASS__, 'admin_page' )
		);
	}

	public static function admin_page() {
		$rules = get_option( 'csstidy-custom-rules', array() );
		wp_enqueue_style( 'csstidy-custom-rules', plugins_url( 'csstidy-custom-rules.css', __FILE__ ) );
		wp_enqueue_script( 'csstidy-custom-rules', plugins_url( 'csstidy-custom-rules.js', __FILE__ ), array( 'wp-util', 'jquery' ) );
		wp_localize_script( 'csstidy-custom-rules', 'csstidyCustomRules', array(
			'rules' => $rules,
		))
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'CSSTidy Custom Rules', 'csstidy-custom-rules' ); ?></h1>
			<a href="#add-new" class="page-title-action add-new-rule"><?php esc_html_e( 'Add New', 'csstidy-custom-rules' ); ?></a>
			<hr class="wp-header-end">

			<form action="admin-post.php" method="post">
				<input type="hidden" name="action" value="save_csstidy_custom_rules" />
				<?php wp_nonce_field( 'csstidy-custom-rules', 'csstidy-nonce' ); ?>
				<ul id="csstidy-custom-rules">
					<li><?php esc_html_e( 'Loading&hellip;', 'csstidy-custom-rules' ); ?></li>
				</ul>
				<?php submit_button( __( 'Add New', 'csstidy-custom-rules' ), 'add-new-rule', '', false ); ?>
				<?php submit_button( __( 'Save', 'csstidy-custom-rules' ) ); ?>
			</form>

			<br class="clear">
		</div>
		<script type="text/html" id="tmpl-csstidy-rule-field">
			<li class="widefat">
				<div>
					<label>
						<?php esc_html_e( 'CSS Property:', 'csstidy-custom-rules' ); ?>
						<input type="text" class="widefat" name="rules[{{ data.name }}][rule]" value="{{ data.rule }}" required pattern="[a-z-]+" title="<?php esc_attr_e( 'Lower-case letters and dashes only.', 'csstidy-custom-rules' ); ?>"/>
					</label>
					<small class="row-actions">
						<span class="trash"><a href="#delete"><?php esc_html_e( 'Delete', 'csstidy-custom-rules' ); ?></a></span>
					</small>
				</div>
				<ul>
					<li><label><input type="checkbox" name="rules[{{ data.name }}][versions][]" value="CSS1.0" <# if ( -1 !== data.versions.indexOf('CSS1.0') ) print( "checked='checked'" ) #> /> CSS1.0</label></li>
					<li><label><input type="checkbox" name="rules[{{ data.name }}][versions][]" value="CSS2.0" <# if ( -1 !== data.versions.indexOf('CSS2.0') ) print( "checked='checked'" ) #> /> CSS2.0</label></li>
					<li><label><input type="checkbox" name="rules[{{ data.name }}][versions][]" value="CSS2.1" <# if ( -1 !== data.versions.indexOf('CSS2.1') ) print( "checked='checked'" ) #> /> CSS2.1</label></li>
					<li><label><input type="checkbox" name="rules[{{ data.name }}][versions][]" value="CSS3.0" <# if ( -1 !== data.versions.indexOf('CSS3.0') ) print( "checked='checked'" ) #> /> CSS3.0</label></li>
				</ul>
			</li>
		</script>
		<?php
	}

	public static function save_csstidy_custom_rules() {
		check_admin_referer( 'csstidy-custom-rules', 'csstidy-nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			die( '👋' );
		}
		$data = $_POST['rules'];

		$data = array_values( $data );
		$names = wp_list_pluck( $data, 'rule' );
		$data = array_combine( $names, $data );

		foreach ( $data as $key => $rule ) {
			// If there's any unrecognized versions, die.
			if ( array_diff( $rule['versions'], array( 'CSS1.0', 'CSS2.0', 'CSS2.1', 'CSS3.0' ) ) ) {
				die( '😖' );
			}
			// If the property is anything other than dashes or letters, die.
			if ( ! preg_match( '/^[a-z\-]+$/', trim( $rule['rule'] ) ) ) {
				die( '🤢' );
			}
			// If there's any unrecognized properties, die.
			if ( array_diff_key( $rule, array( 'rule' => '', 'versions' => '' ) ) ) {
				die( '🙃' );
			}
		}

		update_option( 'csstidy-custom-rules', $data, true );

		wp_safe_redirect( 'tools.php?page=csstidy-custom-rules' );
		exit;
	}

}
CSSTidy_Custom_Rules::go();
