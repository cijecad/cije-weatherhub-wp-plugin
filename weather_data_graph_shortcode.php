<?php
// Function to generate shortcode for line graph with filters
function weather_graph_shortcode() {
    // Enqueue necessary scripts and styles for graph
    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array('jquery'), null, true);
    wp_enqueue_script('weather-graph-js', plugins_url('/weather-graph.js', __FILE__), array('jquery', 'chart-js'), null, true);

    // Output HTML for dropdowns and canvas
    ob_start();
    ?>
    <div class="weather-graph-filter">
        <label for="weather-station">Select Weather Station(s):</label>
        <select id="weather-station" multiple></select>
        <br>
        <label for="y-axis-measure">Select Y Axis Measure:</label>
        <select id="y-axis-measure">
            <option value="temperature">Temperature</option>
            <option value="humidity">Humidity</option>
            <option value="pressure">Pressure</option>
            <option value="wind_speed">Wind Speed</option>
        </select>
        <br>
        <label for="x-axis-time">Select Time Period:</label>
        <select id="x-axis-time">
            <option value="day">Last Day</option>
            <option value="week">Last Week</option>
            <option value="month">Last Month</option>
            <option value="year">Last Year</option>
        </select>
    </div>
    <canvas id="weather-graph" width="800" height="400"></canvas>
    <?php
    return ob_get_clean();
}
add_shortcode('weather_graph', 'weather_graph_shortcode');

// Enqueue JavaScript for processing the graph data and rendering the chart
function enqueue_weather_graph_scripts() {
    wp_enqueue_script('weather-graph-handler', plugins_url('/weather-graph-handler.js', __FILE__), array('jquery', 'chart-js'), null, true);
    wp_localize_script('weather-graph-handler', 'weatherGraphSettings', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'enqueue_weather_graph_scripts');

// Function to fetch weather data for graph
function fetch_weather_graph_data() {
    global $wpdb;

    // Get filter parameters from AJAX request
    $station_ids = isset($_POST['station_ids']) ? array_map('sanitize_text_field', $_POST['station_ids']) : array();
    $y_axis_measure = isset($_POST['y_axis_measure']) ? sanitize_text_field($_POST['y_axis_measure']) : 'temperature';
    $x_axis_time = isset($_POST['x_axis_time']) ? sanitize_text_field($_POST['x_axis_time']) : 'day';

    // Define the time range based on the selected period
    $time_condition = '';
    switch ($x_axis_time) {
        case 'day':
            $time_condition = "AND datetime >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
            break;
        case 'week':
            $time_condition = "AND datetime >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
            break;
        case 'month':
            $time_condition = "AND datetime >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            break;
        case 'year':
            $time_condition = "AND datetime >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            break;
    }

    // Prepare the SQL query to get weather data
    $data_table = $wpdb->prefix . 'weather_data';
    $station_condition = count($station_ids) > 0 ? "AND station_id IN ('" . implode("','", $station_ids) . "')" : '';

    $query = "
        SELECT station_id, $y_axis_measure, datetime
        FROM $data_table
        WHERE 1=1
        $time_condition
        $station_condition
        ORDER BY datetime ASC
    ";

    $results = $wpdb->get_results($query);

    if ($results) {
        wp_send_json_success($results);
    } else {
        wp_send_json_error('No data found');
    }

    wp_die(); // Required to terminate AJAX request properly
}

// Handle AJAX request for fetching graph data
add_action('wp_ajax_fetch_weather_graph_data', 'fetch_weather_graph_data');
add_action('wp_ajax_nopriv_fetch_weather_graph_data', 'fetch_weather_graph_data');
?>
