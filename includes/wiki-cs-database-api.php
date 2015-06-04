<?php

/**
 * Get column and data type format for wp_wca_anchor
 */
function wca_get_anchor_table_columns(){
    return array(
        'anchor_id'=> '%d',
        'anchor_text'=>'%s',
        'link_a'=>'%d',
        'freq_a'=>'%d',
    );
}

/**
 * Get column and data type format for wp_wca_page_wiki
 */
function wca_get_page_wiki_table_columns(){
    return array(
        'page_wiki_id'=> '%d',
        'page_wiki_title'=>'%s',
    );
}

/**
 * Get column and data type format for wp_wca_anchor_sense_map
 */
function wca_get_anchor_sense_map_table_columns(){
    return array(
        'map_id'=> '%d',
        'anchor_id'=> '%d',
        'sense_title'=>'%s',
        'counter'=>'%d',
    );
}

/**
 * Get column and data type format for wp_wca_in_page
 */
function wca_get_in_page_table_columns(){
    return array(
        'in_page_id'=> '%d',
        'map_id'=> '%d',
        'page_wiki_id'=>'%d',
    );
}

/**
 * Inserts an anchor entry into the wp_wca_anchor table
 *
 *@param $data array - An array of key => value pairs to be inserted
 *@return int The anchor ID of the created anchor entry. Or WP_Error or false on failure.
*/
function wca_insert_anchor( $data=array() ){
    global $wpdb;

    //Initialise column format array
    $column_formats = wca_get_anchor_table_columns();

    //Force fields to lower case
    $data = array_change_key_case ( $data );

    //White list columns
    $data = array_intersect_key($data, $column_formats);

    //Reorder $column_formats to match the order of columns given in $data
    $data_keys = array_keys($data);
    $column_formats = array_merge(array_flip($data_keys), $column_formats);

    $wpdb->insert($wpdb->prefix."wca_anchor", $data, $column_formats);

    return $wpdb->insert_id;
}

/**
 * Inserts an page wiki entry into the wp_wca_page_wiki table
 *
 *@param $data array - An array of key => value pairs to be inserted
 *@return int The page wiki ID of the created page wiki entry. Or WP_Error or false on failure.
*/
function wca_insert_page_wiki( $data=array() ){
    global $wpdb;

    //Initialise column format array
    $column_formats = wca_get_page_wiki_table_columns();

    //Force fields to lower case
    $data = array_change_key_case ( $data );

    //White list columns
    $data = array_intersect_key($data, $column_formats);

    //Reorder $column_formats to match the order of columns given in $data
    $data_keys = array_keys($data);
    $column_formats = array_merge(array_flip($data_keys), $column_formats);

    $wpdb->insert($wpdb->prefix."wca_page_wiki", $data, $column_formats);

    return $wpdb->insert_id;
}

/**
 * Inserts an anchor sense map entry into the wp_wca_anchor_sense_map table
 *
 *@param $data array - An array of key => value pairs to be inserted
 *@return int The anchor sense map ID of the created map entry. Or WP_Error or false on failure.
*/
function wca_insert_anchor_sense_map( $data=array() ){
    global $wpdb;

    //Initialise column format array
    $column_formats = wca_get_anchor_sense_map_table_columns();

    //Force fields to lower case
    $data = array_change_key_case ( $data );

    //White list columns
    $data = array_intersect_key($data, $column_formats);

    //Reorder $column_formats to match the order of columns given in $data
    $data_keys = array_keys($data);
    $column_formats = array_merge(array_flip($data_keys), $column_formats);

    $wpdb->insert($wpdb->prefix."wca_anchor_sense_map", $data, $column_formats);

    return $wpdb->insert_id;
}

/**
 * Inserts an in page entry into the wp_wca_in_page table
 *
 *@param $data array - An array of key => value pairs to be inserted
 *@return int The in page ID of the created in page entry. Or WP_Error or false on failure.
*/
function wca_insert_in_page( $data=array() ){
    global $wpdb;

    //Initialise column format array
    $column_formats = wca_get_in_page_table_columns();

    //Force fields to lower case
    $data = array_change_key_case ( $data );

    //White list columns
    $data = array_intersect_key($data, $column_formats);

    //Reorder $column_formats to match the order of columns given in $data
    $data_keys = array_keys($data);
    $column_formats = array_merge(array_flip($data_keys), $column_formats);

    $wpdb->insert($wpdb->prefix."wca_in_page", $data, $column_formats);

    return $wpdb->insert_id;
}

/**
 * Updates an anchor entry with supplied data
 *
 *@param $anchor_id int ID of the anchor entry to be updated
 *@param $data array An array of column=>value pairs to be updated
 *@return bool Whether the log was successfully updated.
*/
function wca_update_anchor( $anchor_id, $data=array() ){
    global $wpdb;

    //anchor ID must be positive integer
    $anchor_id = absint($anchor_id);
    if( empty($anchor_id) )
         return false;

    //Initialise column format array
    $column_formats = wca_get_anchor_table_columns();

    //Force fields to lower case
    $data = array_change_key_case ( $data );

    //White list columns
    $data = array_intersect_key($data, $column_formats);

    //Reorder $column_formats to match the order of columns given in $data
    $data_keys = array_keys($data);
    $column_formats = array_merge(array_flip($data_keys), $column_formats);

    if ( false === $wpdb->update($wpdb->prefix."wca_anchor", $data, array('anchor_id'=>$anchor_id), $column_formats) ) {
         return false;
    }

    return true;
}

/**
 * Updates an page wiki entry with supplied data
 *
 *@param $page_wiki_id int ID of the page wiki entry to be updated
 *@param $data array An array of column=>value pairs to be updated
 *@return bool Whether the log was successfully updated.
*/
function wca_update_page_wiki( $page_wiki_id, $data=array() ){
    global $wpdb;

    //page wiki ID must be positive integer
    $page_wiki_id = absint($page_wiki_id);
    if( empty($page_wiki_id) )
         return false;

    //Initialise column format array
    $column_formats = wca_get_page_wiki_table_columns();

    //Force fields to lower case
    $data = array_change_key_case ( $data );

    //White list columns
    $data = array_intersect_key($data, $column_formats);

    //Reorder $column_formats to match the order of columns given in $data
    $data_keys = array_keys($data);
    $column_formats = array_merge(array_flip($data_keys), $column_formats);

    if ( false === $wpdb->update($wpdb->prefix."wca_page_wiki", $data, array('page_wiki_id'=>$page_wiki_id), $column_formats) ) {
         return false;
    }

    return true;
}

/**
 * Updates an anchor sense map entry with supplied data
 *
 *@param $map_id int ID of the anchor sense map entry to be updated
 *@param $data array An array of column=>value pairs to be updated
 *@return bool Whether the log was successfully updated.
*/
function wca_update_anchor_sense_map( $map_id, $data=array() ){
    global $wpdb;

    //map ID must be positive integer
    $map_id = absint($map_id);
    if( empty($map_id) )
         return false;

    //Initialise column format array
    $column_formats = wca_get_anchor_sense_map_table_columns();

    //Force fields to lower case
    $data = array_change_key_case ( $data );

    //White list columns
    $data = array_intersect_key($data, $column_formats);

    //Reorder $column_formats to match the order of columns given in $data
    $data_keys = array_keys($data);
    $column_formats = array_merge(array_flip($data_keys), $column_formats);

    if ( false === $wpdb->update($wpdb->prefix."wca_anchor_sense_map", $data, array('map_id'=>$map_id), $column_formats) ) {
         return false;
    }

    return true;
}

/**
 * Updates an in page entry with supplied data
 *
 *@param $in_page_id int ID of the in page entry to be updated
 *@param $data array An array of column=>value pairs to be updated
 *@return bool Whether the log was successfully updated.
*/
function wca_update_in_page( $in_page_id, $data=array() ){
    global $wpdb;

    //in page ID must be positive integer
    $in_page_id = absint($in_page_id);
    if( empty($in_page_id) )
         return false;

    //Initialise column format array
    $column_formats = wca_get_in_page_table_columns();

    //Force fields to lower case
    $data = array_change_key_case ( $data );

    //White list columns
    $data = array_intersect_key($data, $column_formats);

    //Reorder $column_formats to match the order of columns given in $data
    $data_keys = array_keys($data);
    $column_formats = array_merge(array_flip($data_keys), $column_formats);

    if ( false === $wpdb->update($wpdb->prefix."wca_in_page", $data, array('in_page_id'=>$in_page_id), $column_formats) ) {
         return false;
    }

    return true;
}

/**
 * Deletes an anchor entry from the database
 *
 *@param $anchor_id int ID of the anchor entry to be deleted
 *@return bool Whether the log was successfully deleted.
*/
function wca_delete_anchor( $anchor_id ){
    global $wpdb;

    //anchor ID must be positive integer
    $anchor_id = absint($anchor_id);
    if( empty($anchor_id) )
         return false;

    $sql = $wpdb->prepare("DELETE from {$wpdb->prefix}wca_anchor WHERE anchor_id = %d", $anchor_id);

    if( !$wpdb->query( $sql ) )
         return false;

    return true;
}

/**
 * Deletes an page wiki entry from the database
 *
 *@param $page_wiki_id int ID of the page wiki entry to be deleted
 *@return bool Whether the log was successfully deleted.
*/
function wca_delete_page_wiki( $page_wiki_id ){
    global $wpdb;

    //page wiki ID must be positive integer
    $page_wiki_id = absint($page_wiki_id);
    if( empty($page_wiki_id) )
         return false;

    $sql = $wpdb->prepare("DELETE from {$wpdb->prefix}wca_page_wiki WHERE page_wiki_id = %d", $page_wiki_id);

    if( !$wpdb->query( $sql ) )
         return false;

    return true;
}

/**
 * Deletes an anchor sense map entry from the database
 *
 *@param $map_id int ID of the anchor sense map entry to be deleted
 *@return bool Whether the log was successfully deleted.
*/
function wca_delete_anchor_sense_map( $map_id ){
    global $wpdb;

    //map ID must be positive integer
    $map_id = absint($map_id);
    if( empty($map_id) )
         return false;

    $sql = $wpdb->prepare("DELETE from {$wpdb->prefix}wca_anchor_sense_map WHERE map_id = %d", $map_id);

    if( !$wpdb->query( $sql ) )
         return false;

    return true;
}

/**
 * Deletes an in page entry from the database
 *
 *@param $in_page_id int ID of the in page entry to be deleted
 *@return bool Whether the log was successfully deleted.
*/
function wca_delete_in_page( $in_page_id ){
    global $wpdb;

    //in page ID must be positive integer
    $in_page_id = absint($in_page_id);
    if( empty($in_page_id) )
         return false;

    $sql = $wpdb->prepare("DELETE from {$wpdb->prefix}wca_in_page WHERE in_page_id = %d", $in_page_id);

    if( !$wpdb->query( $sql ) )
         return false;

    return true;
}

/**
 * Retrieves all anchor entries from the database.
 *
 *@return array Array of anchor entries. False on error.
*/
function wca_get_anchors(){
    global $wpdb;

    /* sql */
    $sql = "SELECT * FROM {$wpdb->prefix}wca_anchor";

    /* Perform query */
    $anchors = $wpdb->get_results($sql);

    return $anchors;
}

/**
 * Retrieves anchor entry from the database according to anchor id.
 *
 *@param $anchor_id Anchor ID
 *@return array Array of anchor entry. False on error.
*/
function wca_get_anchor_by_anchor_id($anchor_id){
    global $wpdb;

    //anchor text must not be empty and must be positive integer
    $anchor_id = absint($anchor_id);
    if( empty($anchor_id) )
         return false;

    /* sql */
    $sql = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wca_anchor WHERE anchor_id=%d", $anchor_id);

    /* Perform query */
    $anchor_entry_array = $wpdb->get_results($sql);

    return $anchor_entry_array;
}

/**
 * Retrieves anchor entry from the database according to anchor text.
 *
 *@param $anchor_text Anchor Text
 *@return array Array of anchor entry. False on error.
*/
function wca_get_anchor_by_anchor_text($anchor_text){
    global $wpdb;

    //anchor text must not be empty and must be lowercase
    $anchor_text = strtolower($anchor_text);
    if( empty($anchor_text) )
         return false;

    /* sql */
    $sql = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wca_anchor WHERE anchor_text=%s", $anchor_text);

    /* Perform query */
    $anchor_entry_array = $wpdb->get_results($sql);

    return $anchor_entry_array;
}

/**
 * Retrieves anchor sense map entry from the database according to anchor id.
 *
 *@param $anchor_id Anchor ID
 *@return array Array of anchor map entry. False on error.
*/
function wca_get_anchor_sense_map_by_anchor_id($anchor_id){
    global $wpdb;

    //anchor ID must be positive integer
    $anchor_id = absint($anchor_id);
    if( empty($anchor_id) )
         return false;

    /* sql */
    $sql = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wca_anchor_sense_map WHERE anchor_id=%d", $anchor_id);

    /* Perform query */
    $maps = $wpdb->get_results($sql);

    return $maps;
}

/**
 * Retrieves anchor sense map entry from the database according to anchor id and sense title.
 *
 *@param $anchor_id Anchor ID and $sense_title Sense Title
 *@return array Array of anchor map entry. False on error.
*/
function wca_get_anchor_sense_map_by_anchor_id_and_sense_title($anchor_id, $sense_title){
    global $wpdb;

    //anchor ID must be positive integer
    $anchor_id = absint($anchor_id);
    if( empty($anchor_id) )
         return false;

    if( empty($sense_title) )
         return false;

    /* sql */
    $sql = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wca_anchor_sense_map WHERE anchor_id=%d AND BINARY sense_title=%s", $anchor_id, $sense_title);

    /* Perform query */
    $maps = $wpdb->get_results($sql);

    return $maps;
}

/**
 * Retrieves in page entry from the database according to map id.
 *
 *@param $map_id Anchor Sense Map ID
 *@return array Array of anchor map entry. False on error.
*/
function wca_get_in_page_by_map_id($map_id){
    global $wpdb;

    //map ID must be positive integer
    $map_id = absint($map_id);
    if( empty($map_id) )
         return false;

    /* sql */
    $sql = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wca_in_page WHERE map_id=%d", $map_id);

    /* Perform query */
    $in_pages = $wpdb->get_results($sql);

    return $in_pages;
}

/**
 * Retrieves in page entry from the database according to map id and page_wiki_id.
 *
 *@param $map_id Anchor Sense Map ID and $page_wiki_id Page Wiki ID
 *@return array Array of anchor map entry. False on error.
*/
function wca_get_in_page_by_map_id_and_page_wiki_id($map_id, $page_wiki_id){
    global $wpdb;

    //map ID must be positive integer
    $map_id = absint($map_id);
    if( empty($map_id) )
         return false;

    //page wiki ID must be positive integer
    $page_wiki_id = absint($page_wiki_id);
    if( empty($page_wiki_id) )
         return false;

    /* sql */
    $sql = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wca_in_page WHERE map_id=%d AND page_wiki_id=%d", $map_id, $page_wiki_id);

    /* Perform query */
    $in_pages = $wpdb->get_results($sql);

    return $in_pages;
}

/**
 * Retrieves pagw wiki entry from the database according to page wiki title.
 *
 *@param $page_wiki_title Page Wiki Title
 *@return array Array of page wiki entry. False on error.
*/
function wca_get_page_wiki_id_by_title($page_wiki_title){
    global $wpdb;

    //page wiki title must not be empty
    if( empty($page_wiki_title) )
         return false;

    /* sql */
    $sql = $wpdb->prepare("SELECT page_wiki_id FROM {$wpdb->prefix}wca_page_wiki WHERE BINARY page_wiki_title=%s", $page_wiki_title);

    /* Perform query */
    $page_wiki_id_array = $wpdb->get_results($sql);

    return $page_wiki_id_array;
}

/**
 * Retrieves page wiki count from the database.
 *
 *@return page wiki count. False on error.
*/
function wca_get_page_wiki_count(){
    global $wpdb;

    /* sql */
    $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}wca_page_wiki";

    /* Perform query */
    $page_wiki_count = $wpdb->get_var($sql);

    return $page_wiki_count;
}

?>