<?php
/**
 * @package autocompletamento_indirizzo
 * @version 0.1.4
 */
/*
Plugin Name: Autocompletamento indirizzo Contact Form 7
Plugin URI: http://wordpress.org/plugins/autocompletamento-indirizzo-contact-form-7/
Description: Aiuta i tuoi utenti con l'autocompilazione dei campi di Indirizzo (partenza, destinazione) e calcola la distanza con il servizio Google Place. CF7
Author: DevFabio
Version: 0.1.4
Author URI: http://inventyourtrade.it
*/

function autocompletament_load_primo(){
	wp_enqueue_script( 'autocompletament_script', plugin_dir_url(__FILE__). 'js/script-mappa.js', array(), 'null', true );
	wp_localize_script( 'autocompletament_script', 'url_sitoweb', plugin_dir_url(__FILE__).'calcola-distanza.php' );
	wp_enqueue_script( 'autocompletament_script' );
	}
add_action('wp_enqueue_scripts','autocompletament_load_primo');
add_action( 'wp_enqueue_scripts', 'autocompletament_gpa_load_user_api' );
/* Inizio seconda parte*/
/***************
Loads google place api scripts
****************************************/
		
function autocompletament_gpa_load_user_api()
{
  $gpa_page = get_option( 'orem_cf7_geo_gpa_page' );
  $api_key = get_option( 'orem_cf7_geo_api_key' );
  wp_localize_script( 'autocompletament_script', 'apikey', $api_key );
  if(is_ssl())
  {
		$securee = 'https';
  }
  else
  {
		$securee = 'http';
  }
  $api_script = $securee.'://maps.googleapis.com/maps/api/js?key=' . $api_key . '&libraries=places&callback=initAutocomplete';
 
  wp_enqueue_script( 'gpa-google-places-api', $api_script, array(), 'null', true );
	
}
add_action('admin_init', 'autocompletament_load_plugin');
/* do stuff once right after activation */
function autocompletament_load_plugin(){
	if (is_admin() && get_option('Activated_Plugin') == 'autocompletament_indirizzo') 
	{
		delete_option( 'Activated_Plugin' );
		
		if (!class_exists('WPCF7')) 
		{
			add_action('admin_notices', 'autocompletament_self_deactivate_notice');
			
			/** Deactivate our plugin if contact form 7 is not installed ***/
			
			deactivate_plugins(plugin_basename(__FILE__));
			
			if (isset($_GET['activate'])) 
			{
				unset($_GET['activate']);
			}
		}
	}
}
function autocompletament_self_deactivate_notice()
	{
	?>
		<div class="notice notice-error">
			<?php    echo "<h2>" . __( 'Please install and activate contact form 7 plugin before activating this plugin. <i>Autocompletamento indirizzo contact form 7</i>', 'autocompletament_indirizzo' ) . "</h2>"; ?>
		</div>
	<?php
	}
function autocompletament_plugin_activate()
	{
		
		$user_permission = current_user_can( 'update_core' );
		if ($user_permission == true)
		{
			add_option('Activated_Plugin', 'autocompletament_indirizzo');
		}
					
	// end of function orem_cf7_address_autocomplete_plugin_activate
	}
	
/*	 plugin activation	*/
register_activation_hook(__FILE__, 'autocompletament_plugin_activate' );

/******************
		creating Google Place API menu item under contacts main menu
		***************************************************************/
		
 function autocompletament_indirizzo_menu_item()
{
	add_submenu_page(
										'wpcf7',
										__('Google Place API','google-place-api'),
										__('Google Place API','google-place-api'), 
										'manage_options',
										'google-place-api',
										'autocompletament_indirizzo_google_place_admin',
										'dashicons-admin-post'
									);
}

/*	create google place api menu under contacts menu	*/
add_action('admin_menu', 'autocompletament_indirizzo_menu_item');
		
/***********************
creating google place api page in admin panel
**********************************************************/
		
function autocompletament_indirizzo_google_place_admin()
{
?>
		<div class="wrap">
			<h1>Google Places API Info.</h1>
			<form method="post" action="options.php">
				<?php
					settings_fields('section');
					do_settings_sections('orem-cf7-gpa-options');      
					submit_button(); 
				?>
			
			</form>
		</div>
<?php
}
/*****************
displaying fields on google place api page
*******************************************************/
function autocompletament_indirizzo_display_gpa_fields()
{
	$user_permission = current_user_can( 'update_core' );
	if ($user_permission == true)
	{
		add_settings_section('section', 'All Settings', null, 'orem-cf7-gpa-options');
		
		add_settings_field('orem_cf7_geo_api_key', 'Google Places API Key', 'autocompletament_indirizzo_display_api_key_element', 'orem-cf7-gpa-options', 'section');
	
		register_setting('section', 'orem_cf7_geo_api_key');
	}
}
/****************************
creating text field for Google Places API Key
****************************************************************/

function autocompletament_indirizzo_display_api_key_element() 
{
?>
	<input type="password" required name="orem_cf7_geo_api_key" id="api_key" value="<?php echo get_option('orem_cf7_geo_api_key')?>" />
<?php
}

/*	display fields on google place api page	*/
add_action('admin_init', 'autocompletament_indirizzo_display_gpa_fields');

/*SHORT CODE*/
/*	create tag for address autocomplete in admin panel*/
		
add_action( 'wpcf7_admin_init', 'autocompletament_indirizzo_add_tag_generator', 20 );
function autocompletament_indirizzo_add_tag_generator() 
{
	if ( ! function_exists( 'wpcf7_add_tag_generator' ) )
		return;

	wpcf7_add_tag_generator( 'partenza', __( 'Indirizzo di partenza', 'autocompletament_indirizzo' ),
		'tb-tg-pane-autocomplete', 'autocompletament_indirizzo_tag_generator_addrauto'  );
	wpcf7_add_tag_generator( 'arrivo', __( 'Indirizzo di destinazione', 'autocompletament_indirizzo' ),
		'tb-tg-pane-autocomplete', 'autocompletament_indirizzo_tag_generator_addrauto_arrivo'  );
	wpcf7_add_tag_generator( 'distanza', __( 'Distanza', 'autocompletament_indirizzo' ),
		'tb-tg-pane-autocomplete', 'autocompletament_indirizzo_tag_generator_addrauto_distanza'  );
}
/******************
		address autocomplete tag details and html genrated
		*********************************************************/
		
function autocompletament_indirizzo_tag_generator_addrauto($contact_form, $args = '' )
	{
		$args = wp_parse_args( $args, array() );
			$type = 'partenza';

			$description = __( "Generate a form-tag for a group of autocomplete field.", 'autocompletament_indirizzo' );
			$desc_link ="";

		?>
		<div class="control-box">
			<fieldset>
				<legend><?php echo sprintf( esc_html( $description ), $desc_link ); ?></legend>

				<table class="form-table">
					<tbody>					
						<tr>
							<th scope="row"><?php echo esc_html( __( 'Field type', 'autocompletament_indirizzo' ) ); ?></th>
							<td>
								<fieldset>
								<legend class="screen-reader-text"><?php echo esc_html( __( 'Field type', 'autocompletament_indirizzo' ) ); ?></legend>
								<label><input type="checkbox" name="required" /> <?php echo esc_html( __( 'Required field', 'autocompletament_indirizzo' ) ); ?></label>
								</fieldset>
							</td>
						</tr>					

						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'autocompletament_indirizzo' ) ); ?></label></th>
							<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
						</tr>

						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id (optional)', 'autocompletament_indirizzo' ) ); ?></label></th>
							<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
						</tr>

						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class (optional)', 'autocompletament_indirizzo' ) ); ?></label></th>
							<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>							
						</tr>

						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-place' ); ?>"><?php echo esc_html( __( 'Placeholder (optional)', 'autocompletament_indirizzo' ) ); ?></label></th>
							<td><input type="text" name="placeholder" class="oneline option" id="<?php echo esc_attr( $args['content'] . '-place' ); ?>" /></td>							
						</tr>

						
					</tbody>
				</table>
			</fieldset>
		</div>

		<div class="insert-box">
			<input type="text" name="<?php echo $type; ?>" class="tag code" readonly onfocus="this.select()" />

			<div class="submitbox">
			<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'autocompletament_indirizzo' ) ); ?>" />
			</div>

			<br class="clear" />

			<p class="description mail-tag"><label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"><?php echo sprintf( esc_html( __( "To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'contact-form-7' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?><input type="text" class="mail-tag code hidden" readonly id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>" /></label></p>
		</div>
		<?php
	}
function autocompletament_indirizzo_tag_generator_addrauto_arrivo($contact_form, $args = '' )
	{
		$args = wp_parse_args( $args, array() );
			$type = 'arrivo';

			$description = __( "Generate a form-tag for a group of autocomplete field.", 'autocompletament_indirizzo' );
			$desc_link ="";

		?>
		<div class="control-box">
			<fieldset>
				<legend><?php echo sprintf( esc_html( $description ), $desc_link ); ?></legend>

				<table class="form-table">
					<tbody>					
						<tr>
							<th scope="row"><?php echo esc_html( __( 'Field type', 'autocompletament_indirizzo' ) ); ?></th>
							<td>
								<fieldset>
								<legend class="screen-reader-text"><?php echo esc_html( __( 'Field type', 'autocompletament_indirizzo' ) ); ?></legend>
								<label><input type="checkbox" name="required" /> <?php echo esc_html( __( 'Required field', 'autocompletament_indirizzo' ) ); ?></label>
								</fieldset>
							</td>
						</tr>					

						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'autocompletament_indirizzo' ) ); ?></label></th>
							<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
						</tr>

						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id (optional)', 'autocompletament_indirizzo' ) ); ?></label></th>
							<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
						</tr>

						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class (optional)', 'autocompletament_indirizzo' ) ); ?></label></th>
							<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>							
						</tr>

						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-place' ); ?>"><?php echo esc_html( __( 'Placeholder (optional)', 'autocompletament_indirizzo' ) ); ?></label></th>
							<td><input type="text" name="placeholder" class="oneline option" id="<?php echo esc_attr( $args['content'] . '-place' ); ?>" /></td>							
						</tr>

						
					</tbody>
				</table>
			</fieldset>
		</div>

		<div class="insert-box">
			<input type="text" name="<?php echo $type; ?>" class="tag code" readonly onfocus="this.select()" />

			<div class="submitbox">
			<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'autocompletament_indirizzo' ) ); ?>" />
			</div>

			<br class="clear" />

			<p class="description mail-tag"><label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"><?php echo sprintf( esc_html( __( "To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'contact-form-7' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?><input type="text" class="mail-tag code hidden" readonly id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>" /></label></p>
		</div>
		<?php
	}
function autocompletament_indirizzo_tag_generator_addrauto_distanza($contact_form, $args = '' )
	{
		$args = wp_parse_args( $args, array() );
			$type = 'distanza';

			$description = __( "Generate a form-tag for a group of autocomplete field.", 'autocompletament_indirizzo' );
			$desc_link ="";

		?>
		<div class="control-box">
			<fieldset>
				<legend><?php echo sprintf( esc_html( $description ), $desc_link ); ?></legend>

				<table class="form-table">
					<tbody>					
						<tr>
							<th scope="row"><?php echo esc_html( __( 'Field type', 'autocompletament_indirizzo' ) ); ?></th>
							<td>
								<fieldset>
								<legend class="screen-reader-text"><?php echo esc_html( __( 'Field type', 'autocompletament_indirizzo' ) ); ?></legend>
								<label><input type="checkbox" name="required" /> <?php echo esc_html( __( 'Required field', 'autocompletament_indirizzo' ) ); ?></label>
								</fieldset>
							</td>
						</tr>					

						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'autocompletament_indirizzo' ) ); ?></label></th>
							<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
						</tr>

						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id (optional)', 'autocompletament_indirizzo' ) ); ?></label></th>
							<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
						</tr>

						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class (optional)', 'autocompletament_indirizzo' ) ); ?></label></th>
							<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>							
						</tr>

						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-place' ); ?>"><?php echo esc_html( __( 'Placeholder (optional)', 'autocompletament_indirizzo' ) ); ?></label></th>
							<td><input type="text" name="placeholder" class="oneline option" id="<?php echo esc_attr( $args['content'] . '-place' ); ?>" /></td>							
						</tr>

						
					</tbody>
				</table>
			</fieldset>
		</div>

		<div class="insert-box">
			<input type="text" name="<?php echo $type; ?>" class="tag code" readonly onfocus="this.select()" />

			<div class="submitbox">
			<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'autocompletament_indirizzo' ) ); ?>" />
			</div>

			<br class="clear" />

			<p class="description mail-tag"><label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"><?php echo sprintf( esc_html( __( "To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'contact-form-7' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?><input type="text" class="mail-tag code hidden" readonly id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>" /></label></p>
		</div>
		<?php
	}

	
	/*************
		autocomplete tag messages
		*************************************/


add_action( 'wpcf7_init', 'autocompletament_indirizzo_add_form_tag' );
 
function autocompletament_indirizzo_add_form_tag() {
    wpcf7_add_form_tag( array( 'partenza', 'arrivo','distanza'), 'autocompletament_indirizzo_partenza_form_tag_handler', true ); // partenza
}
function autocompletament_indirizzo_partenza_form_tag_handler( $tag ) { 
    $tag = new WPCF7_FormTag( $tag ); 
 
    if ( empty( $tag->name ) ) { 
        return ''; 
    } 
 
    $validation_error = wpcf7_get_validation_error( $tag->name ); 
 
    $class = wpcf7_form_controls_class( $tag->type, 'wpcf7-text' ); 
 
 
    if ( $validation_error ) { 
        $class .= ' wpcf7-not-valid'; 
    } 
 
    $atts = array(); 
 
    $atts['size'] = $tag->get_size_option( '40' ); 
    $atts['maxlength'] = $tag->get_maxlength_option(); 
    $atts['minlength'] = $tag->get_minlength_option(); 
 
    if ( $atts['maxlength'] && $atts['minlength'] 
    && $atts['maxlength'] < $atts['minlength'] ) { 
        unset( $atts['maxlength'], $atts['minlength'] ); 
    } 
 
    $atts['class'] = $tag->get_class_option( $class ); 
    $atts['id'] = 'partenza_geo';//$tag->get_id_option(); 
	if($tag->type=='arrivo')
		$atts['id'] = 'arrivo_geo';
	if($tag->type=='distanza')
		$atts['id'] = 'distanza';
    $atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true ); 
 
    $atts['autocomplete'] = $tag->get_option( 'autocomplete',  
        '[-0-9a-zA-Z]+', true ); 
 
    if ( $tag->has_option( 'readonly' ) ) { 
        $atts['readonly'] = 'readonly'; 
    } 
 
    if ( $tag->is_required() ) { 
        $atts['aria-required'] = 'true'; 
    } 
 
    $atts['aria-invalid'] = $validation_error ? 'true' : 'false'; 
 
    $value = (string) reset( $tag->values ); 
 
    if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) { 
        $atts['placeholder'] = $value; 
        $value = ''; 
    } 
 
    $value = $tag->get_default_option( $value ); 
 
    $value = wpcf7_get_hangover( $tag->name, $value ); 
 
    $atts['value'] = $value; 
 
    if ( wpcf7_support_html5() ) { 
        $atts['type'] = $tag->basetype; 
    } else { 
        $atts['type'] = 'text'; 
    } 
 
    $atts['name'] = $tag->name; 
 
    $atts = wpcf7_format_atts( $atts ); 
 
    $html = sprintf( 
        '<span class="wpcf7-form-control-wrap %1$s"><input %2$s />%3$s</span>',  
        sanitize_html_class( $tag->name ), $atts, $validation_error ); 
 
    return $html; 
} 

	