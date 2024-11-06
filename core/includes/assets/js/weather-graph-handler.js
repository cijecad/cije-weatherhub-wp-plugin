jQuery(document).ready(function($) {
    // Log a message to confirm the document is ready
    console.log('Document is ready');

    // Ensure the map container exists
    if ($('#weather-map').length === 0) {
        console.error('Map container not found');
        return;
    }

    // Initialize the map
    var map = L.map('weather-map').setView([0, 0], 2);

    // Log a message to confirm the map is initialized
    console.log('Map initialized');

    // Add a tile layer to the map
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Log a message to confirm the tile layer is added
    console.log('Tile layer added to map');

    // Fetch weather stations via AJAX
    console.log('Sending AJAX request to fetch weather stations');
    $.ajax({
        url: weatherHubSettings.ajax_url,
        method: 'POST',
        data: {
            action: 'fetch_weather_stations'
        },
        success: function(response) {
            console.log('AJAX request successful', response);
            if (response.success) {
                // Add markers to the map for each weather station
                response.data.stations.forEach(function(station) {
                    console.log('Adding marker for station:', station);
                    var popupContent = '<b>' + station.station_name + '</b><br>' + station.school + '<br>' +
                        'Latitude: ' + station.latitude + '<br>' +
                        'Longitude: ' + station.longitude;
                    L.marker([station.latitude, station.longitude])
                        .addTo(map)
                        .bindPopup(popupContent);
                });
            } else {
                console.error('Failed to fetch weather stations:', response.data);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX request failed:', status, error);
            console.error('Response:', xhr.responseText);
        }
    });
});