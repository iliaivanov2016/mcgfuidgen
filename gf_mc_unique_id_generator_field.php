<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/*
#IFVER WORDPRESS
Plugin Name: Gravity Forms Mc Unique ID Generator Field (Lite)
Plugin URI: https://modcoding.com/product/gravity-forms-mc-unique-id-generator-field-wordpress-plugin/?utm_source=wordpress
Description: Unique identifiers generator field - located in Advanced Fields section of Gravity Forms fields editor. Lite version, see readme.md for details
Version: 1.20
Author: Modular Coding Inc.
Author URI: https://modcoding.com?utm_source=wordpress
License: GNU GPL v.2
#ENDIF
#IFNVER WORDPRESS
Plugin Name: Gravity Forms Mc Unique ID Generator Field (Full Version)
Plugin URI: https://modcoding.com/product/gravity-forms-mc-unique-id-generator-field-wordpress-plugin/?utm_source=customer
Description: Unique identifiers generator field - located in Advanced Fields section of Gravity Forms fields editor.
Version: 1.20
Author: Modular Coding Inc.
Author URI: https://modcoding.com?utm_source=customer
License: See licenses folder for text of licenses you have purchased
#ENDIF
*/
define ("MCGFUIDGEN_PLUGIN_VERSION","?ver=1.20");
define ("MCGFUIDGEN_TRANSLATE_DOMAIN","mcgfuidgen");
define ("MCGFUIDGEN_UNQIUEID_TAG", "{UniqueID}");
define ("MCGFUIDGEN_MAX_RETRY",100); // max attempts to generate random value
define ("MCGFUIDGEN_DIGITS","0123456789");
define ("MCGFUIDGEN_ABC","abcdefghijklmnopqrstuvwxyz");
define ("MCGFUIDGEN_SYMBOLS","+-=*:;.,@()[]{}#%&^_?!");
define ("MCGFUIDGEN_PLUGIN_DIR",str_replace(DIRECTORY_SEPARATOR,"/",plugin_dir_path( __FILE__ )));
define ("MCGFUIDGEN_PLUGIN_URL",plugins_url( '' , __FILE__ ));
define ("MCGFUIDGEN_AJAX_URL",admin_url('admin-ajax.php'));
global $table_prefix;
define("MCGFUIDGEN_TABLE_NAME",$table_prefix."mcgfuidgen_data");
define ("MCGFUIDGEN_FORM_TABLE",$table_prefix."rg_form_meta");
define ("MCGFUIDGEN_LEAD_TABLE",$table_prefix."rg_lead");
define ("MCGFUIDGEN_LEAD_ENTRY_TABLE",$table_prefix."rg_lead_detail");
define ("MCGFUIDGEN_LEAD_LONG_ENTRY_TABLE",$table_prefix."rg_lead_detail_long");
require_once MCGFUIDGEN_PLUGIN_DIR."includes/log.php";
require_once MCGFUIDGEN_PLUGIN_DIR."includes/functions.php";
/////////////////////////////////////////////////////// main code //////////////////////////////////////////////////////
function mcgfuidgen_activation(){
	global $wpdb;
	$sql = "CREATE TABLE IF NOT EXISTS `".MCGFUIDGEN_TABLE_NAME."`(
	  id BIGINT NOT NULL auto_increment,
	  form_id  INT NOT NULL,
	  entry_id BIGINT NULL,
	  post_id BIGINT NULL,
	  field_id INT NOT NULL,
  	id_str VARCHAR(100) NULL,
  	id_int BIGINT NULL,
	  PRIMARY KEY(id),
	  KEY (id_str),
	  KEY (post_id),
	  KEY (entry_id),
	  KEY (field_id),
	  UNIQUE KEY(id_int, id_str, entry_id, form_id)
	 ) ENGINE = MyISAM;
 	";
	$wpdb->query ( $sql );
	//var_dump($res);die($sql);
}
function mcgfuidgen_add_field_buttons( $field_groups ){
	foreach ( $field_groups as &$group ) {
		if ( $group['name'] == 'advanced_fields' ) {
			$group['fields'][] = array(
					'class'     => 'button',
					'data-type' => 'uidgen',
					'value'     => __( 'Unique ID', MCGFUIDGEN_TRANSLATE_DOMAIN  ),
					'onclick'   => "StartAddField('uidgen');"
			);
			break;
		}
	}
	return $field_groups;
}

function mcgfuidgen_field_standard_settings( $position, $form_id ) {
	//create settings on position 20 (right after Field Description)
	if ( $position == 20 ) {
		?>
		<li class="uidgen_length_setting field_uidgen field_setting">
			<label for="field_uidgen_length" class="mcgfuidgen_iblock" style="margin-bottom: 0 !important;">
				<?php _e( 'Maximum length', MCGFUIDGEN_TRANSLATE_DOMAIN ); ?>
				<?php gform_tooltip( 'form_field_uidgen_length_tooltip' ) ?>
			</label>
			<select id="field_uidgen_length" class="small gfield_select" onchange="mcgfuidgen_save_settings()">
				<?php for ($i = 1; $i <= 100; $i++) { $sel = ($i == 10) ? 'selected="selected"': ""; ?>
					<option value="<?php echo $i;?>" <?php echo $sel;?>><?php echo $i;?></option>
				<?php } ?>
			</select>
		</li>
		<li class="uidgen_chartype_setting field_uidgen field_setting">
			<label for="field_uidgen_chartype" class="mcgfuidgen_iblock" style="margin-bottom: 0 !important;">
				<?php _e( 'Characters type', MCGFUIDGEN_TRANSLATE_DOMAIN ); ?>
				<?php gform_tooltip( 'form_field_uidgen_chartype_tooltip' ) ?>
			</label>
			<select id="field_uidgen_chartype" class="medium gfield_select" onchange="mcgfuidgen_save_settings()">
					<option value="digits" selected="selected">Digits (123)</option>
					<option value="upper">Uppercase (ABC)</option>
					<option value="lower">Lowercase (abc)</option>
					<option value="mixed">Mixed (AbC)</option>
					<option value="upper_digits">Upper and digits (A1b2C3)</option>
					<option value="lower_digits">Lower and digits (a1b2c3)</option>
					<option value="mixed_digits">Mixed and digits (A1b2C3)</option>
					<option value="all">Mixed, digits and symbols (A1@b-2C!3)</option>
			</select>
		</li>
<?php
#IFVER WORDPRESS
?>
		<li class="uidgen_separators_setting field_uidgen field_setting">
			<a href="https://modcoding.com/product/gravity-forms-mc-unique-id-generator-field-wordpress-plugin/?utm_source=wordpress">
				Please visit our web site
			</a>
			to purchase <B>Full Version</b> with additional functionality (separators and Post Update plugin support).
			<select id="field_uidgen_separator" class="small gfield_select" onchange="mcgfuidgen_save_settings()" style="display: none"><option value="none" selected="selected">None</option></select>
			<select id="field_uidgen_separatorfreq" class="medium gfield_select" onchange="mcgfuidgen_save_settings()" style="display: none"><option value="0" selected="selected">None</option></select>
		</li>
<?php
#ENDIF
#IFNVER WORDPRESS
?>
		<li class="uidgen_separators_setting field_uidgen field_setting">
			<label for="field_uidgen_separator">
				<?php _e( 'Characters separator', MCGFUIDGEN_TRANSLATE_DOMAIN ); ?>
				<?php gform_tooltip( 'form_field_uidgen_separator_tooltip' ) ?>
			</label>
			<select id="field_uidgen_separator" class="small gfield_select" onchange="mcgfuidgen_save_settings()">
					<option value="none" selected="selected">None</option>
					<option value="dash">Dash (-)</option>
					<option value="space">Space (&nbsp;)</option>
					<option value="comma">Comma (,)</option>
					<option value="dot">Dot (.)</option>
					<option value="quote">Quote (&#39;)</option>
					<option value="quote2">Double Quote (&#34;)</option>
					<option value="underscore">Underscore (_)</option>
			</select>
			<select id="field_uidgen_separatorfreq" class="medium gfield_select" onchange="mcgfuidgen_save_settings()">
					<option value="0" selected="selected">None</option>
					<option value="1">Every symbol</option>
					<option value="2">Every 2 symbols</option>
					<option value="3">Every 3 symbols</option>
					<option value="4">Every 4 symbols</option>
					<option value="5">Every 5 symbols</option>
					<option value="6">Every 6 symbols</option>
					<option value="7">Every 7 symbols</option>
					<option value="8">Every 8 symbols</option>
					<option value="9">Every 9 symbols</option>
					<option value="10">Every 10 symbols</option>
			</select>
		</li>
<?php
#ENDIF
?>
		<li class="uidgen_sequence_setting field_uidgen field_setting">
			<input type="checkbox" id="field_sequence_enabled"  onclick="mcgfuidgen_save_settings()" />
			<label for="field_sequence_start" class="mcgfuidgen_iblock" style="width: 75%!important; margin-bottom: 0 !important; display: inline-block !important;">
				<?php _e( 'Sequence values', MCGFUIDGEN_TRANSLATE_DOMAIN ); ?>
				<?php gform_tooltip( 'form_field_uidgen_sequence_tooltip' ) ?>
			</label>
			<input type="number" id="field_sequence_start" class="medium" onchange="mcgfuidgen_save_settings()" onblur="mcgfuidgen_save_settings()" placeholder="<?php _e( 'Start value', MCGFUIDGEN_TRANSLATE_DOMAIN ); ?>" min="0" step="1" />
			<input type="number" id="field_sequence_step" class="small" onchange="mcgfuidgen_save_settings()" onblur="mcgfuidgen_save_settings()" placeholder="<?php _e( 'Step', MCGFUIDGEN_TRANSLATE_DOMAIN ); ?>"  min="1" step="1" />
		</li>
		<?php
	}
}

function mcgfuidgen_field_appearance_settings( $position, $form_id ) {
 if ( $position == 150 ) {
?>
		<li class="uidgen_render_setting field_uidgen field_setting">
			<label for="field_uidgen_render" class="mcgfuidgen_iblock" style="margin-bottom: 0 !important;">
				<?php _e( 'Render options', MCGFUIDGEN_TRANSLATE_DOMAIN ); ?>
				<?php gform_tooltip( 'form_field_uidgen_render_tooltip' ) ?>
			</label>
			<select id="field_uidgen_render" class="medium gfield_select" onchange="mcgfuidgen_save_settings()">
					<option value="text" selected="selected">Text</option>
					<option value="hidden">Hidden</option>
					<option value="div">Div</option>
					<option value="span">Span</option>
					<option value="html">Custom HTML</option>
			</select>
			<textarea id="field_uidgen_render_custom_html" placeholder="Custom HTML" style="width:  96%; margin-top: 8px;"  onchange="mcgfuidgen_save_settings()" onblur="mcgfuidgen_save_settings()"><div><?php echo MCGFUIDGEN_UNQIUEID_TAG;?></div></textarea>
		</li>
<?php
	}
}

function mcgfuidgen_tooltips( $tooltips ) {
  $tooltips['form_field_uidgen_length_tooltip'] = "<h6>".__( 'Maximum length', MCGFUIDGEN_TRANSLATE_DOMAIN )."</h6>".__( 'Select number of characters used to generate identifier value.', MCGFUIDGEN_TRANSLATE_DOMAIN );
  $tooltips['form_field_uidgen_chartype_tooltip'] = "<h6>".__( 'Characters type', MCGFUIDGEN_TRANSLATE_DOMAIN )."</h6>".__( 'Select type of characters used to generate identifier value. if you will select Digits and length up to 20 characters, an integer value will be stored in the database. It works much faster than strings.', MCGFUIDGEN_TRANSLATE_DOMAIN );
  $tooltips['form_field_uidgen_separator_tooltip'] = "<h6>".__( 'Characters separator', MCGFUIDGEN_TRANSLATE_DOMAIN )."</h6>".__( 'Select type of character separator and how much characters in a row should be separated into groups.', MCGFUIDGEN_TRANSLATE_DOMAIN );
	$tooltips['form_field_uidgen_sequence_tooltip'] = "<h6>".__( 'Sequence values', MCGFUIDGEN_TRANSLATE_DOMAIN )."</h6>".__( 'If you have selected this option the Identifier value will be generated as sequential number starting from specified value incremented with specified step on every new submit. If option is disabled, then random values will be generated"' );
	$tooltips['form_field_uidgen_render_tooltip'] = "<h6>".__( 'Render options', MCGFUIDGEN_TRANSLATE_DOMAIN )."</h6>".__( 'Specifies how this field will be rendered on form. You can use custom HTML, but keep {UniqueID} shortcode.' );
  return $tooltips;
}

function mcgfuidgen_editor_js(){
	wp_enqueue_script( 'mcgfuidgen-editor', MCGFUIDGEN_PLUGIN_URL."/assets/js/editor.js", array(), MCGFUIDGEN_PLUGIN_VERSION, true );
	wp_enqueue_script( 'mcgfuidgen-jquery-stringify', MCGFUIDGEN_PLUGIN_URL."/assets/js/jQuery.stringify.js", array(), MCGFUIDGEN_PLUGIN_VERSION, true );
	wp_enqueue_style( 'mcgfuidgen-editor', MCGFUIDGEN_PLUGIN_URL."/assets/css/editor.css", array(), MCGFUIDGEN_PLUGIN_VERSION, true );
	echo '
		<script type=\'text/javascript\'>
		var MCGFUIDGEN_AJAX_URL = "'.MCGFUIDGEN_AJAX_URL.'";
		var MCGFUIDGEN_UNQIUEID_TAG = "'.MCGFUIDGEN_UNQIUEID_TAG.'";
		var MCGFUIDGEN_DIGITS = "'.MCGFUIDGEN_DIGITS.'";
		var MCGFUIDGEN_ABC = "'.MCGFUIDGEN_ABC.'";
		var MCGFUIDGEN_SYMBOLS = "'.MCGFUIDGEN_SYMBOLS.'";
	</script>
	';
}

function mcgfuidgen_field_type_title( $title, $field_type ) {
//mcgfuidgen_log(">mcgfuidgen_field_type_title title = $title type = $field_type");
	if ( $field_type == 'uidgen' ) {
//mcgfuidgen_log("<mcgfuidgen_field_type_title ".__( 'Unique ID', MCGFUIDGEN_TRANSLATE_DOMAIN ));
		return __( 'Unique ID', MCGFUIDGEN_TRANSLATE_DOMAIN );
	}
	else {
//mcgfuidgen_log("<mcgfuidgen_field_type_title $title");
		return $title;
	}
}

function mcgfuidgen_load_value($post_id){
	global $wpdb;
	// load value from db
if ( defined( "MCGFUIDGEN_DEBUG" ) ) mcgfuidgen_log(">mcgfuidgen_load_value post_id = $post_id");
	$qr = $wpdb->get_results("SELECT id_int, id_str FROM `".MCGFUIDGEN_TABLE_NAME."` WHERE post_id = ".$post_id);
if ( defined( "MCGFUIDGEN_DEBUG" ) ) mcgfuidgen_log("1.mcgfuidgen_load_value post_id = $post_id\n".print_r($qr,true));
	if (count($qr) < 1)  return false;
	if (is_array($qr)) {
		$value = (strlen(@$qr[0]->id_int) > 0) ? $qr[0]->id_int : $qr[0]->id_str;
	}
if ( defined( "MCGFUIDGEN_DEBUG" ) ) mcgfuidgen_log(">mcgfuidgen_load_value post_id = $post_id value = $value");
	return array("value" => $value, "id" => "");
}

function mcgfuidgen_generate_value($form_id,$entry_id,$field_id,$settings){
	if (!is_array($settings)) return false;
	global $wpdb;
	if ((int)$entry_id  == 0)
		$entry_id = "";
	$separator = $abc = "";
#IFVER WORDPRESS
	$sep = "none";
	$freq = 0;
#ENDIF
#IFNVER WORDPRESS
	$sep = $settings["separator"];
	$freq = (int)@$settings["separator_freq"];
#ENDIF
	$len = $settings["max_length"];
	$seq_start = ((int)@$settings["sequence_on"] > 0) ? (int)@$settings["sequence_start"] : -1;
	$seq_step = (int)@$settings["sequence_step"];
	$t = $settings["char_type"];
	// prepare params: $abs is alphabet, set of characters used to generate unique identifier value
	if ((int)@$settings["sequence_on"] <= 0) {
		if ($t == "digits")
		 $abc = MCGFUIDGEN_DIGITS;
		else
		if ($t == "upper")
		 $abc = strtoupper(MCGFUIDGEN_ABC);
		else
		if ($t == "lower")
		 $abc = MCGFUIDGEN_ABC;
		else
		if ($t == "mixed")
		 $abc = strtoupper(MCGFUIDGEN_ABC).MCGFUIDGEN_ABC;
		else
		if ($t == "upper_digits")
		 $abc = MCGFUIDGEN_DIGITS.strtoupper(MCGFUIDGEN_ABC);
		else
		if ($t == "lower_digits")
		 $abc = MCGFUIDGEN_DIGITS.MCGFUIDGEN_ABC;
		else
		if ($t == "mixed_digits")
		 $abc = MCGFUIDGEN_DIGITS.strtoupper(MCGFUIDGEN_ABC).MCGFUIDGEN_ABC;
		else
		 $abc = MCGFUIDGEN_DIGITS.strtoupper(MCGFUIDGEN_ABC).MCGFUIDGEN_ABC.MCGFUIDGEN_SYMBOLS;
	}
#IFNVER WORDPRESS
	if ($sep == "space") $separator = " ";
	else
	if ($sep == "dash") $separator = "-";
	else
	if ($sep == "comma") $separator = ",";
	else
	if ($sep == "dot") $separator = ".";
	else
	if ($sep == "quote") $separator = "&#39;";
	else
	if ($sep == "quote2") $separator = "&#34;";
	else
	if ($sep == "underscore") $separator = "_";
#ENDIF
	$abc_len = strlen($abc);
	$retry = 0;
if ( defined( "MCGFUIDGEN_DEBUG" ) ) mcgfuidgen_log(">mcgfuidgen_generate_value form_id = $form_id, entry_id = $entry_id, field_id = $field_id, abc len = $abc_len, ABC:\n$abc\nsettings:\n".print_r($settings,true));
	if ((int)@$settings["sequence_on"] > 0)
		$seq_start += $seq_step;
	while ($retry < MCGFUIDGEN_MAX_RETRY) {
		$value = "";
		if ((int)@$settings["sequence_on"] > 0)
			$abc = "" . $seq_start;
		for ($i = 1; $i <= $len; $i++ ) {
			if ((int)@$settings["sequence_on"] > 0) {
				$c = substr($abc,$i-1,1);
			} else {
				$x = rand(0, PHP_INT_MAX) % $abc_len;
				$c = substr($abc,$x,1);
			}
			$value .= $c;
			if ($freq > 0)
				if (((($len-$i) % $freq) == 0) && ($i < $len))
				 $value .= $separator;
		}
		if ((int)@$settings["sequence_on"] > 0) {
			$settings["sequence_start"] = $seq_start;
			// save settings
			$form = GFAPI::get_form($form_id);
			foreach ($form["fields"] as &$field) {
				if ($field->id == $field_id) {
					$field->mcgfuidgen_settings = json_encode($settings);
					break;
				}
			}
			GFAPI::update_form( $form );
			$seq_start += $seq_step;
		}
		// check if this is unique value
		$wpdb->query("LOCK TABLES `".MCGFUIDGEN_TABLE_NAME."` WRITE");
		$qty = (int)$wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) AS qty FROM `".MCGFUIDGEN_TABLE_NAME."`
						WHERE (form_id = $form_id) AND ((id_str = %s) OR (id_int = %d)) ", $value, $value ));
if ( defined( "MCGFUIDGEN_DEBUG" ) ) mcgfuidgen_log("1.mcgfuidgen_generate_value form_id = $form_id, entry_id = $entry_id, value = $value, qty = $qty, retry = $retry");
		if ($qty <= 0) {
//var_dump($wpdb->prepare("INSERT INTO `".MCGFUIDGEN_TABLE_NAME."`(form_id,entry_id,field_id,id_str) VALUES ( $form_id, %s ,$field_id, %s )", $entry_id, $value ));die;
			if (($t == "digits") && ($freq <= 0) && ($len <= 10))
				$wpdb->query($wpdb->prepare( "INSERT INTO `".MCGFUIDGEN_TABLE_NAME."`(form_id,entry_id,field_id,id_int) VALUES ($form_id,%s,$field_id,%d)", $entry_id, $value ));
			else
				$wpdb->query($wpdb->prepare( "INSERT INTO `".MCGFUIDGEN_TABLE_NAME."`(form_id,entry_id,field_id,id_str) VALUES ($form_id,%s,$field_id,%s)", $entry_id, $value ));
			$wpdb->query("UNLOCK TABLES");
			$id = $wpdb->insert_id;
if ( defined( "MCGFUIDGEN_DEBUG" ) ) mcgfuidgen_log("<mcgfuidgen_generate_value form_id = $form_id, entry_id = $entry_id, value = $value, id = $id, retry = $retry");
			return array("id" => $id, "value" => $value);
		}
		$wpdb->query("UNLOCK TABLES");
		$retry++;
	} // loop until value will not be unqiue
if ( defined( "MCGFUIDGEN_DEBUG" ) ) mcgfuidgen_log("<mcgfuidgen_generate_value ERROR! form_id = $form_id, entry_id = $entry_id, value = $value, retry = $retry");
	return false;
}

function mcgfuidgen_field_input($input, $field, $value, $entry_id, $form_id){
	if ($field->type == 'uidgen') {
		$post_id = @$GLOBALS["mcgfuidgen_post_id"];
if (defined("MCGFUIDGEN_DEBUG")) mcgfuidgen_log(">mcgfuidgen_field_input value = $value, entry_id = $entry_id, form_id = $form_id, post_id = $post_id, field:\n".print_r($field,true)."\n".print_r($value,true));
//		$val = (is_array($value)) ? @$value[ $field['id'] ] : $value;
		$settings_str = $field->mcgfuidgen_settings;
if (defined("MCGFUIDGEN_DEBUG")) mcgfuidgen_log("1.mcgfuidgen_field_input value = $value, entry_id = $entry_id, form_id = $form_id, settings string:\n".$settings_str);
		if (strpos($settings_str,"}") === false) return $input;
		$settings = json_decode($settings_str,true);
if (defined("MCGFUIDGEN_DEBUG")) mcgfuidgen_log("2.mcgfuidgen_field_input value = $value, entry_id = $entry_id, form_id = $form_id, settings:\n".print_r($settings,true));
		if (!is_array($settings)) return $input;
		if ($post_id !== false) {
			$ar = mcgfuidgen_load_value($post_id);
			if ($ar === false)
				$ar = mcgfuidgen_generate_value($form_id,$entry_id,$field->id,$settings);
		} else {
			$ar = mcgfuidgen_generate_value($form_id,$entry_id,$field->id,$settings);
		}
if (defined("MCGFUIDGEN_DEBUG")) mcgfuidgen_log("3.mcgfuidgen_field_input value = $value, entry_id = $entry_id, form_id = $form_id, value - id:\n".print_r($ar,true));
		$pr_type = $settings["render_type"];
		if ($pr_type == "html")  {
			$html = $settings["render_html"];
			if (stripos($html,MCGFUIDGEN_UNQIUEID_TAG) === false)
				$html .= MCGFUIDGEN_UNQIUEID_TAG;
		} else
		if ($pr_type == "span")  {
			$html = '<span>'.MCGFUIDGEN_UNQIUEID_TAG.'</span>';
		} else
		if ($pr_type == "div")  {
			$html = '<div>'.MCGFUIDGEN_UNQIUEID_TAG.'</div>';
		} else
		if ($pr_type == "hidden")  {
			$html = '<input type="hidden" value="'.MCGFUIDGEN_UNQIUEID_TAG.'"/>';
		} else {
			$html = '<input type="text" value="'.MCGFUIDGEN_UNQIUEID_TAG.'" readonly="readonly" disabled="disabled" />';
		}
		$html = str_replace(MCGFUIDGEN_UNQIUEID_TAG,$ar["value"],$html);
		$input = '
							<div class="ginput_complex ginput_container">
								<div class="mcgfuidgen_html">'.$html.'</div>
								<input type="hidden" class="mcgfuidgen_form_id" value=\''.$form_id.'\'/>
								<input type="hidden" class="mcgfuidgen_entry_id" value=\''.$entry_id.'\'/>
							';
		$input .= sprintf('
								<input type="hidden" name="input_%1$s" class="gform_hidden mcgfuidgen_value" id="input_%2$s_%1$s" value=\'%3$s\' />
								<input type="hidden" name="input_%1$s_id" class="gform_hidden mcgfuidgen_value" id="input_%2$s_%1$s_id" value=\'%4$s\' />
								',
					$field['id'],
					$form_id,
					$ar["value"],
					$ar["id"]
		);
		$input .= '
							</div>
';
if (defined("MCGFUIDGEN_DEBUG")) mcgfuidgen_log("<mcgfuidgen_field_input value = $value, entry_id = $entry_id, form_id = $form_id, field:\n".$input);
	}
	return $input;
}

function mcgfuidgen_post_paging( $form, $source_page_number, $current_page_number ) {
if ( defined( "MCGFUIDGEN_DEBUG" ) ) {
	 mcgfuidgen_log("mcgfuidgen_post_paging form_id = " . $form["id"]." UIDGEN front =  ".$GLOBALS['MCGFUIDGEN_FRONT'] ." MCGF front = ".$GLOBALS['MCGF_FRONT_INIT']);
	file_put_contents( MCGFUIDGEN_PLUGIN_DIR . "submit_page_form_" . $form["id"] . ".txt", print_r( $form, true ) );
	file_put_contents( MCGFUIDGEN_PLUGIN_DIR . "submit_page_request_" . $form["id"] . ".txt", print_r( $_REQUEST, true ) );
}
	if ((int)@$GLOBALS['MCGF_FRONT_INIT'] <= 0) {
		 $GLOBALS['MCGF_FRONT_INIT'] = 1;
//		 wp_enqueue_script( 'jquery', site_url(). '/wp-includes/js/jquery/jquery.js', array(), MCGFUIDGEN_PLUGIN_VERSION, false );
//		 wp_enqueue_script( 'jquery-migrate', site_url(). '/wp-includes/js/jquery/jquery-migrate.min.js', array(), MCGFUIDGEN_PLUGIN_VERSION, false );
	} // global init
	if ((int)@$GLOBALS['MCGFUIDGEN_FRONT'] <= 0) {
		$GLOBALS['MCGFUIDGEN_FRONT'] = 1;
	} // MCGFUIDGEN init
}

function mcgfuidgen_field_css_class($classes, $field, $form){
	if( $field["type"] == "uidgen" ){
		$classes .= " gform_uidgen";
	}
	return $classes;
}

function mcgfuidgen_head(){
	@ob_start();
}

function mcgfuidgen_footer(){
	$s = @ob_get_clean();
	$form_id = 0;
	if ($ar = mcgfuidgen_GetStringBetweenTags("gform_fields_","'",$s))
		$form_id = (int)@$ar[0];
	if ($form_id > 0) {
		// some form rendered
		$GLOBALS['MCGF_FRONT_INIT'] = 1;
		// render required scripts for front view of submission pages of the form
		mcgfuidgen_post_paging(array("id" => $form_id),1,1);
	}
	echo $s;
}

function mcgfuidgen_after_submission( $entry, $form ){
	global $wpdb;
	$form_id = $form["id"];
	$post_id = $entry["post_id"];
	$entry_id = $entry["id"];
if ( defined( "MCGFUIDGEN_DEBUG" ) ) {
	 mcgfuidgen_log(">mcgfuidgen_after_submission form_id = ".$form["id"]." post_id = $post_id, entry_id = $entry_id");
	file_put_contents( MCGFUIDGEN_PLUGIN_DIR . "uidgen_after_submit_form_" . $form["id"] . ".txt", print_r( $form, true ) );
	file_put_contents( MCGFUIDGEN_PLUGIN_DIR . "uidgen_after_submit_entry_" . $form["id"] . ".txt", print_r( $entry, true ) );
}
	// set post_id on insert new post
	foreach ($form["fields"] as $field) {
			if ($field->type == "uidgen") {
				$field_id = $field->id;
				$id = @$_REQUEST["input_".$field_id."_id"];
if ( defined( "MCGFUIDGEN_DEBUG" ) ) mcgfuidgen_log("1.mcgfuidgen_after_submission field_id = $field_id id = $id");
				if (strlen($id) > 0) {
					$sql = $wpdb->prepare( "UPDATE `".MCGFUIDGEN_TABLE_NAME."` SET post_id = %s, entry_id = %s, form_id = %d, field_id = %d WHERE id = %s",$post_id,$entry_id,$form_id,$field_id,$id );
if ( defined( "MCGFUIDGEN_DEBUG" ) ) mcgfuidgen_log("2.mcgfuidgen_after_submission field_id = $field_id query\n".print_r($sql,true));
					$wpdb->query($sql);
				}
			}
	}
}

////////////////////////////////////////////////// filters, actions, hooks /////////////////////////////////////////////
register_activation_hook( __FILE__, 'mcgfuidgen_activation' ); // plugin activation
// add new field button in fields editor
add_filter( 'gform_add_field_buttons', 'mcgfuidgen_add_field_buttons' );
// adds custom settings to fields editor 'General options' tab (when Unique id generator field is selected)
add_action( 'gform_field_standard_settings', 'mcgfuidgen_field_standard_settings', 10, 2 );
// adds custom settings to fields editor 'Appearance Settings' tab (when Unique id generator field is selected)
add_action( 'gform_field_appearance_settings', 'mcgfuidgen_field_appearance_settings', 10, 2 );
// show tooltips in fields editor
add_filter( 'gform_tooltips', 'mcgfuidgen_tooltips' );
// adds javascript code for new features added to fields editor by this plugin
add_action( 'gform_editor_js', 'mcgfuidgen_editor_js' );
// Adds title to GF custom field
add_filter( 'gform_field_type_title' , 'mcgfuidgen_field_type_title', 10, 2);
// Renders input element show on form submission page
if (!is_admin()) add_filter('gform_field_input', 'mcgfuidgen_field_input', 10, 5);
// adds front scripts and CSS if form has page breaks
$GLOBALS['MCGFUIDGEN_FRONT'] = 0;
if ((int)@$GLOBALS['MCGF_FRONT_INIT'] <= 0) $GLOBALS['MCGF_FRONT_INIT']= 0;
add_action( 'gform_post_paging', 'mcgfuidgen_post_paging', 10, 3 );
// Add a custom class to the field li
add_action("gform_field_css_class", "mcgfuidgen_field_css_class", 10, 3);
// after form submission
add_action('gform_after_submission', 'mcgfuidgen_after_submission', 10, 2);
// filter page content
add_action('init', 'mcgfuidgen_head', 0);
add_action('wp_footer', 'mcgfuidgen_footer', PHP_INT_MAX);
#IFNVER WORDPRESS
// support for Gravity Form Update Post plugin shortcodes: [gravityform id="<FORM_ID>" update="<POST_ID>"] or [gravityform id="<FORM_ID>" update]
// loads unqiue id value stored on post creation instead of generation new value
function mcgfuidgen_shortcode_atts_gravityforms($out, $pairs, $atts){
if ( defined( "MCGFUIDGEN_DEBUG" ) ) {
	mcgfuidgen_log(">mcgfuidgen_shortcode_atts_gravityforms");
	file_put_contents( MCGFUIDGEN_PLUGIN_DIR . "mcgfuidgen_shortcode_atts_gravityforms_out.txt", print_r( $out, true ) );
	file_put_contents( MCGFUIDGEN_PLUGIN_DIR . "mcgfuidgen_shortcode_atts_gravityforms_pairs.txt", print_r( $pairs, true ) );
	file_put_contents( MCGFUIDGEN_PLUGIN_DIR . "mcgfuidgen_shortcode_atts_gravityforms_atts.txt", print_r( $atts, true ) );
}
	$post_id = (isset($atts["update"]) && ($atts["update"] != "false") && is_numeric(@$atts["update"])) ? $atts["update"] : false;
	if (! $post_id ) {
		$post_id = (! empty($GLOBALS['post']->ID) ) ? $GLOBALS['post']->ID : false;
	}
	$GLOBALS["mcgfuidgen_post_id"] = $post_id;
if ( defined( "MCGFUIDGEN_DEBUG" ) )	mcgfuidgen_log("<mcgfuidgen_shortcode_atts_gravityforms post_id = $post_id");
	return $out;
}
if (!is_admin()) add_filter( 'shortcode_atts_gravityforms', 'mcgfuidgen_shortcode_atts_gravityforms', 10, 3 );
#ENDIF
////////////////////////////////////////////////// end filters, actions, hooks /////////////////////////////////////////
?>