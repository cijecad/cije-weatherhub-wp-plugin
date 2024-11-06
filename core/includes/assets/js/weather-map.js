jQuery(document).ready(function($) {
    // Initialize the Leaflet map with a default view
    var map = L.map('weather-map').setView([0, 0], 2);

    // Add the OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Fetch weather stations via AJAX
    $.ajax({
        url: weatherHubSettings.ajax_url,
        method: 'POST',
        data: {
            action: 'fetch_weather_stations'
        },
        success: function(response) {
            console.log('AJAX response:', response); // Log the AJAX response

            if (response.success) {
                var bounds = new L.LatLngBounds(); // Create a new bounds object

                // Add markers for each weather station
                response.data.forEach(function(station) {
                    console.log('Adding marker for station:', station); // Log each station

                    // Ensure all fields are properly handled
                    var popupContent = '<b>' + (station.station_name || 'N/A') + '</b><br>' +
                        'Temperature: ' + (station.temperature || 'N/A') + '°C<br>' +
                        'Humidity: ' + (station.humidity || 'N/A') + '%<br>' +
                        'Pressure: ' + (station.pressure || 'N/A') + ' hPa<br>' +
                        'Wind Speed: ' + (station.wind_speed || 'N/A') + ' m/s<br>' +
                        'Last Updated: ' + (station.date_time || 'N/A') + '<br>';
                    console.log('Popup content:', popupContent); // Log the popup content

                    var marker = L.marker([station.latitude, station.longitude]).addTo(map)
                        .bindPopup(popupContent);
                    bounds.extend(marker.getLatLng()); // Extend the bounds to include this marker's position
                });

                // Fit the map to the bounds of the markers
                map.fitBounds(bounds);
            } else {
                console.error('Failed to fetch weather stations:', response.data);
            }
        },
        error: function(error) {
            console.error('AJAX error:', error);
        }
    });
});