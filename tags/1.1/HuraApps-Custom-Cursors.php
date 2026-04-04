<?php
/*
  Plugin Name: Hura Custom Cursors
  Version: 1.1
  Description: Customizing your website cursor.
  Author: Hura Apps
  Author URI: https://www.huraapps.com
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
	
class Hura_Apps_Custom_Cursors {	

	const VERSION        = '1.1';
	const OPTION_NAME    = 'huraapps_customer_cursor';
	const SETTINGS_GROUP = 'hura-apps-custom-cursors-plugin-settings';
	const MENU_SLUG      = 'hura-apps-custom-cursors-panel';
	const CURSOR_COUNT   = 35;
	const FLAG_COUNT     = 40;

	public $plugin_title       = 'Hura Custom Cursors';
	public $plugin_description = 'This plugin to customize your website cursor, you will get a very elegant and unique site.';

	private $plugin_url;

	function __construct() {
		$this->plugin_url = plugin_dir_url( __FILE__ );

		add_action( 'admin_menu',        array( $this, 'add_menu_item' ) );
		add_action( 'admin_init',        array( $this, 'register_settings' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );
	}

	function register_settings() {
		register_setting(
			self::SETTINGS_GROUP,
			self::OPTION_NAME,
			array( $this, 'sanitize_cursor_option' )
		);
	}

	function get_allowed_cursors() {
		static $allowed = null;

		if ( null !== $allowed ) {
			return $allowed;
		}

		$allowed = array( '' );

		for ( $i = 1; $i <= self::CURSOR_COUNT; $i++ ) {
			$allowed[] = 'cursor-' . $i;
		}

		for ( $i = 1; $i <= self::FLAG_COUNT; $i++ ) {
			$allowed[] = 'flag_' . $i;
		}

		return $allowed;
	}

	function sanitize_cursor_option( $value ) {
		$value = sanitize_text_field( wp_unslash( $value ) );

		if ( in_array( $value, $this->get_allowed_cursors(), true ) ) {
			return $value;
		}

		return '';
	}

	function get_saved_cursor() {
		$value = get_option( self::OPTION_NAME, '' );

		if ( in_array( $value, $this->get_allowed_cursors(), true ) ) {
			return $value;
		}

		return '';
	}

	function get_cursor_image_url( $cursor_name ) {
		return $this->plugin_url . 'images/cursors/' . $cursor_name . '.png';
	}

	function settings_page()
	{	
		if ( ! current_user_can( 'manage_options' ) ) {
			echo '<p style="text-align:center;">You do not have permission to access this page.</p>';
			return;
		}

		$config_cursor = $this->get_saved_cursor();
		?>
			<style>
				h3.hndle2{border-bottom: 1px solid #eee;}
				.hura-flex{display:flex;flex-wrap:wrap;}
				.hura-flex div{text-align:center;width:10%;margin-bottom:20px;}
				.hura-flex div label{cursor:pointer;}
				.hura-flex div label input{display:block;margin: 0 auto;}
				.hura-btn-wrap p.submit{text-align:center;}
			</style>

			<h1><?php echo esc_html($this->plugin_title); ?></h1>				

			<div id="poststuff" class="hura-admin-wrapper metabox-holder has-right-sidebar">
				<div class="inner-sidebar">
					<div id="side-sortables" class="meta-box-sortabless ui-sortable">
						<div class="postbox ">
							<h3 class="hndle2"><span>About <?php echo esc_html($this->plugin_title); ?></span></h3>
							<div class="inside">
								<p><?php echo esc_html($this->plugin_description); ?></p>															
							</div>
						</div>

						<div class="postbox ">
							<h3 class="hndle2"><span>About Us</span></h3>
							<div class="inside">
								<p>Hura Apps is a web development team based in Vietnam. You can contact us at:</p>
								<ul>
									<li>Email: <a href="mailto:support@huraapps.com">support@huraapps.com</a></li>
									<li>LinkedIn: <a href="//www.linkedin.com/company/huraapps" target="_blank" rel="noopener noreferrer">huraapps</a></li>
									<li>Website: <a href="//www.huraapps.com" target="_blank" rel="noopener noreferrer">www.huraapps.com</a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="has-sidebar sm-padded">
					<div id="post-body-content" class="has-sidebar-content">
						<div class="meta-box-sortabless">
							<div class="postbox">								
								<div class="inside">									
									<div class="main-section">
										<h3 class="hndle2">Settings</h3>
										<p>Choose one of the below custom cursors</p>
										<form action="options.php" method="post">
											<?php
												settings_fields( self::SETTINGS_GROUP );
												do_settings_sections( self::SETTINGS_GROUP );
											?>
											<div class="hura-flex hura-cursors" style="margin-top:25px;">									
												<div>
													<label>
														Default
														<input type="radio" name="<?php echo esc_attr( self::OPTION_NAME ); ?>" value="" <?php checked( $config_cursor, '' ); ?>>
													</label>
												</div>	
												<?php for ( $i = 1; $i <= self::CURSOR_COUNT; $i++ ) { ?>												
												<div>
													<label>
														<img src="<?php echo esc_url( $this->get_cursor_image_url( 'cursor-' . $i ) ); ?>" alt="<?php echo esc_attr( 'cursor-' . $i ); ?>">
														<input type="radio" name="<?php echo esc_attr( self::OPTION_NAME ); ?>" value="cursor-<?php echo esc_attr( $i ); ?>" <?php checked( $config_cursor, 'cursor-' . $i ); ?>>
													</label>
												</div>
												<?php } ?>								
												<?php for ( $i = 1; $i <= self::FLAG_COUNT; $i++ ) { ?>												
												<div>
													<label>
														<img src="<?php echo esc_url( $this->get_cursor_image_url( 'flag_' . $i ) ); ?>" alt="<?php echo esc_attr( 'flag_' . $i ); ?>">
														<input type="radio" name="<?php echo esc_attr( self::OPTION_NAME ); ?>" value="flag_<?php echo esc_attr( $i ); ?>" <?php checked( $config_cursor, 'flag_' . $i ); ?>>
													</label>
												</div>
												<?php } ?>
											</div>											
											<div class="hura-btn-wrap"><?php submit_button(); ?></div>										
										</form>										
										<hr>																				
										<p>If you found any issue, please let us know by send email to us at <a href="mailto:support@huraapps.com">support@huraapps.com</a>.</p>
									</div>
									<script>
								(function() {
									<?php if ( '' !== $config_cursor ) : ?>
									document.body.style.cursor = "url('<?php echo esc_url( $this->get_cursor_image_url( $config_cursor ) ); ?>'), auto";
									<?php endif; ?>
									var baseUrl = '<?php echo esc_url( $this->plugin_url . 'images/cursors/' ); ?>';
									document.querySelectorAll('input[name="<?php echo esc_js( self::OPTION_NAME ); ?>"]').forEach(function(radio) {
										radio.addEventListener('change', function() {
											var cursor_name = this.value;
											document.body.style.cursor = cursor_name
												? "url('" + baseUrl + cursor_name + ".png'), auto"
												: 'default';
										});
									});
								})();
									</script>
								</div>
								<div class="clear"></div>
							</div>							
							<div class="postbox">
								<div class="inside">
									<p style="text-align:center;">Copyright &copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> by <a href="//www.huraapps.com" target="_blank" rel="noopener noreferrer">Hura Apps</a>. All rights reserved.<br>Developed and Designed by <a href="//anhkiet.biz" target="_blank" rel="noopener noreferrer">Kiet Huynh</a>.</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
<?php
	}
	function enqueue_frontend_styles() {
		$cursor = $this->get_saved_cursor();

		if ( '' === $cursor ) {
			return;
		}

		wp_register_style( 'hura-custom-cursor', false, array(), self::VERSION );
		wp_enqueue_style( 'hura-custom-cursor' );
		wp_add_inline_style( 'hura-custom-cursor', 'body{cursor:url("' . esc_url( $this->get_cursor_image_url( $cursor ) ) . '"),auto!important;}' );
	}
	function add_menu_item()
	{
		add_submenu_page(
			'themes.php',
			'Hura Custom Cursors Panel',
			'Custom Cursors',
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'settings_page' )
		);
	}
}
$Hura_Apps_Custom_Cursors = new Hura_Apps_Custom_Cursors();
?>