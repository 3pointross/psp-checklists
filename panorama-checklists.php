<?php

/**
 * Plugin Name: Project Panorama Checklists
 * Plugin URI: http://www.projectpanorama.com
 * Description: Change tasks from % complete to checkboxes
 * Version: 1.3
 * Author: 37 MEDIA
 * Author URI: http://www.projectpanorama.com
 * License: GPL2
 * Text Domain: psp_projects
 */

function psp_checklists_assets() {

	if( function_exists( 'update_sub_field' ) ) {

	    wp_register_script( 'psp-checklists', plugins_url() . '/panorama-checklists/assets/js/psp-checklists-acf5.js', array( 'jquery' ), '1.2', false );


	} else {

	    wp_register_script( 'psp-checklists', plugins_url() . '/panorama-checklists/assets/js/psp-checklists.js', array( 'jquery'), '1.2', false );

	}

    wp_register_style( 'psp-checklists', plugins_url() . '/panorama-checklists/assets/css/psp-checklists.css' );

    wp_enqueue_script( 'psp-checklists') ;
    wp_enqueue_style( 'psp-checklists' );

}

add_filter( 'psp_phase_fields', 'psp_add_checklist_switch_to_phases' );
function psp_add_checklist_switch_to_phases( $fields ) {

	$checkbox_field = array(
        'key' => 'field_5436e8a5e06c9',
        'label' => __('Tasks are checklists','psp_projects'),
        'name' => 'phase_tasks_as_checklist',
        'type' => 'checkbox',
        'choices' => array (
            'Yes' => 'Yes',
        ),
        'default_value' => '',
        'layout' => 'vertical',

	);

	$fields[ 'fields' ][ 1] [ 'sub_fields' ][] = $checkbox_field;

	return $fields;

}


// Enqeue All
add_action( 'admin_enqueue_scripts', 'psp_checklists_assets', 9999 );

add_action( 'psp_head', 'psp_checklist_frontend_assets' );
function psp_checklist_frontend_assets() {

	if( current_user_can( 'edit_psp_project' ) ) {

		psp_register_script( 'psp-checklists-front', plugins_url() . '/panorama-checklists/assets/js/psp-checklist-front.js', array( 'jquery' ), '1.0', false );
		psp_register_style( 'psp-checklists-front', plugins_url() . '/panorama-checklists/assets/css/psp-checklist-front.css', null, '1.0', false );

	}


}

add_action( 'psp_after_dashboard_phase_tasks', 'psp_checklist_add_hidden_field_dashboard', 10, 2 );
function psp_checklist_add_hidden_field_dashboard( $phase_id, $post_id ) {

	$phases 	= get_field( 'phases', $post_id );

	if( !isset( $phases[ $phase_id ][ 'phase_tasks_as_checklist'][ 0 ] ) ) return;

	$checklist 	= ( $phases[ $phase_id ][ 'phase_tasks_as_checklist'][ 0 ] == 'Yes' ? true : false );

	if( $checklist ) {

		echo '<input type="hidden" class="psp-phase-meta-indicator" value="Yes" name="psp-phase-meta-indicator">';

	}

}

add_action( 'psp_after_individual_phase_wrapper', 'psp_checklist_add_hidden_field' );
function psp_checklist_add_hidden_field() {

	$checklist = get_sub_field( 'phase_tasks_as_checklist' );

	if( ( !empty( $checklist ) ) && ( $checklist[ 0 ] == 'Yes') ) {

		echo '<input type="hidden" class="psp-phase-meta-indicator" value="Yes" name="psp-phase-meta-indicator">';

	}

}

function psp_array_key_exists_wildcard ( $array, $search, $return = '' ) {
    $search = str_replace( '\*', '.*?', preg_quote( $search, '/' ) );
    $result = preg_grep( '/^' . $search . '$/i', array_keys( $array ) );
    if ( $return == 'key-value' )
        return array_intersect_key( $array, array_flip( $result ) );
    return $result;
}

function psp_array_value_exists_wildcard ( $array, $search, $return = '' ) {
    $search = str_replace( '\*', '.*?', preg_quote( $search, '/' ) );
    $result = preg_grep( '/^' . $search . '$/i', array_values( $array ) );
    if ( $return == 'key-value' )
        return array_intersect( $array, $result );
    return $result;
}

/* Update Script */


$api_url = 'http://www.projectpanorama.com/addons/updates';
$plugin_slug = basename( dirname( __FILE__ ) );


// Take over the update check
// add_filter('pre_set_site_transient_update_plugins', 'psp_checklists_check_for_plugin_update');

function psp_checklists_check_for_plugin_update($checked_data) {
    global $api_url, $plugin_slug;

    if ( empty( $checked_data->checked ) )
        return $checked_data;

    $request_args = array(
        'slug' 		=> $plugin_slug,
        'version' 	=> $checked_data->checked[ $plugin_slug .'/'. $plugin_slug . '.php' ],
    );

    $request_string = psp_checklists_prepare_request( 'basic_check', $request_args );

    // Start checking for an update
    $raw_response 	= wp_remote_post($api_url, $request_string);

    if ( !is_wp_error( $raw_response ) && ( $raw_response[ 'response' ][ 'code' ] == 200 ) )
        $response = unserialize($raw_response['body']);

    if (is_object($response) && !empty($response)) // Feed the update data into WP updater
        $checked_data->response[$plugin_slug .'/'. $plugin_slug .'.php'] = $response;

    return $checked_data;
}


// Take over the Plugin info screen
// add_filter('plugins_api', 'psp_checklists_api_call', 10, 3);

function psp_checklists_api_call($def, $action, $args) {

    global $plugin_slug, $api_url;

    if ($args->slug != $plugin_slug)
        return false;

    // Get the current version
    $plugin_info = get_site_transient('update_plugins');
    $current_version = $plugin_info->checked[$plugin_slug .'/'. $plugin_slug .'.php'];
    $args->version = $current_version;

    $request_string = psp_checklist_prepare_request($action, $args);

    $request = wp_remote_post($api_url, $request_string);

    if (is_wp_error($request)) {
        $res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
    } else {
        $res = unserialize($request['body']);

        if ($res === false)
            $res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
    }

    return $res;
}


function psp_checklists_prepare_request($action, $args) {
    global $wp_version;

    return array(
        'body' => array(
            'action' => $action,
            'request' => serialize($args),
            'api-key' => md5(get_bloginfo('url'))
        ),
        'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
    );
}
