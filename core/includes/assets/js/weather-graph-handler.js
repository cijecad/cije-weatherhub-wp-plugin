jQuery(document).ready(function($) {
    // Fetch weather stations via AJAX
    $.ajax({
        url: weatherGraphSettings.ajax_url,
        method: 'POST',
        data: {
            action: 'fetch_weather_stations_for_graph'
        },
        success: function(response) {
            console.log('Stations AJAX response:', response);
            if (response.success) {
                var stations = response.data;
                var $weatherStationSelect = $('#weather-station');

                // Populate the dropdown with weather stations
                stations.forEach(function(station) {
                    var option = $('<option></option>')
                        .attr('value', station.station_id)
                        .text(station.station_name);
                    $weatherStationSelect.append(option);
                });
            } else {
                console.error('Failed to fetch weather stations:', response.data);
            }
        },
        error: function(error) {
            console.error('Stations AJAX error:', error);
        }
    });

    // Function to parse date strings into Date objects
    function parseDateString(dateString) {
        var parts = dateString.split(/[- :]/);
        return new Date(
            parseInt(parts[0], 10), // Year
            parseInt(parts[1], 10) - 1, // Month (0-based)
            parseInt(parts[2], 10), // Day
            parseInt(parts[3], 10), // Hour
            parseInt(parts[4], 10), // Minute
            parseInt(parts[5], 10) // Second
        );
    }

    // Fetch weather data and render the graph
    function fetchWeatherDataAndRenderGraph() {
        var stationId = $('#weather-station').val();
        var measure = $('#y-axis-measure').val();
        var timeRange = $('#time-range').val();

        if (!stationId || !measure || !timeRange) {
            return;
        }

        $.ajax({
            url: weatherGraphSettings.ajax_url,
            method: 'POST',
            data: {
                action: 'fetch_weather_data_for_graph',
                station_id: stationId,
                measure: measure,
                time_range: timeRange
            },
            success: function(response) {
                if (response.success) {
                    var weatherData = response.data;

                    var labels = [];
                    var values = [];

                    weatherData.forEach(function(point) {
                        var date = parseDateString(point.date_time);
                        var value = parseFloat(point[measure]);

                        if (!isNaN(date.getTime()) && !isNaN(value)) {
                            labels.push(date); // Use Date objects for labels
                            values.push(value);
                        } else {
                            console.warn('Invalid data point:', point.date_time, point[measure]);
                        }
                    });

                    console.log('Processed Labels:', labels);
                    console.log('Processed Values:', values);

                    // Remove debugging display
                    // $('#debug-output').html(
                    //     '<p>Labels: ' + labels.join(', ') + '</p>' +
                    //     '<p>Values: ' + values.join(', ') + '</p>'
                    // );

                    // Clear the previous chart instance if it exists
                    if (window.myChart) {
                        window.myChart.destroy();
                    }

                    // Render the graph using Chart.js
                    var ctx = document.getElementById('weather-graph').getContext('2d');
                    window.myChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: measure.charAt(0).toUpperCase() + measure.slice(1),
                                data: values,
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                xAxes: [{ // For Chart.js version 2.x
                                    type: 'time', // Set x-axis type to 'time'
                                    time: {
                                        parser: 'YYYY-MM-DD HH:mm:ss', // Adjust based on your date format
                                        tooltipFormat: 'MMM D, YYYY HH:mm',
                                        unit: 'day', // 'hour', 'day', etc., depending on your data
                                        displayFormats: {
                                            day: 'MMM D', // Format for the x-axis labels
                                        }
                                    },
                                    scaleLabel: {
                                        display: true,
                                        labelString: 'Date' // Label for the x-axis
                                    },
                                    ticks: {
                                        source: 'data', // Ensure ticks match your data points
                                        autoSkip: true,
                                        maxRotation: 0,
                                        callback: function(value, index, values) {
                                            return moment(value).format('MMM D'); // Adjust format as needed
                                        }
                                    }
                                }],
                                yAxes: [{
                                    scaleLabel: {
                                        display: true,
                                        labelString: measure.charAt(0).toUpperCase() + measure.slice(1)
                                    }
                                }]
                            }
                        }
                    });
                } else {
                    console.error('Failed to fetch weather data:', response.data);
                }
            },
            error: function(error) {
                console.error('Weather data AJAX error:', error);
            }
        });
    }

    // Event listeners for dropdown changes
    $('#weather-station, #y-axis-measure, #time-range').on('change', function() {
        fetchWeatherDataAndRenderGraph();
    });

    // Initial call to render the graph
    fetchWeatherDataAndRenderGraph();
});