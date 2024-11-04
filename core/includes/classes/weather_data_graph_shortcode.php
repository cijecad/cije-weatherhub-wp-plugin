<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

// Function to output the weather graph shortcode
function weather_graph_shortcode($atts) {
    // Enqueue the Chart.js library
    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), null, true);

    // Enqueue the JavaScript file for handling the graph
    wp_enqueue_script('weather-graph-handler-js', plugins_url('../assets/js/weather-graph-handler.js', __FILE__), array('jquery', 'chart-js'), null, true);

    // Localize script to pass AJAX URL and other settings
    wp_localize_script('weather-graph-handler-js', 'weatherGraphSettings', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));

    ob_start();
    ?>
    <div>
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

        <label for="x-axis-time">Select X-Axis Time:</label>
        <select id="x-axis-time">
            <option value="24_hour">24 Hour</option>
            <option value="week">Last Week</option>
            <option value="month">Last Month</option>
            <option value="year">Last Year</option>
        </select>
    </div>
    <canvas id="weather-graph" width="800" height="400"></canvas>
    <?php
    return ob_get_clean();
}

// Function to fetch weather data for graph
function fetch_weather_graph_data() {
    global $wpdb;

    // Retrieve POST data
    $station_ids = isset($_POST['station_ids']) ? $_POST['station_ids'] : '';
    $y_axis_measure = isset($_POST['y_axis_measure']) ? $_POST['y_axis_measure'] : '';
    $x_axis_time = isset($_POST['x_axis_time']) ? $_POST['x_axis_time'] : '';

    // Validate required fields
    if (empty($station_ids) || empty($y_axis_measure) || empty($x_axis_time)) {
        wp_send_json_error('Missing required fields');
        return;
    }

    // Fetch weather data based on the selected parameters
    $table_name = $wpdb->prefix . 'weather_data';
    $query = $wpdb->prepare("
        SELECT date_time, $y_axis_measure 
        FROM $table_name 
        WHERE station_id IN (%s) 
        AND date_time >= DATE_SUB(NOW(), INTERVAL 1 %s)
        ORDER BY date_time ASC
    ", implode(',', $station_ids), strtoupper($x_axis_time));

    $results = $wpdb->get_results($query);

    if ($results) {
        $labels = array();
        $values = array();
        foreach ($results as $result) {
            $labels[] = $result->date_time;
            $values[] = $result->$y_axis_measure;
        }
        wp_send_json_success(array('labels' => $labels, 'values' => $values));
    } else {
        wp_send_json_error('No data found');
    }
}

// Function to fetch weather stations
function fetch_weather_stations_for_dropdown() {
    global $wpdb;

    // Fetch weather stations from the database
    $table_name = $wpdb->prefix . 'weather_stations';
    $stations = $wpdb->get_results("SELECT station_id, station_name FROM $table_name");

    if ($stations) {
        wp_send_json_success(array('stations' => $stations));
    } else {
        wp_send_json_error('Failed to fetch weather stations');
    }
}

add_action('wp_ajax_fetch_weather_graph_data', 'fetch_weather_graph_data');
add_action('wp_ajax_nopriv_fetch_weather_graph_data', 'fetch_weather_graph_data');
add_action('wp_ajax_fetch_weather_stations_for_dropdown', 'fetch_weather_stations_for_dropdown');
add_action('wp_ajax_nopriv_fetch_weather_stations_for_dropdown', 'fetch_weather_stations_for_dropdown');
add_shortcode('weather_graph', 'weather_graph_shortcode');