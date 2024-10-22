<?php
// Function to output the weather graph shortcode
function weather_graph_shortcode($atts) {
    ob_start();
    ?>
    <div>
        <label for="weather-station">Select Weather Station:</label>
        <select id="weather-station">
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

    // Fetch data from the database
    // This is a placeholder query, you need to implement the actual data fetching logic
    $query = $wpdb->prepare("
        SELECT datetime, $y_axis_measure
        FROM {$wpdb->prefix}weather_data
        WHERE station_id IN (" . implode(',', array_fill(0, count($station_ids), '%d')) . ")
        $time_condition
        ORDER BY datetime ASC
    ", $station_ids);

    $results = $wpdb->get_results($query);

    // Prepare data for the response
    $data = array(
        'labels' => array(),
        'datasets' => array(
            array(
                'label' => ucfirst($y_axis_measure),
                'data' => array()
            )
        )
    );

    foreach ($results as $row) {
        $data['labels'][] = $row->datetime;
        $data['datasets'][0]['data'][] = $row->$y_axis_measure;
    }

    wp_send_json_success($data);
}