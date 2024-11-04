jQuery(document).ready(function($) {
    // Function to fetch weather data and update the graph
    function fetchWeatherData() {
        const stationIds = $('#weather-station').val();
        const yAxisMeasure = $('#y-axis-measure').val();
        const xAxisTime = $('#x-axis-time').val();

        console.log('Fetching weather data with parameters:', {
            station_ids: stationIds,
            y_axis_measure: yAxisMeasure,
            x_axis_time: xAxisTime,
        });

        $.ajax({
            url: weatherGraphSettings.ajax_url,
            type: 'POST',
            data: {
                action: 'fetch_weather_graph_data',
                station_ids: stationIds,
                y_axis_measure: yAxisMeasure,
                x_axis_time: xAxisTime,
            },
            success: function(response) {
                console.log('AJAX request successful', response);
                if (response.success) {
                    updateGraph(response.data);
                } else {
                    console.error('No data found:', response.data);
                    alert('No data found for the selected parameters.');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX request failed:', status, error);
                alert('Failed to fetch weather data. Please try again.');
            }
        });
    }

    // Function to fetch weather stations and populate the dropdown
    function fetchWeatherStations() {
        console.log('Fetching weather stations for dropdown');
        $.ajax({
            url: weatherGraphSettings.ajax_url,
            type: 'POST',
            data: {
                action: 'fetch_weather_stations_for_dropdown'
            },
            success: function(response) {
                console.log('AJAX request successful', response);
                if (response.success) {
                    const stations = response.data.stations;
                    const dropdown = $('#weather-station');
                    dropdown.empty();
                    dropdown.append($('<option>', {
                        value: '',
                        text: 'Select a station',
                        disabled: true,
                        selected: true
                    }));
                    stations.forEach(function(station) {
                        console.log('Adding station to dropdown:', station);
                        dropdown.append($('<option>', {
                            value: station.station_id,
                            text: station.station_name
                        }));
                    });
                } else {
                    console.error('Failed to fetch weather stations:', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX request failed:', status, error);
                alert('Failed to fetch weather stations. Please try again.');
            }
        });
    }

    // Initialize the chart with Chart.js
    let weatherChart;
    function initializeGraph() {
        const ctx = document.getElementById('weather-graph').getContext('2d');
        weatherChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Weather Data',
                    data: [],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day'
                        }
                    },
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Function to update the graph with new data
    function updateGraph(data) {
        console.log('Updating graph with data:', data);
        weatherChart.data.labels = data.labels;
        weatherChart.data.datasets[0].data = data.values;
        weatherChart.update();
    }

    // Fetch weather data when the form is submitted
    $('#weather-form').on('submit', function(event) {
        event.preventDefault();
        fetchWeatherData();
    });

    // Fetch weather stations and populate the dropdown on page load
    fetchWeatherStations();

    // Initialize the graph on page load
    initializeGraph();
});