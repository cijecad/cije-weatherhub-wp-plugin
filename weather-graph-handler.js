jQuery(document).ready(function($) {
    // Function to fetch weather data and update the graph
    function fetchWeatherData() {
        const stationIds = $('#weather-station').val();
        const yAxisMeasure = $('#y-axis-measure').val();
        const xAxisTime = $('#x-axis-time').val();

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
                if (response.success) {
                    updateGraph(response.data);
                } else {
                    console.error(response.data);
                    alert('No data found for the selected parameters.');
                }
            },
            error: function() {
                alert('Failed to fetch weather data. Please try again.');
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
                labels: [], // Time labels
                datasets: [] // Data for weather stations
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        type: 'time',
                        title: {
                            display: true,
                            text: 'Time'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Measurement'
                        }
                    }
                }
            }
        });
    }

    // Function to update the chart with new data
    function updateGraph(data) {
        // Clear the previous data
        weatherChart.data.labels = [];
        weatherChart.data.datasets = [];

        // Prepare data for each station
        const stationData = {};
        data.forEach(entry => {
            if (!stationData[entry.station_id]) {
                stationData[entry.station_id] = {
                    label: 'Station ' + entry.station_id,
                    data: [],
                    borderColor: getRandomColor(),
                    fill: false
                };
            }
            stationData[entry.station_id].data.push({
                x: new Date(entry.datetime),
                y: entry[yAxisMeasure]
            });
        });

        // Add the new data to the chart
        Object.values(stationData).forEach(dataset => {
            weatherChart.data.datasets.push(dataset);
        });

        // Update chart labels
        if (data.length > 0) {
            weatherChart.data.labels = data.map(entry => new Date(entry.datetime));
        }

        // Update the chart
        weatherChart.update();
    }

    // Utility function to generate random colors for each station
    function getRandomColor() {
        const letters = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    // Attach event listeners to dropdowns to fetch data when they change
    $('#weather-station, #y-axis-measure, #x-axis-time').change(fetchWeatherData);

    // Initialize the graph
    initializeGraph();
});
