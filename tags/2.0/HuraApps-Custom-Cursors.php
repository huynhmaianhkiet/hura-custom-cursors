<?php
/*
  Plugin Name: Hura Custom Cursors
  Version: 2.0
  Description: Customizing your website cursor.
  Author: Hura Apps
  Author URI: https://www.huraapps.com
*/

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

class Hura_Apps_Custom_Cursors {

const VERSION        = '2.0';
const OPTION_NAME    = 'huraapps_customer_cursor';
const SETTINGS_GROUP = 'hura-apps-custom-cursors-plugin-settings';
const MENU_SLUG      = 'hura-apps-custom-cursors-panel';
const CUSTOM_PREFIX  = 'custom:';
const CURSOR_COUNT   = 35;
const FLAG_COUNT     = 40;

public $plugin_title       = 'Hura Custom Cursors';
public $plugin_description = 'This plugin to customize your website cursor, you will get a very elegant and unique site.';

private $plugin_url;

function __construct() {
$this->plugin_url = plugin_dir_url( __FILE__ );

add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
add_action( 'admin_init', array( $this, 'register_settings' ) );
add_action( 'admin_post_hura_upload_custom_cursor', array( $this, 'handle_custom_cursor_upload' ) );
add_action( 'admin_post_hura_delete_custom_cursor', array( $this, 'handle_custom_cursor_delete' ) );
add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );
}

function register_settings() {
register_setting(
self::SETTINGS_GROUP,
self::OPTION_NAME,
array( $this, 'sanitize_cursor_option' )
);
}

function get_custom_cursor_dir_path() {
return plugin_dir_path( __FILE__ ) . 'images/custom-cursors/';
}

function get_custom_cursor_dir_url() {
return $this->plugin_url . 'images/custom-cursors/';
}

function ensure_custom_cursor_dir() {
$dir = $this->get_custom_cursor_dir_path();

if ( ! file_exists( $dir ) ) {
wp_mkdir_p( $dir );
}

if ( file_exists( $dir ) ) {
$index_file = $dir . 'index.html';
if ( ! file_exists( $index_file ) ) {
file_put_contents( $index_file, "<html><body></body></html>\n" );
}
}
}

function get_custom_cursors() {
$this->ensure_custom_cursor_dir();

$files = glob( $this->get_custom_cursor_dir_path() . '*.{png,gif,cur,ico,webp}', GLOB_BRACE );
if ( ! is_array( $files ) ) {
return array();
}

natcasesort( $files );

$cursors = array();
foreach ( $files as $file_path ) {
$file_name = basename( $file_path );
$cursors[] = array(
'id'   => self::CUSTOM_PREFIX . $file_name,
'file' => $file_name,
'url'  => $this->get_custom_cursor_dir_url() . $file_name,
);
}

return $cursors;
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

$custom_cursors = $this->get_custom_cursors();
foreach ( $custom_cursors as $custom_cursor ) {
$allowed[] = $custom_cursor['id'];
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

function get_active_cursor_url() {
$cursor = $this->get_saved_cursor();

if ( '' === $cursor ) {
return '';
}

if ( 0 === strpos( $cursor, self::CUSTOM_PREFIX ) ) {
$file = sanitize_file_name( substr( $cursor, strlen( self::CUSTOM_PREFIX ) ) );
if ( '' === $file ) {
return '';
}

$path = $this->get_custom_cursor_dir_path() . $file;
if ( file_exists( $path ) ) {
return $this->get_custom_cursor_dir_url() . $file;
}

return '';
}

return $this->get_cursor_image_url( $cursor );
}

function redirect_to_settings( $notice = '' ) {
$url = add_query_arg(
array(
'page' => self::MENU_SLUG,
),
admin_url( 'themes.php' )
);

if ( '' !== $notice ) {
$url = add_query_arg( 'hura_cursor_notice', rawurlencode( $notice ), $url );
}

wp_safe_redirect( $url );
exit;
}

function handle_custom_cursor_upload() {
if ( ! current_user_can( 'manage_options' ) ) {
wp_die( esc_html__( 'You do not have permission to upload cursors.', 'hura-custom-cursors' ) );
}

check_admin_referer( 'hura_upload_custom_cursor' );

if ( empty( $_FILES['hura_custom_cursor_file']['name'] ) ) {
$this->redirect_to_settings( 'upload-empty' );
}

require_once ABSPATH . 'wp-admin/includes/file.php';

$uploaded = wp_handle_upload(
$_FILES['hura_custom_cursor_file'],
array(
'test_form' => false,
'mimes'     => array(
'png'  => 'image/png',
'gif'  => 'image/gif',
'webp' => 'image/webp',
'ico'  => 'image/x-icon',
'cur'  => 'application/octet-stream',
),
)
);

if ( isset( $uploaded['error'] ) ) {
$this->redirect_to_settings( 'upload-failed' );
}

$this->ensure_custom_cursor_dir();

$destination_dir = $this->get_custom_cursor_dir_path();
if ( ! file_exists( $destination_dir ) ) {
@unlink( $uploaded['file'] );
$this->redirect_to_settings( 'upload-failed' );
}

$source_path = $uploaded['file'];
$target_name = wp_unique_filename( $destination_dir, sanitize_file_name( basename( $source_path ) ) );
$target_path = $destination_dir . $target_name;

if ( ! @copy( $source_path, $target_path ) ) {
@unlink( $source_path );
$this->redirect_to_settings( 'upload-failed' );
}

@unlink( $source_path );
$this->redirect_to_settings( 'upload-success' );
}

function handle_custom_cursor_delete() {
if ( ! current_user_can( 'manage_options' ) ) {
wp_die( esc_html__( 'You do not have permission to delete cursors.', 'hura-custom-cursors' ) );
}

$cursor_file = isset( $_POST['cursor_file'] ) ? sanitize_file_name( wp_unslash( $_POST['cursor_file'] ) ) : '';
if ( '' === $cursor_file ) {
$this->redirect_to_settings( 'delete-failed' );
}

check_admin_referer( 'hura_delete_custom_cursor_' . $cursor_file );

if ( self::CUSTOM_PREFIX . $cursor_file === $this->get_saved_cursor() ) {
$this->redirect_to_settings( 'delete-active' );
}

$file_path = $this->get_custom_cursor_dir_path() . $cursor_file;
if ( ! file_exists( $file_path ) || ! is_file( $file_path ) ) {
$this->redirect_to_settings( 'delete-failed' );
}

if ( ! @unlink( $file_path ) ) {
$this->redirect_to_settings( 'delete-failed' );
}

$this->redirect_to_settings( 'delete-success' );
}

function render_notice() {
if ( empty( $_GET['hura_cursor_notice'] ) ) {
return;
}

$notice = sanitize_key( wp_unslash( $_GET['hura_cursor_notice'] ) );
$messages = array(
'upload-success' => array( 'success', 'Cursor uploaded successfully.' ),
'upload-empty'   => array( 'error', 'Please choose a cursor file before uploading.' ),
'upload-failed'  => array( 'error', 'Upload failed. Please try again with a valid cursor file.' ),
'delete-success' => array( 'success', 'Cursor deleted successfully.' ),
'delete-active'  => array( 'error', 'You cannot delete the cursor currently in use.' ),
'delete-failed'  => array( 'error', 'Could not delete this cursor file.' ),
);

if ( ! isset( $messages[ $notice ] ) ) {
return;
}

list( $class, $message ) = $messages[ $notice ];
echo '<div class="notice notice-' . esc_attr( $class ) . ' is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
}

function settings_page() {
if ( ! current_user_can( 'manage_options' ) ) {
echo '<p style="text-align:center;">You do not have permission to access this page.</p>';
return;
}

$config_cursor  = $this->get_saved_cursor();
$preview_cursor = $this->get_active_cursor_url();
$custom_cursors = $this->get_custom_cursors();
?>
<?php $this->render_notice(); ?>
<style>
h3.hndle2{border-bottom:1px solid #eee;}
.hura-flex{display:flex;flex-wrap:wrap;}
.hura-flex div{text-align:center;width:10%;margin-bottom:20px;}
.hura-flex div label{cursor:pointer;}
.hura-flex div label input{display:block;margin:0 auto;}
.hura-btn-wrap p.submit{text-align:center;}
.hura-upload-block p{margin:0 0 10px;}
.hura-upload-dropzone{border:2px dashed #8c8f94;border-radius:8px;background:#f6f7f7;padding:18px 14px;text-align:center;transition:all .2s ease;cursor:pointer;}
.hura-upload-dropzone.is-dragover{border-color:#2271b1;background:#eef6fc;}
.hura-upload-dropzone p{margin:0 0 8px;}
.hura-upload-dropzone .description{margin:0;color:#50575e;}
.hura-upload-dropzone .dashicons{font-size:28px;width:28px;height:28px;color:#2271b1;margin-bottom:8px;}
.hura-upload-dropzone .hura-upload-main{font-size:13px;line-height:1.5;color:#1d2327;}
.hura-upload-dropzone .hura-upload-sub{margin:6px 0 0;color:#50575e;}
.hura-upload-file{display:none;}
.hura-upload-filename{margin-top:10px;font-size:12px;color:#50575e;min-height:18px;}
.hura-upload-filename.is-error{color:#b32d2e;font-weight:600;}
.hura-upload-actions{margin-top:10px;display:flex;gap:8px;align-items:center;}
.hura-upload-error{margin-top:10px;display:none;}
.hura-upload-error.is-visible{display:block;}
.hura-library-title{font-size:14px;font-weight:600;margin:15px 0 12px;}
.hura-custom-item-actions{margin-top:6px;}
.hura-custom-item-actions button{padding:0;border:0;background:none;color:#a00;cursor:pointer;text-decoration:underline;}
</style>

<h1><?php echo esc_html( $this->plugin_title ); ?></h1>

<div id="poststuff" class="hura-admin-wrapper metabox-holder has-right-sidebar">
<div class="inner-sidebar">
<div id="side-sortables" class="meta-box-sortabless ui-sortable">
<div class="postbox ">
<h3 class="hndle2"><span>About <?php echo esc_html( $this->plugin_title ); ?></span></h3>
<div class="inside">
<p><?php echo esc_html( $this->plugin_description ); ?></p>
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

<div class="postbox hura-upload-block">
<h3 class="hndle2"><span>Upload Your Own Cursor</span></h3>
<div class="inside">
<p>Upload a cursor image from your device. You can click to choose file or drag and drop into the field below.</p>
<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" enctype="multipart/form-data">
<?php wp_nonce_field( 'hura_upload_custom_cursor' ); ?>
<input type="hidden" name="action" value="hura_upload_custom_cursor">
<input class="hura-upload-file" id="hura-custom-cursor-file" type="file" name="hura_custom_cursor_file" accept=".png,.gif,.cur,.ico,.webp,image/png,image/gif,image/webp,image/x-icon">
<div id="hura-upload-dropzone" class="hura-upload-dropzone" tabindex="0" role="button" aria-label="Upload cursor file">
<span class="dashicons dashicons-upload" aria-hidden="true"></span>
<p class="hura-upload-main"><strong>Drop cursor file here</strong> or click the button below</p>
<p class="hura-upload-sub">Supported: PNG, GIF, CUR, ICO, WEBP</p>
</div>
<div id="hura-upload-filename" class="hura-upload-filename">No file selected.</div>
<div id="hura-upload-error" class="notice notice-error inline hura-upload-error"><p>Invalid file type. Please select PNG, GIF, CUR, ICO, or WEBP.</p></div>
<div class="hura-upload-actions">
<button type="button" id="hura-choose-file" class="button">Choose File</button>
<button type="submit" id="hura-upload-submit" class="button button-primary">Upload Cursor</button>
</div>
</form>
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
<p>Choose one of the cursors below.</p>
<form id="hura-settings-form" action="options.php" method="post">
<?php
settings_fields( self::SETTINGS_GROUP );
do_settings_sections( self::SETTINGS_GROUP );
?>
<p class="hura-library-title">Built-in Cursor Library</p>
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

<p class="hura-library-title">Your Uploaded Cursors</p>
<div class="hura-flex hura-cursors">
<?php if ( empty( $custom_cursors ) ) { ?>
<div style="width:100%;text-align:left;">No custom cursor uploaded yet.</div>
<?php } else { ?>
<?php foreach ( $custom_cursors as $ci => $custom_cursor ) { ?>
<div>
<label>
<img src="<?php echo esc_url( $custom_cursor['url'] ); ?>" alt="<?php echo esc_attr( $custom_cursor['file'] ); ?>">
<input type="radio" name="<?php echo esc_attr( self::OPTION_NAME ); ?>" value="<?php echo esc_attr( $custom_cursor['id'] ); ?>" <?php checked( $config_cursor, $custom_cursor['id'] ); ?>>
</label>
<div class="hura-custom-item-actions">
<?php if ( $config_cursor === $custom_cursor['id'] ) { ?>
<span>In use</span>
<?php } else { ?>
<button type="submit" form="<?php echo esc_attr( 'hura-delete-form-' . $ci ); ?>" onclick="return confirm('Delete this custom cursor?');">Delete</button>
<?php } ?>
</div>
</div>
<?php } ?>
<?php } ?>
</div>
<div class="hura-btn-wrap"><?php submit_button(); ?></div>
</form>
<?php foreach ( $custom_cursors as $ci => $custom_cursor ) { ?>
<form id="<?php echo esc_attr( 'hura-delete-form-' . $ci ); ?>" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
<?php wp_nonce_field( 'hura_delete_custom_cursor_' . $custom_cursor['file'] ); ?>
<input type="hidden" name="action" value="hura_delete_custom_cursor">
<input type="hidden" name="cursor_file" value="<?php echo esc_attr( $custom_cursor['file'] ); ?>">
</form>
<?php } ?>
<hr>
<p>If you found any issue, please let us know by send email to us at <a href="mailto:support@huraapps.com">support@huraapps.com</a>.</p>
</div>
<script>
(function() {
<?php if ( '' !== $preview_cursor ) : ?>
document.body.style.cursor = "url('<?php echo esc_url( $preview_cursor ); ?>'), auto";
<?php endif; ?>
var baseUrl = '<?php echo esc_url( $this->plugin_url . 'images/cursors/' ); ?>';
var customPrefix = '<?php echo esc_js( self::CUSTOM_PREFIX ); ?>';
var customBaseUrl = '<?php echo esc_url( $this->get_custom_cursor_dir_url() ); ?>';

function applyCursorPreview(cursorName) {
if (!cursorName) {
document.body.style.cursor = 'default';
return;
}

if (cursorName.indexOf(customPrefix) === 0) {
var fileName = cursorName.substring(customPrefix.length);
document.body.style.cursor = "url('" + customBaseUrl + fileName + "'), auto";
return;
}

document.body.style.cursor = "url('" + baseUrl + cursorName + ".png'), auto";
}

document.querySelectorAll('input[name="<?php echo esc_js( self::OPTION_NAME ); ?>"]').forEach(function(radio) {
radio.addEventListener('change', function() {
applyCursorPreview(this.value);
});
});

var uploadDropzone = document.getElementById('hura-upload-dropzone');
var uploadFileInput = document.getElementById('hura-custom-cursor-file');
var uploadFileName = document.getElementById('hura-upload-filename');
var chooseFileButton = document.getElementById('hura-choose-file');
var uploadSubmitButton = document.getElementById('hura-upload-submit');
var uploadError = document.getElementById('hura-upload-error');
var allowedExtensions = ['png', 'gif', 'cur', 'ico', 'webp'];

function resetUploadError() {
if (uploadError) {
uploadError.classList.remove('is-visible');
}

if (uploadFileName) {
uploadFileName.classList.remove('is-error');
}

if (uploadSubmitButton) {
uploadSubmitButton.disabled = false;
}
}

function showUploadError(message) {
if (uploadError) {
uploadError.classList.add('is-visible');
uploadError.innerHTML = '<p>' + message + '</p>';
}

if (uploadFileName) {
uploadFileName.classList.add('is-error');
}

if (uploadSubmitButton) {
uploadSubmitButton.disabled = true;
}
}

function isAllowedFile(fileName) {
var parts = fileName.toLowerCase().split('.');
if (parts.length < 2) {
return false;
}

return allowedExtensions.indexOf(parts.pop()) !== -1;
}

function setSingleFile(file) {
if (!uploadFileInput || !file) {
return;
}

var dt = new DataTransfer();
dt.items.add(file);
uploadFileInput.files = dt.files;
}

function validateAndRenderSelectedFile(file) {
if (!file) {
resetUploadError();
if (uploadFileName) {
uploadFileName.textContent = 'No file selected.';
}
return;
}

if (!isAllowedFile(file.name)) {
if (uploadFileName) {
uploadFileName.textContent = 'Selected: ' + file.name;
}
showUploadError('Invalid file type: ' + file.name + '. Please select PNG, GIF, CUR, ICO, or WEBP.');
return;
}

resetUploadError();
if (uploadFileName) {
uploadFileName.textContent = 'Selected: ' + file.name;
}
}

function updateUploadFileName() {
if (!uploadFileInput) {
return;
}

validateAndRenderSelectedFile(uploadFileInput.files && uploadFileInput.files.length > 0 ? uploadFileInput.files[0] : null);
}

if (uploadDropzone && uploadFileInput) {
uploadDropzone.addEventListener('click', function() {
uploadFileInput.click();
});

if (chooseFileButton) {
chooseFileButton.addEventListener('click', function() {
uploadFileInput.click();
});
}

uploadDropzone.addEventListener('keydown', function(event) {
if (event.key === 'Enter' || event.key === ' ') {
event.preventDefault();
uploadFileInput.click();
}
});

uploadFileInput.addEventListener('change', updateUploadFileName);

['dragenter', 'dragover'].forEach(function(eventName) {
uploadDropzone.addEventListener(eventName, function(event) {
event.preventDefault();
event.stopPropagation();
uploadDropzone.classList.add('is-dragover');
});
});

['dragleave', 'dragend', 'drop'].forEach(function(eventName) {
uploadDropzone.addEventListener(eventName, function(event) {
event.preventDefault();
event.stopPropagation();
uploadDropzone.classList.remove('is-dragover');
});
});

uploadDropzone.addEventListener('drop', function(event) {
if (!event.dataTransfer || !event.dataTransfer.files || event.dataTransfer.files.length === 0) {
return;
}

setSingleFile(event.dataTransfer.files[0]);
updateUploadFileName();
});

updateUploadFileName();
}
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
$cursor_url = $this->get_active_cursor_url();

if ( '' === $cursor_url ) {
return;
}

wp_register_style( 'hura-custom-cursor', false, array(), self::VERSION );
wp_enqueue_style( 'hura-custom-cursor' );
wp_add_inline_style( 'hura-custom-cursor', 'body{cursor:url("' . esc_url( $cursor_url ) . '"),auto!important;}' );
}

function add_menu_item() {
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
