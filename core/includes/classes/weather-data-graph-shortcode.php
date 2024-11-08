<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

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
        global $wpdb;

        // Log a message to confirm the function is being called
        error_log('fetch_weather_data_for_graph called');

        // Retrieve and sanitize input parameters
        $station_id = intval($_POST['station_id']);
        $measure = sanitize_text_field($_POST['measure']);
        $time_range = sanitize_text_field($_POST['time_range']);

        // Log the query parameters
        error_log("Query parameters: station_id={$station_id}, measure={$measure}, time_range={$time_range}");

        // Validate the measure against allowed columns
        $allowed_measures = ['temperature', 'humidity', 'pressure', 'wind_speed', 'precipitation'];
        if (!in_array($measure, $allowed_measures)) {
            wp_send_json_error('Invalid measure selected.');
            return;
        }

        // Determine the start date based on the selected time range
        switch ($time_range) {
            case '24_hours':
                $start_date = date('Y-m-d H:i:s', strtotime('-24 hours'));
                break;
            case 'week':
                $start_date = date('Y-m-d H:i:s', strtotime('-1 week'));
                break;
            case 'month':
                $start_date = date('Y-m-d H:i:s', strtotime('-1 month'));
                break;
            case 'year':
                $start_date = date('Y-m-d H:i:s', strtotime('-1 year'));
                break;
            case 'all_time':
            default:
                $start_date = '2000-01-01 00:00:00';
                break;
        }

        // Log the start date
        error_log("Start date for time range: {$start_date}");

        // Prepare the SQL query
        $table_name = $wpdb->prefix . 'weather_data';
        $query = $wpdb->prepare(
            "
            SELECT date_time, `$measure`
            FROM $table_name
            WHERE station_id = %d AND date_time >= %s
            ORDER BY date_time ASC
            ",
            $station_id,
            $start_date
        );

        // Log the query
        error_log('Query: ' . $query);

        // Execute the query
        $results = $wpdb->get_results($query);

        // Log the results
        error_log('Weather data fetched: ' . print_r($results, true));

        if ($results) {
            wp_send_json_success($results);
        } else {
            wp_send_json_error('No data found for the selected time range.');
        }
    }
}

add_action('wp_ajax_fetch_weather_stations_for_graph', 'fetch_weather_stations_for_graph');
add_action('wp_ajax_nopriv_fetch_weather_stations_for_graph', 'fetch_weather_stations_for_graph');
add_action('wp_ajax_fetch_weather_data_for_graph', 'fetch_weather_data_for_graph');
add_action('wp_ajax_nopriv_fetch_weather_data_for_graph', 'fetch_weather_data_for_graph');
add_shortcode('weather_graph', 'weather_graph_shortcode');
?>