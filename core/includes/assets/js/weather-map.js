jQuery(document).ready(function($) {
    console.log('Weather map script loaded'); // Debugging statement

    // Initialize the map centered on North America
    var map = L.map('weather-map').setView([37.8, -96], 4);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Fetch weather station data and add markers to the map
    $.ajax({
        url: weatherHubSettings.ajax_url,
        type: 'POST',
        data: {
            action: 'fetch_weather_stations'
        },
        success: function(response) {
            console.log('AJAX response:', response); // Debugging statement
            if (response.success) {
                response.data.stations.forEach(function(station) {
                    L.marker([station.latitude, station.longitude])
                        .addTo(map)
                        .bindPopup('<b>' + station.station_name + '</b><br>' + station.school);
                });
            } else {
                console.error(response.data);
                alert('Failed to fetch weather stations.');
            }
        },
        error: function() {
            alert('Failed to fetch weather stations. Please try again.');
        }
    });
});