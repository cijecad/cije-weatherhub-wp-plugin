<?php
// Add the admin menu and settings page
add_action('admin_menu', 'weather_graph_plugin_menu');

function weather_graph_plugin_menu() {
    add_options_page(
        'Weather Hub Plugin Settings', // Page title
        'Weather Hub',                 // Menu title
        'manage_options',                // Capability
        'weather-hub-plugin',          // Menu slug
        'weather_hub_plugin_settings_page' // Callback function
    );
}

function weather_graph_plugin_settings_page() {
    ?>
    <div class="wrap">
        <h1>Weather Graph </h1>
        <p>To display the weather graph, use the following shortcode:</p>
        <pre><code>[weather_graph]</code></pre>
        <p>Insert this shortcode into any post or page where you want the graph to appear.</p>
        <h1>Weather Map </h1>
        <p>To display the weather map, use the following shortcode:</p>
        <pre><code>[weather_map]</code></pre>
        <p>Insert this shortcode into any post or page where you want the weather data map to appear.</p>
        <h1>Weather Station Registration </h1>
        <p>To display the weather station registration form, use the following shortcode:</p>
        <pre><code>[register_station]</code></pre>
        <p>Insert this shortcode into any post or page where you want the weather station registration form to appear.</p>
    </div>
    <?php
}
?>