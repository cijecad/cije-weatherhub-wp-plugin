<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) exit;
// Set the default timezone
date_default_timezone_set('America/Denver');

// Function to output the weather graph shortcode
function weather_graph_shortcode($atts) {
    // Log a message to confirm the shortcode is being called
    error_log('weather_graph_shortcode called');

// Enqueue Chart.js and date adapter
wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), null, true);
wp_enqueue_script('chartjs-adapter-date-fns', 'https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns', array('chart-js'), null, true);

// Enqueue the JavaScript file for handling the graph
$version = filemtime(plugin_dir_path(__FILE__) . '../assets/js/weather-graph-handler.js');
wp_enqueue_script('weather-graph-handler-js', plugins_url('../assets/js/weather-graph-handler.js', __FILE__), array('jquery', 'chart-js', 'chartjs-adapter-date-fns'), $version, true);

    // Localize script to pass AJAX URL and other settings
    wp_localize_script('weather-graph-handler-js', 'weatherGraphSettings', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));

    ob_start();
    ?>
<div>
    <!-- Existing dropdowns and canvas -->
    <label for="weather-station">Select Weather Station:</label>
    <select id="weather-station">
        <option value="" disabled selected>Select a station</option>
        <!-- Options will be populated dynamically -->
    </select>

    <label for="y-axis-measure">Select Y-Axis Measure:</label>
    <select id="y-axis-measure">
        <option value="temperature">Temperature</option>
        <option value="humidity">Humidity</option>
        <option value="pressure">Pressure</option>
        <option value="wind_speed">Wind Speed</option>
        <option value="precipitation">Precipitation</option>
    </select>

    <label for="time-range">Select Time Range:</label>
    <select id="time-range">
        <option value="24_hours">Past 24 Hours</option>
        <option value="week">Past Week</option>
        <option value="month">Past Month</option>
        <option value="year">Past Year</option>
    </select>

    <canvas id="weather-graph" width="400" height="200"></canvas>
    <div id="debug-output"></div> <!-- Debug output div -->
</div>

    <?php
    return ob_get_clean();
}

// New function to fetch weather stations for the graph
if (!function_exists('fetch_weather_stations_for_graph')) {
    function fetch_weather_stations_for_graph() {
        global $wpdb;

        // Log a message to confirm the function is being called
        error_log('fetch_weather_stations_for_graph called');

        // Fetch weather stations from the database
        $results = $wpdb->get_results("
            SELECT station_id, station_name
            FROM {$wpdb->prefix}weather_stations
        ");

        // Log the results
        error_log('Weather stations fetched: ' . print_r($results, true));

        if ($results) {
            wp_send_json_success($results);
        } else {
            wp_send_json_error('Failed to fetch weather stations');
        }
    }
}

// New function to fetch weather data for the graph
if (!function_exists('fetch_weather_data_for_graph')) {
    function fetch_weather_data_for_graph() {
    // Retrieve parameters from AJAX request
    $station_id = isset($_POST['station_id']) ? intval($_POST['station_id']) : 0;
    $measure = isset($_POST['measure']) ? sanitize_text_field($_POST['measure']) : '';
    $time_range = isset($_POST['time_range']) ? sanitize_text_field($_POST['time_range']) : '';

    // Validate inputs
    if ($station_id === 0 || empty($measure) || empty($time_range)) {
        wp_send_json_error('Invalid parameters');
        exit;
    }

    // Calculate the start date based on the time range
    $current_time = date('Y-m-d H:i:s');
    if ($time_range === '24_hours') {
        $start_date = date('Y-m-d H:i:s', strtotime('-24 hours', strtotime($current_time)));
    } elseif ($time_range === '7_days') {
        $start_date = date('Y-m-d H:i:s', strtotime('-7 days', strtotime($current_time)));
    } else {
        $start_date = '1970-01-01 00:00:00'; // Default to earliest date
    }

    error_log("Start date for time range (America/Denver): $start_date");

    global $wpdb;
    $measure = esc_sql($measure);

    $sql = $wpdb->prepare(
        "
        SELECT date_time, `$measure`
        FROM {$wpdb->prefix}weather_data
        WHERE station_id = %d AND date_time >= %s
        ORDER BY date_time ASC
        ",
        $station_id,
        $start_date
    );

    error_log("Query: \n$sql");

    $results = $wpdb->get_results($sql);

    error_log('Weather data fetched: ' . print_r($results, true));

    if ($results) {
        wp_send_json_success($results);
    } else {
        wp_send_json_error('No data found for the specified parameters');
    }

    exit; // Ensure no further output is sent
}
}


add_action('wp_ajax_fetch_weather_stations_for_graph', 'fetch_weather_stations_for_graph');
add_action('wp_ajax_nopriv_fetch_weather_stations_for_graph', 'fetch_weather_stations_for_graph');
add_action('wp_ajax_fetch_weather_data_for_graph', 'fetch_weather_data_for_graph');
add_action('wp_ajax_nopriv_fetch_weather_data_for_graph', 'fetch_weather_data_for_graph');
add_shortcode('weather_graph', 'weather_graph_shortcode');
?>