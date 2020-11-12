<?php
/**
 * Plugin Name: Project Panorama Checklists
 * Plugin URI: http://www.projectpanorama.com
 * Description: Change tasks from % complete to checkboxes
 * Version: 1.5
 * Author: SnapOrbital
 * Author URI: http://www.projectpanorama.com
 * License: GPL2
 * Text Domain: psp_projects
 */

define( 'PSP_CHECK_VER', '1.5' );

function psp_checklists_assets() {

	if( function_exists( 'update_sub_field' ) ) {
	    wp_register_script( 'psp-checklists', plugins_url() . '/panorama-checklists/assets/js/psp-checklists-acf5.js', array( 'jquery' ), PSP_CHECK_VER, false );
	} else {
	    wp_register_script( 'psp-checklists', plugins_url() . '/panorama-checklists/assets/js/psp-checklists.js', array( 'jquery'), PSP_CHECK_VER, false );
	}

    wp_register_style( 'psp-checklists', plugins_url() . '/panorama-checklists/assets/css/psp-checklists.css' );

    wp_enqueue_script( 'psp-checklists') ;
    wp_enqueue_style( 'psp-checklists' );

}

add_filter( 'psp_phase_fields', 'psp_add_checklist_switch_to_phases', 1, 1 );
function psp_add_checklist_switch_to_phases( $fields ) {

	$checkbox_field = array(
        'key' 		=> 'field_5436e8a5e06c9',
        'label' 	=> __('Tasks are checklists','psp_projects'),
        'name' 		=> 'phase_tasks_as_checklist',
        'type'	 	=> 'checkbox',
        'choices' 	=> array (
            'Yes' 		=> __( 'Yes', 'psp_projects' ),
        ),
        'default_value' => '',
        'layout' 		=> 'vertical',
	);

	$new_fields = array();
	$sub_fields = array();

	foreach( $fields['fields'] as $field ) {

		if( $field['name'] == 'phases' ) {

			foreach( $field['sub_fields'] as $sub_field ) {

				if( $sub_field['name'] == 'tasks' ) {
					$sub_fields[] = $checkbox_field;
				}

				$sub_fields[] = $sub_field;

			}

			$field['sub_fields'] = $sub_fields;

		}

		$new_fields[] = $field;

	}

	$fields['fields'] = $new_fields;

	return $fields;

}


// Enqeue All
add_action( 'admin_enqueue_scripts', 'psp_checklists_assets', 9999 );

add_action( 'psp_head', 'psp_checklist_frontend_assets', 99999 );
function psp_checklist_frontend_assets() {

	psp_register_script( 'psp-checklists-front', plugins_url() . '/panorama-checklists/assets/js/psp-checklist-front.js', array( 'jquery' ), PSP_CHECK_VER, false );
	psp_register_style( 'psp-checklists-front', plugins_url() . '/panorama-checklists/assets/css/psp-checklist-front.css', null, PSP_CHECK_VER, false );

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

add_action( 'psp_before_task_name', 'psp_add_checklist_to_tasks', 10, 5 );
function psp_add_checklist_to_tasks( $post_id, $phase_index, $task_id, $phases, $phase ) {

	echo '<span class="psp-checklist-box"></span>';

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

require 'vendor/plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/3pointross/psp-checklists',
	__FILE__,
	'psp-checklists'
);

//Optional: Set the branch that contains the stable release.
$myUpdateChecker->setBranch('master');
