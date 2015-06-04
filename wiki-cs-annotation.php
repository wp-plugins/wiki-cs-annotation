<?php
/*
    Plugin Name: Wiki CS Annotation
    Plugin URI: http://apriliusplugin.comuv.com/
    Version: 1.0.1
    Description: This plugin performs entity annotation by giving tag suggestions and creating links to Wikipedia.
    Author: William Aprilius
    Author URI: https://profiles.wordpress.org/william-aprilius
    License: GPLv2
 */
/*  Copyright 2015  William Aprilius (email : william.aprilius@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

defined('ABSPATH') or exit; //prevents direct access to the file

$wca_message_html_prefix_updated = '<div id="message" class="updated"><p>';
$wca_message_html_prefix_error = '<div id="message" class="error"><p>';
$wca_message_html_prefix_warning = '<div id="message" class="updated warning"><p>';
$wca_message_html_prefix_note = '<div id="message" class="updated note"><p>';
$wca_message_html_suffix = '</p></div>';

$wca_plugin_dir_path = plugin_dir_path(__FILE__);

/**
 * creates tables when activate plugin
 * hooked via register_activation_hook
 */
function wca_install_wiki_cs_annotation() {
    global $wpdb;
    global $charset_collate;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // =========== CREATE TABLE wp_wca_anchor ===========
    $sql_create_table_anchor = "CREATE TABLE {$wpdb->prefix}wca_anchor (
		anchor_id int(5) unsigned NOT NULL auto_increment,
		anchor_text varchar(100) NOT NULL,
		link_a int(5) unsigned NOT NULL default '0',
                freq_a int(5) unsigned NOT NULL default '0',
		PRIMARY KEY  (anchor_id)
		) $charset_collate; ";
    dbDelta($sql_create_table_anchor);

    // =========== CREATE TABLE wp_wca_page_wiki ===========
    $sql_create_table_page_wiki = "CREATE TABLE {$wpdb->prefix}wca_page_wiki (
		page_wiki_id int(5) unsigned NOT NULL auto_increment,
		page_wiki_title varchar(100) NOT NULL,
		PRIMARY KEY  (page_wiki_id)
		) $charset_collate; ";
    dbDelta($sql_create_table_page_wiki);

    // =========== CREATE TABLE wp_wca_anchor_sense_map ===========
    $sql_create_table_anchor_sense_map = "CREATE TABLE {$wpdb->prefix}wca_anchor_sense_map (
		map_id int(5) unsigned NOT NULL auto_increment,
		anchor_id int(5) unsigned NOT NULL,
		sense_title varchar(100) NOT NULL,
                counter int(5) unsigned NOT NULL default '0',
		PRIMARY KEY  (map_id)
		) $charset_collate; ";
    dbDelta($sql_create_table_anchor_sense_map);
    // Add foreign key references to wp_wca_anchor table
    $sql_add_foreign_reference_wp_wca_anchor = "ALTER TABLE {$wpdb->prefix}wca_anchor_sense_map ADD FOREIGN KEY (anchor_id)
        REFERENCES {$wpdb->prefix}wca_anchor(anchor_id);";
    $wpdb->query($sql_add_foreign_reference_wp_wca_anchor);

    // =========== CREATE TABLE wp_wca_in_page ===========
    $sql_create_table_in_page = "CREATE TABLE {$wpdb->prefix}wca_in_page (
		in_page_id int(5) unsigned NOT NULL auto_increment,
		map_id int(5) unsigned NOT NULL,
		page_wiki_id int(5) unsigned NOT NULL,
		PRIMARY KEY  (in_page_id)
		) $charset_collate; ";
    dbDelta($sql_create_table_in_page);
    // Add foreign key references to wp_wca_anchor_sense_map table
    $sql_add_foreign_reference_wp_wca_anchor_sense_map = "ALTER TABLE {$wpdb->prefix}wca_in_page ADD FOREIGN KEY (map_id)
        REFERENCES {$wpdb->prefix}wca_anchor_sense_map(map_id);";
    $wpdb->query($sql_add_foreign_reference_wp_wca_anchor_sense_map);
    // Add foreign key references to wp_wca_page_wiki table
    $sql_add_foreign_reference_wp_wca_page_wiki = "ALTER TABLE {$wpdb->prefix}wca_in_page ADD FOREIGN KEY (page_wiki_id)
        REFERENCES {$wpdb->prefix}wca_page_wiki(page_wiki_id);";
    $wpdb->query($sql_add_foreign_reference_wp_wca_page_wiki);
}

register_activation_hook(__FILE__, 'wca_install_wiki_cs_annotation');

/**
 * return plugin version
 */
function wca_get_plugin_version(){
    if(!function_exists('get_plugin_data')){
        require_once(ABSPATH .'wp-admin/includes/plugin.php');
    }

    $wca_plugin_data = get_plugin_data( __FILE__, false, false);
    $wca_plugin_version = $wca_plugin_data['Version'];
    return $wca_plugin_version;
}

/**
 * only if the admin panel is being displayed
 */
if (is_admin ()) {
    add_action('admin_menu', 'wca_menu_link');
    add_action('admin_init', 'wca_admin_init_actions');
    add_action('wp_ajax_wca_get_suggested_tags', 'wca_get_suggested_tags');
    add_action('wp_ajax_wca_import_anchor_data', 'wca_import_anchor_data');
}

/**
 * add sub menu page to the Settings menu.
 */
function wca_menu_link() {
    add_options_page('Wiki CS Annotation', 'Wiki CS Annotation', 'manage_options', 'wiki-cs-annotation', 'wca_options_page_display');
}

/**
 * load plugin setting page
 * Wiki CS Annotation
 */
function wca_options_page_display() {
    global $wca_message_html_prefix_updated,
	$wca_message_html_prefix_error,
	$wca_message_html_prefix_warning,
	$wca_message_html_prefix_note,
	$wca_message_html_suffix,
        $wca_plugin_dir_path;

    if(!function_exists('split_xml_file')){
        require_once($wca_plugin_dir_path . 'includes/import-anchor-data.php');
    }
?>
    <div class="wrap">
        <h2>Wiki CS Annotation</h2>
        <div id="wca_complete_message"></div>
        <div class="wca_settings_box">
            <h3>Import Anchor Dictionary</h3>
            <form name="wca_import_form" action="<?php echo admin_url('options-general.php?page=wiki-cs-annotation'); ?>" enctype="multipart/form-data" method="post">
                <table class="wca_width_100_percent">
                    <tr>
                        <td class="wca_width_45_percent">
                            Import anchor dictionary from a XML file:
                            <span class="wca_help" title="This tool will import anchor dictionary which is required for annotating entity. The filename must contain the suffix &quot;xml&quot;.">i</span>
                        </td>
                        <td>
                            <input type="file" name="wca_uploaded_file" />
                        </td>
                        <td>
                            <?php submit_button( 'Import', 'secondary' ); ?>
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="wca_import_action" value="import-data" />
                <?php wp_nonce_field('wca_import_from_xml_nonce', 'wca_import_from_xml_hash'); ?>
            </form>
            <div id="wca_progress_bar"><div class="progress-label"></div></div>
        </div>
    </div>
<?php
    /* Listen for form submission */
    if ( empty( $_POST['wca_import_action'] ) || 'import-data' !== $_POST['wca_import_action'] )
        return;

    /* Check permissions and nonces */
    if ( ! current_user_can( 'manage_options' ) )
        wp_die();

    /* Check nonce */
    check_admin_referer( 'wca_import_from_xml_nonce', 'wca_import_from_xml_hash' );

    /* Perform checks on file: */

    // Sanity check
    if ( $_FILES["wca_uploaded_file"]["error"] !== 4 ){
        $file = $_FILES["wca_uploaded_file"];
        
        // Is it of the expected type?
        if ( $file["type"] == "text/xml" ){
            
            // Impose a limit on the size of the uploaded file. Max 2097152 bytes = 2MB
            if ( $file["size"] < 2097152 ) {
                
                if( $file["error"] == 0 ){
                    /* If we've made it this far then we can import the data */

                    try{
                        $split_file_locations = split_xml_file($file['tmp_name'], $wca_plugin_dir_path . 'tmp');

                        // file chunck count
                        $xml_chunk_count = sizeof($split_file_locations);

                        // anchor_entries_count
                        $anchor_entries_count = $split_file_locations[0];

                        for($i = 0; $i < $xml_chunk_count-1; $i++){

                            $input_hidden_name = 'wca_import_chunk_location_' . ($i + 1);

                            ?>

                            <input type="hidden" name="<?php echo $input_hidden_name ?>" id="<?php echo $input_hidden_name ?>" value="<?php echo $split_file_locations[$i+1] ?>" />

                            <?php
                        }

                        echo "<script type='text/javascript'>
                            wca_set_anchor_entries_total_count($anchor_entries_count);
                            wca_chunk_form_submit($xml_chunk_count-1);
                        </script>";
                        //
                    }
                    catch(Exception $e){
                        echo $wca_message_html_prefix_error . "<strong>Error: </strong>" . $e->getMessage() . $wca_message_html_suffix;
                    }
                }
                else{
                    echo $wca_message_html_prefix_error . "<strong>Error:</strong> Error encountered: " . $file["error"] . $wca_message_html_suffixs;
                }
            }
            else{
                $size = size_format( $file['size'], 2 );
                echo $wca_message_html_prefix_error . "<strong>Error:</strong> File size too large (". $size ."). Maximum 2MB" . $wca_message_html_suffix;
            }
        }
        else{
            echo $wca_message_html_prefix_error . "<strong>Error:</strong> There was an error importing the anchor data. File type detected: '" . $file['type'] . "'. 'text/xml' expected" . $wca_message_html_suffix;
        }
    }
    else{
        echo $wca_message_html_prefix_error . "<strong>Error:</strong> No file found" . $wca_message_html_suffix;
    }
}

/**
 * init action
 */
function wca_admin_init_actions(){
    global $pagenow;

    if($pagenow == 'options-general.php' AND isset($_GET['page']) AND $_GET['page'] == 'wiki-cs-annotation'){ //page options-general.php?page=wiki-cs-annotation is being displayed
        add_action('admin_enqueue_scripts', 'wca_load_options_page_scripts');
    }

    if(in_array($pagenow, array('post.php', 'post-new.php'))){ //page post.php or post-new.php is being displayed
        add_action('add_meta_boxes', 'wca_meta_box_add');
        add_action('admin_enqueue_scripts', 'wca_load_meta_box_scripts');
        add_action('save_post', 'wca_savetags', 10, 2);
        add_filter('content_save_pre', 'wca_link_content_filter' );
    }
}

/**
 * load JS and CSS on the options page
 */
function wca_load_options_page_scripts(){
    $wca_plugin_url = plugin_dir_url( __FILE__ );
    //load CSS
    wp_enqueue_style('wca_options_page_style', $wca_plugin_url .'css/wca-options-page.css', false, wca_get_plugin_version());
    //load CSS JQuery UI
    wp_enqueue_style('wca_progress_bar_style', $wca_plugin_url .'css/wca_jquery-ui.min.css', false, wca_get_plugin_version());
    //load JS
    wp_enqueue_script('wca_options_page_js', $wca_plugin_url . 'js/wca-options-page.js', array('jquery', 'jquery-ui-progressbar'), wca_get_plugin_version());
    //Localize script
    wp_localize_script( 'wca_options_page_js', 'ajax_object', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'ajax_import_nonce' => wp_create_nonce('wca_import_nonce')
    ) );
}

/**
 * add meta box
 */
function wca_meta_box_add(){
    add_meta_box('wca_meta_box','Wiki CS Annotation','wca_meta_box_content','post','normal');
}

/**
 * meta box content
 */
function wca_meta_box_content(){
    //Existing post tags
    global $post;
    $existing_tags = wp_get_post_tags($post->ID);
    $tags = array();
    if (count($existing_tags) > 0) {
        foreach ($existing_tags as $tag) {
            if ($tag->taxonomy == 'post_tag')
                $tags[] = $tag->name;
        }
    }
?>
    <div>
        <div class="wca_left_metabox">
            <input type="hidden" name="wca_anchor_sense_list" id="wca_anchor_sense_list" value=""/>
            <input type="hidden" name="wca_tag_list" id="wca_tag_list" value="<?php echo implode(', ', $tags); ?>" />
            <p>
                <span class="wca_bold_text">Selected Tags:</span>
                <span class="wca_help" title="This section contains tags that is selected for post.">i</span>
            </p>
            <div id="wca_tag_assign" class="wca_tag_container"></div>
            <p>
                <span class="wca_bold_text">Suggested Tags:</span>
                <span class="wca_help" title="This section contains tags that is suggested by Wiki CS Annotation Plugin.">i</span>
            </p>
            <div id="wca_tag_suggestions" class="wca_tag_container"></div>
            <p>
                <br/><input type="button" id="btn_select_all_suggested" class="button-secondary" style="display: none;" onclick="wca_select_all_suggested_tags();" value="Select All Suggested Tags" />
            </p>
            <p>
                <input type="button" class="button-secondary" onclick="wca_get_suggested_tags();" value="Get Tag Suggestions" />
            </p>
        </div>
        <div class="wca_right_metabox">
            <p>
                <label for="amount">&rho;NA:</label>
                <input type="text" id="amount" readonly style="width:40px; border: 0;" />
            </p>
            <p>Many tags</p>
            <div id="wca_slider_vertical" style="height:200px; width: 8px; margin: 0px auto;"></div>
            <p>Few tags</p>
            <p><input type="button" class="button-secondary" onclick="wca_reset_rhoNA();" value="Reset" /></p>
        </div>
        <div style="clear:both"></div>
    </div>
    <script type="text/javascript">wca_pruning_slider();</script>
    <script type="text/javascript">wca_redisplay_tags();</script>
<?php
}

/**
 * runs when the author save or publish post
 */
function wca_savetags($post_id, $post){
    if ($post->post_type == 'revision') return;
    if (!isset($_POST['wca_tag_list'])) return;
    $taglist = $_POST['wca_tag_list'];
    $tags = explode(', ', $taglist);
    if (strlen(trim($taglist)) > 0 && is_array($tags) && sizeof($tags) > 0) {
        // sanitize
        $tags = array_map('sanitize_text_field', $tags);
        // set post tags
        wp_set_post_tags($post_id, $tags);
    }
    else{
        wp_set_post_tags($post_id, array());
    }
}

/**
 * runs when the author requests tags for their post
 */
function wca_get_suggested_tags(){

    global $wca_plugin_dir_path;

    if(!function_exists('wca_anchor_parsing')){
        require_once($wca_plugin_dir_path . 'includes/tagme-implementation.php');
    }

    /* Check nonce */
    check_ajax_referer( 'wca_meta_box_nonce', 'security' );

    if(!isset($_POST['rhoNA'])) $rhoNA = 0.2;
    else $rhoNA = strip_tags($_POST['rhoNA']);

    $text_content = strip_tags($_POST['text_content']);

    $tags = wca_get_tag_suggestion($text_content, $rhoNA);

    if (count($tags) == 0) wp_die('No Tags');

    echo json_encode($tags);

    wp_die();
}

/**
 * add link on pre save or publish post
 */
function wca_link_content_filter($content){
    if (!isset($_POST['wca_anchor_sense_list'])) return $content;
    
    $anchor_sense_list = $_POST['wca_anchor_sense_list'];
    $anchor_senses = explode(', ', $anchor_sense_list);
    foreach($anchor_senses as $anchor_sense){
        
        list($anchor_text, $sense_title) = explode('|', $anchor_sense);

        $idx_found = stripos($content, $anchor_text);
        if($idx_found === false) continue;
        $current_anchor_text = substr($content, $idx_found, strlen($anchor_text));

        $link = "https://id.wikipedia.org/wiki/" . $sense_title;
        $desc = str_replace("_", " ", $sense_title);

        $url = "<a href=\"$link\" title=\"$desc\" rel=\"nofollow\" target=\"_blank\" >";
        $url .= $current_anchor_text . "</a>";

        $regEx = '\'(?!((<.*?)|(<a.*?)))(\b'. $anchor_text . '\b)(?!(([^<>]*?)>)|([^>]*?</a>))\'si';
        $content = preg_replace($regEx, $url, $content);
    }

    return $content;
}

/**
 * load JS and CSS on the post.php or post-new.php page
 */
function wca_load_meta_box_scripts(){
    $wca_plugin_url = plugin_dir_url( __FILE__ );
    //Load CSS
    wp_enqueue_style('wca_meta_box_style', $wca_plugin_url .'css/wca-meta-box.css', false, wca_get_plugin_version());
    //load CSS JQuery UI
    wp_enqueue_style('wca_slider_style', $wca_plugin_url .'css/wca_jquery-ui.min.css', false, wca_get_plugin_version());
    //Load JS
    wp_enqueue_script('wca_meta_box_js', $wca_plugin_url . 'js/wca-meta-box.js', array('jquery', 'jquery-ui-slider'), wca_get_plugin_version());
    //Localize script
    wp_localize_script( 'wca_meta_box_js', 'ajax_object', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'wca_plugin_url' => plugin_dir_url( __FILE__ ),
        'ajax_meta_box_nonce' => wp_create_nonce('wca_meta_box_nonce')
    ) );
}

/**
 * runs when the author import xml file from setting page
 */
function wca_import_anchor_data(){

    global $wca_plugin_dir_path;

    check_ajax_referer( 'wca_import_nonce', 'security' );

    if(!function_exists('import_anchor_data')){
        require_once($wca_plugin_dir_path . 'includes/import-anchor-data.php');
    }

    if(!isset ($_POST['chunk_location'])) wp_die();

    $xml_chunk_file = strip_tags($_POST['chunk_location']);

    $imported = import_anchor_data($wca_plugin_dir_path . 'tmp/' . $xml_chunk_file);
    echo $imported;

    // delete file
    unlink($wca_plugin_dir_path . 'tmp/' . $xml_chunk_file);

    wp_die();
}

/**
 * drop tables when uninstall
 * hooked via register_uninstall_hook
 */
function wca_uninstall_wiki_cs_annotation() {
    global $wpdb;
    //Remove our table (if it exists)
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wca_in_page");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wca_anchor_sense_map");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wca_page_wiki");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wca_anchor");
}

register_uninstall_hook(__FILE__, 'wca_uninstall_wiki_cs_annotation');

?>