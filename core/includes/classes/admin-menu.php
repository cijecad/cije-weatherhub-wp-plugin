<?php
// Add the admin menu and settings page
add_action('admin_menu', 'weather_graph_plugin_menu');

function weather_graph_plugin_menu() {
    add_options_page(
        'Weather Graph Plugin Settings', // Page title
        'Weather Graph',                 // Menu title
        'manage_options',                // Capability
        'weather-graph-plugin',          // Menu slug
        'weather_graph_plugin_settings_page' // Callback function
    );
}

function weather_graph_plugin_settings_page() {
    ?>
    <div class="wrap">
        <h1>Weather Graph Plugin</h1>
        <p>To display the weather graph, use the following shortcode:</p>
        <pre><code>[weather_graph]</code></pre>
        <p>Insert this shortcode into any post or page where you want the graph to appear.</p>
    </div>
    <?php
}
