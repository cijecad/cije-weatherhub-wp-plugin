jQuery(document).ready(function($) {
    // Create the map and set the view to the United States
    var map = L.map('weather-map').setView([37.8, -96], 4); // Adjust the coordinates and zoom level to show the entire USA

    // Add OpenStreetMap tiles to the map
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Function to fetch weather data and plot it on the map
    function fetchWeatherData() {
        $.ajax({
            url: weatherHubSettings.ajax_url,  // Provided by wp_localize_script in the PHP file
            method: 'POST',
            data: {
                action: 'fetch_weather_data'  // AJAX action
            },
            success: function(response) {
                console.log("AJAX response:", response);  // Log the AJAX response for debugging

                // Parse the response JSON
                try {
                    var weatherData = JSON.parse(response);
                } catch (e) {
                    console.error("Error parsing JSON response:", e);
                    return;  // Stop execution if JSON parsing fails
                }

                // Iterate over the weather data and plot it on the map
                weatherData.forEach(function(station) {
                    // Check if latitude and longitude are valid
                    if (station.latitude && station.longitude) {
                        var marker = L.marker([station.latitude, station.longitude]).addTo(map);
                        marker.bindPopup(`
                            <strong>Station Name:</strong> ${station.station_name}<br>
                            <strong>Station ID:</strong> ${station.station_id}<br>
                            <strong>School:</strong> ${station.school}<br>
                            <strong>Temperature:</strong> ${station.temperature} °C<br>
                            <strong>Humidity:</strong> ${station.humidity} %<br>
                            <strong>Pressure:</strong> ${station.pressure} hPa<br>
                            <strong>Precipitation:</strong> ${station.precipitation} mm<br>
                            <strong>Wind Speed:</strong> ${station.wind_speed} m/s<br>
                        `);
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX error:", status, error);
            }
        });
    }

    // Fetch weather data when the document is ready
    fetchWeatherData();
});