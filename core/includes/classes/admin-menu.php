<?php
// Add a menu item under 'Settings'
add_action('admin_menu', 'cije_weather_hub_plugin_menu');

function cije_weather_hub_plugin_menu() {
    add_options_page(
        __('Weather Hub Plugin Settings', 'cije-weather-hub'), // Page title
        __('Weather Hub', 'cije-weather-hub'),                 // Menu title
        'manage_options',                                      // Capability
        'weather-hub-plugin',                                  // Menu slug
        'cije_weather_hub_plugin_settings_page'                // Callback function
    );
}

function cije_weather_hub_plugin_settings_page() {
    // Check if the user has the required capability
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php _e('Weather Graph', 'cije-weather-hub'); ?></h1>
        <p><?php _e('To display the weather graph, use the following shortcode:', 'cije-weather-hub'); ?></p>
        <pre><code>[weather_graph]</code></pre>
        <p><?php _e('Insert this shortcode into any post or page where you want the graph to appear.', 'cije-weather-hub'); ?></p>
        <h1><?php _e('Weather Map', 'cije-weather-hub'); ?></h1>
        <p><?php _e('To display the weather map, use the following shortcode:', 'cije-weather-hub'); ?></p>
        <pre><code>[weather_map]</code></pre>
        <p><?php _e('Insert this shortcode into any post or page where you want the weather data map to appear.', 'cije-weather-hub'); ?></p>
        <h1><?php _e('Weather Station Registration', 'cije-weather-hub'); ?></h1>
        <p><?php _e('To display the weather station registration form, use the following shortcode:', 'cije-weather-hub'); ?></p>
        <pre><code>[register_station]</code></pre>
        <p><?php _e('Insert this shortcode into any post or page where you want the weather station registration form to appear.', 'cije-weather-hub'); ?></p>
    </div>
    <?php
}
?>