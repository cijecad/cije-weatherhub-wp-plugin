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
                labels: [],
                datasets: [{
                    label: '',
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
        weatherChart.data.labels = data.labels;
        weatherChart.data.datasets[0].label = data.datasets[0].label;
        weatherChart.data.datasets[0].data = data.datasets[0].data;
        weatherChart.update();
    }

    // Event listeners for the select elements
    $('#weather-station, #y-axis-measure, #x-axis-time').change(fetchWeatherData);

    // Initialize the graph on page load
    initializeGraph();
    fetchWeatherData();
});