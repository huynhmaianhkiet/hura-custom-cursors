<?php
/*
  Plugin Name: Hura Custom Cursors
  Version: 1.0
  Description: Customizing your website cursor.
  Author: Hura Apps
  Author URI: https://www.huraapps.com
*/
	
class Hura_Apps_Custom_Cursors {	

	public $plugin_title = 'Hura Custom Cursors';
	public $plugin_description = 'This plugin to customize your website cursor, you will get a very elegant and unique site.';	
	
	function __construct() {
		add_action( 'admin_menu', array(&$this, 'add_menu_item'));
		add_action('wp_footer', array(&$this, 'add_huraapps_custom_cursors_script'));
		register_setting( 'hura-apps-custom-cursors-plugin-settings', 'huraapps_customer_cursor' );
	}
	function settings_page()
	{	
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
								<p></p>
								<p>Hura Apps is a Vietnam-based Web & Mobile App development team. You can contact us via:</p>
								<ul>
									<li>Email: <a href="mailto:info@huraapps.com">Info@huraapps.com</a></li>
									<li>Facebook: <a href="//www.facebook.com/huraapps" target="_blank">Huraapps</a></li>
									<li>Website: <a href="//www.huraapps.com" target="_blank">wWw.HuraApps.Com</a></li>
								</ul>
								<p></p>
							</div>
						</div>
					</div>
				</div>
				<div class="has-sidebar sm-padded">
					<div id="post-body-content" class="has-sidebar-content">
						<div class="meta-box-sortabless">
							<div class="postbox">								
								<div class="inside">									
									<?php
										if(current_user_can('administrator')){	
											$config_cursor = esc_attr(get_option('huraapps_customer_cursor'));
									?>
									<div class="main-section">
										<h3 class="hndle2">Settings</h3>
										<p>Choose one of the below custom cursors</p>
										<form action="options.php" method="post">
											<?php
												settings_fields( 'hura-apps-custom-cursors-plugin-settings' );
												do_settings_sections( 'hura-apps-custom-cursors-plugin-settings' );
											?>
											<div class="hura-flex hura-cursors" style="margin-top:25px;">									
												<div>
													<label>
														Default
														<input type="radio" name="huraapps_customer_cursor" value="" <?php echo esc_attr(($config_cursor=='')?'checked':''); ?>>
													</label>
												</div>	
												<?php for($i=1;$i<=3;$i++){?>												
												<div>
													<label>
														<img src="<?php echo esc_url(plugin_dir_url( __FILE__ ).'images/cursors/cursor-'.$i.'.png'); ?>">
														<input type="radio" name="huraapps_customer_cursor" value="cursor-<?php echo esc_attr($i); ?>" <?php echo esc_attr(($config_cursor=='cursor-'.$i)?'checked':''); ?>>
													</label>
												</div>
												<?php } ?>								
												<?php for($i=1;$i<=22;$i++){?>												
												<div>
													<label>
														<img src="<?php echo esc_url(plugin_dir_url( __FILE__ ).'images/cursors/flag_'.$i.'.png'); ?>">
														<input type="radio" name="huraapps_customer_cursor" value="flag_<?php echo esc_attr($i); ?>" <?php echo esc_attr(($config_cursor=='flag_'.$i)?'checked':''); ?>>
													</label>
												</div>
												<?php } ?>
											</div>											
											<div class="hura-btn-wrap"><?php submit_button(); ?></div>										
										</form>										
										<hr>																				
										<p>If you found any issue, please let us know by send email to us at <a href="mailto:info@huraapps.com">info@huraapps.com</a>.</p>
									</div>
									<script>
										<?php if( $config_cursor!='' ){ ?>
										document.getElementsByTagName("body")[0].style.cursor = "url('<?php echo esc_url(plugin_dir_url( __FILE__ ).'images/cursors/'.$config_cursor.'.png'); ?>'), auto";
										<?php } ?>
										jQuery(document).ready(function($){					
											$('input[name=huraapps_customer_cursor]').change(function(){
												var cursor_name = $(this).val();
												if( cursor_name != '' ){
													document.getElementsByTagName("body")[0].style.cursor = "url('<?php echo esc_url(plugin_dir_url( __FILE__ ).'images/cursors/'); ?>"+cursor_name+".png'), auto";
												}else{
													document.getElementsByTagName("body")[0].style.cursor = "default";
												}
											});
											
										});
									</script>
									<?php
										}else{
											echo "<p style='text-align:center;'>You don't have permission to access</p>";
										}
									?>
								</div>
								<div class="clear"></div>
							</div>							
							<div class="postbox">
								<div class="inside">
									<p style="text-align:center;">Copyright &copy; <?php echo date("Y"); ?> by <a href="//www.huraapps.com" target="_blank">Hura Apps</a>. All rights reserved.<br>Developed and Designed by <a href="//anhkiet.biz" target="_blank">Kiet Huynh</a>.</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
<?php
	}
	function add_huraapps_custom_cursors_script() {
			$config_cursor = esc_attr(get_option('huraapps_customer_cursor'));
			if( $config_cursor!='' ){
				echo '<style>body{cursor: url("'.esc_url(plugin_dir_url( __FILE__ )).'images/cursors/'.$config_cursor.'.png"), auto!important;}</style>';				
			}
	}
	function add_menu_item()
	{
		add_submenu_page('themes.php','Hura Custom Cursors Panel','Custom Cursors','manage_options','hura-apps-custom-cursors-panel',array(&$this,"settings_page"));
	}
}
$Hura_Apps_Custom_Cursors = new Hura_Apps_Custom_Cursors();
?>