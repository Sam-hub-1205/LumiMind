<?php
require_once('../sidebar/sidebar.html');
// Database connection
$conn = new mysqli("localhost", "root", "", "LumiMind");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare data for monthly mood chart
$monthlyMoodCounts = [];
$monthlyStmt = $conn->prepare("SELECT mood, DATE_FORMAT(entry_date, '%Y-%m') AS month FROM mood_entries ORDER BY month ASC");
$monthlyStmt->execute();
$monthlyResult = $monthlyStmt->get_result();
while ($row = $monthlyResult->fetch_assoc()) {
    if (!isset($monthlyMoodCounts[$row['month']])) {
        $monthlyMoodCounts[$row['month']] = [
            "happy-excited" => 0,
            "angry" => 0,
            "sad" => 0,
            "surprised" => 0,
            "happy" => 0,
            "confused" => 0,
            "worried" => 0,
            "neutral" => 0,
        ];
    }
    $monthlyMoodCounts[$row['month']][$row['mood']]++;
}
$monthlyStmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Mood Chart</title>
    <link rel="stylesheet" href="mood_sections.css">
    <style>
      body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f7f6;
        color: #333;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-bottom: 40px;
      }

      .chart-container {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
        margin: 20px;
        padding: 30px;
        width: 90%;
        max-width: 900px;
      }

      .chart-container h2 {
        text-align: center;
        color: #555;
        margin-bottom: 20px;
      }

      #monthlyMoodChart {
        width: 100%;
        height: 400px;
        background-color: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        padding: 10px;
        margin: 0 auto;
      }

      button {
        background-color: yellow;
        color: #555;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        margin-top: 20px;
        transition: background-color 0.3s ease;
      }

      button:hover {
        background-color: #ffeb3b; /* Bright yellow for hover */
        color: #212121;
        border-color: #ffeb3b;
      }

      .back-button-container {
        width: 90%;
        max-width: 900px;
        margin: 20px auto 0 auto;
        text-align: center;
      }
    </style>
</head>
<body>
    <div class="chart-container">
        <h2>Monthly Mood Trends</h2>
        <canvas id="monthlyMoodChart"></canvas>
    </div>
    <div class="back-button-container">
      <button onclick="window.location.href='moodhistory.php'">Back to Mood History</button>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const chartCanvas = document.getElementById('monthlyMoodChart');
            if (chartCanvas) {
                const monthlyData = <?php echo json_encode($monthlyMoodCounts); ?>;
                const labels = Object.keys(monthlyData);
                const moodTypes = ["happy-excited", "angry", "sad", "surprised", "happy", "confused", "worried", "neutral"];
                const barColors = ["#ffdd57", "#f44336", "#2196f3", "#4caf50", "#ff9800", "#9c27b0", "#607d8b", "#795548"];

                const datasets = [];
                moodTypes.forEach((mood, index) => {
                    const data = labels.map(month => monthlyData[month]?.[mood] || 0);
                    datasets.push({
                        label: mood,
                        data: data,
                        backgroundColor: barColors[index],
                        borderColor: barColors[index],
                        borderWidth: 1
                    });
                });

                new Chart(chartCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                padding: 10,
                                fontColor: '#555'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Monthly Mood Distribution',
                            fontColor: '#333',
                            fontSize: 18,
                            padding: 10
                        },
                        scales: {
                            xAxes: [{
                                stacked: true,
                                ticks: {
                                    fontColor: '#666',
                                    padding: 5
                                },
                                gridLines: {
                                    display: false,
                                }
                            }],
                            yAxes: [{
                                stacked: true,
                                ticks: {
                                    beginAtZero: true,
                                    fontColor: '#666',
                                    padding: 10
                                },
                                gridLines: {
                                    color: '#eee',
                                    drawBorder: false
                                }
                            }]
                        },
                        tooltips: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFontColor: '#fff',
                            bodyFontColor: '#fff',
                            borderColor: 'rgba(0, 0, 0, 0.8)',
                            borderWidth: 1,
                            cornerRadius: 4,
                            displayColors: true,
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    let label = data.datasets[tooltipItem.datasetIndex].label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += tooltipItem.value;
                                    return label;
                                },
                                title: function(tooltipItems, data) {
                                    return 'Month: ' + data.labels[tooltipItems[0].index];
                                }
                            }
                        },
                        hover: {
                            mode: 'index',
                            intersect: false
                        },
                    }
                });
            }
        });
    </script>
</body>
</html>
