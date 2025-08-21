<?php
/*
Plugin Name:  COVERSE helper functions
Plugin URI:   https://coverse.org.au
Description:  Some helper functions and shortcodes for use on the COVERSE website. Requires Formidable Forms.
Version:      1.0.5
Requires at least: 6.2
Requires PHP: 7.4
Author:       Rado Faletič
Author URI:   https://radofaletic.com
License:      GPL v2 or later
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  coverse-helper-functions
Update URI:   https://raw.githubusercontent.com/coverseau/coverse-helper-functions/refs/heads/main/update-info.json
*/

/**
 * Plugin updater handler function.
 * Pings the Github repo that hosts the plugin to check for updates.
 */
function coverse_helper_functions_check_for_plugin_update($transient) {
// If no update transient or transient is empty, return.
if (empty($transient->checked)) {
  return $transient;
}

// Plugin slug, path to the main plugin file, and the URL of the update server
$plugin_slug = 'coverse-helper-functions/coverse-helper-functions.php';
$update_url = 'https://raw.githubusercontent.com/coverseau/coverse-helper-functions/refs/heads/main/update-info.json';

// Fetch update information from your server
$response = wp_remote_get($update_url);
if (is_wp_error($response)) {
  return $transient;
}

// Parse the JSON response (update_info.json must return the latest version details)
$update_info = json_decode(wp_remote_retrieve_body($response));

// If a new version is available, modify the transient to reflect the update
if (version_compare($transient->checked[$plugin_slug], $update_info->new_version, '<')) {
  $plugin_data = array(
	  'slug'        => 'coverse-helper-functions',
	  'plugin'      => $plugin_slug,
	  'new_version' => $update_info->new_version,
	  'url'         => $update_info->url,
	  'package'     => $update_info->package, // URL of the plugin zip file
	);
  $transient->response[ $plugin_slug ] = (object) $plugin_data;
}

return $transient;
}
add_filter('pre_set_site_transient_update_plugins', 'coverse_helper_functions_check_for_plugin_update');

register_activation_hook( __FILE__, 'coverse_helper_functions_activate');
function coverse_helper_functions_activate(){
	if (!is_plugin_active('formidable/formidable.php') && current_user_can('activate_plugins')) {
		wp_die('Sorry, but this plugin requires the <a href="https://wordpress.org/plugins/formidable/" target="_blank">Formidable Forms</a> plugin to be installed and active. <br><a href="' . admin_url('plugins.php') . '">« Return to Plugins</a>');
	}
}

// Combined total for two or more fields. This creates a new shortcode [fields-stats] for use on your page. Your shortcode will look like this: [fields-stats ids="x,y,z"]. Replace x, y, and z with the IDs of the fields you want to total.
function my_fields_stats($atts) {
	$defaults = array(
		'ids' => false,
	);
	$atts = array_merge($defaults, $atts);
	$ids = explode(',', $atts['ids']);
	unset( $atts['ids'] );

	$total = 0;
	foreach ( $ids as $id ) {
		$atts['id'] = $id;
		$total += FrmProStatisticsController::stats_shortcode( $atts );
	}
	return $total;
}
add_shortcode('fields-stats', 'my_fields_stats');

// Subtract one simple stat from another. This creates a new shortcode that allows you to subtract one stat from the other. The stats need to be the same type (e.g. total or count) and not have any filters or use any other parameters that would be specific to one stat but not the other.
// Usage: [subtract-fields total=358 removed=359 type=total].
// Replace 358 with the id of the field that holds the initial total and 359 with the id of the field that holds the amount being subtracted from the total.
function frm_subtract_fields ($atts) {
	$defaults = array(
		'total' => false,
		'removed'=> false,
	);
	$atts = array_merge($defaults, $atts);

	$total_id = $atts['total'];
	unset ($atts['total']);
	$removed_id = $atts['removed'];
	unset ($atts['removed']);

	$atts['id'] = $total_id;
	$total = FrmProStatisticsController::stats_shortcode( $atts );
	$atts['id'] = $removed_id;
	$removed = FrmProStatisticsController::stats_shortcode( $atts );

	return $total - $removed;
}
add_shortcode('subtract-fields', 'frm_subtract_fields');

// Percentage of a specific field value.
// Usage: [frm-percent id=x value="Option 1" without="Option 2"]
function frm_stats_percent($atts){
	$defaults = array(
		'id' => false, 'user_id' => false,
		'value' => false, 'without' => false, 'round' => 100, 'limit' => '', 'decimal' => 2
	);
	extract(shortcode_atts($defaults, $atts));
	if (!$id) return;
	$type = 'count';
	$value_count = FrmProStatisticsController::stats_shortcode(compact('id', 'type', 'value', 'limit'));
	$total_count = FrmProStatisticsController::stats_shortcode(compact('id', 'type', 'limit'));
	if ($without) {
		$value = $without;
		$without_count = FrmProStatisticsController::stats_shortcode(compact('id', 'type', 'value', 'limit'));
		$total_count -= $without_count;
	}
	return round((($value_count / $total_count) * 100), $round);
}
add_shortcode('frm-percent', 'frm_stats_percent');

// Percentage of a specific field value.
// Usage: [frm-percent-total id=x value="Option 1"]
function frm_stats_percent_total($atts){
	$defaults = array(
		'id' => false, 'user_id' => false,
		'value' => false, 'round' => 100, 'limit' => '', 'decimal' => 2
	);
	extract(shortcode_atts($defaults, $atts));
	if (!$id) return;
	$type = 'count';
	$value_count = FrmProStatisticsController::stats_shortcode(compact('id', 'type', 'value', 'limit'));
	$id = 133; // fudge to look at the 'biological sex' field for the total count
	$total_count = FrmProStatisticsController::stats_shortcode(compact('id', 'type', 'limit'));
	return round((($value_count / $total_count) * 100), $round);
}
add_shortcode('frm-percent-total', 'frm_stats_percent_total');

// Percentage not of a specific field value.
// Usage: [frm-percent-not-total id=x value="Option 1"]
function frm_stats_percent_not_total($atts){
	$defaults = array(
		'id' => false, 'user_id' => false,
		'value' => false, 'round' => 100, 'limit' => '', 'decimal' => 2
	);
	extract(shortcode_atts($defaults, $atts));
	if (!$id) return;
	$type = 'count';
	$value_count = FrmProStatisticsController::stats_shortcode(compact('id', 'type', 'value', 'limit'));
	$id = 133; // fudge to look at the 'biological sex' field for the total count
	$total_count = FrmProStatisticsController::stats_shortcode(compact('id', 'type', 'limit'));
	return round(((($total_count - $value_count) / $total_count) * 100), $round);
}
add_shortcode('frm-percent-not-total', 'frm_stats_percent_not_total');

// Subtract one simple stat from the current year to calculate 'age', and rounds to nearest integer. This creates a new shortcode that allows you to subtract one stat from a given year.
// Usage: [frm-age-from-years year=2022 id=359 type=average].
// Replace 2022 with the initial year value (or don't include it at all, and the current year will be used) and 359 with the id of the field that holds the amount being subtracted from the year.
function frm_stats_age_from_years ($atts) {
	$defaults = array(
		'year' => false,
		'id' => false
	);
	foreach ($defaults as $key => $value) {
		if (!$atts[$key]) {
			$atts[$key] = $value;
		}
	}
	
	if (!$atts['id']) return;
	$year = $atts['year'];
	unset ($atts['year']);
	if (!$year) {
		$year = (int)date('Y');
	}
	$years_calc = FrmProStatisticsController::stats_shortcode( $atts );
	return round($year - $years_calc);
}
add_shortcode('frm-age-from-years', 'frm_stats_age_from_years');
