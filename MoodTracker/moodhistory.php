<?php
require_once('../sidebar/sidebar.html');
// Database connection
$conn = new mysqli("localhost", "root", "", "LumiMind");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get mood icon URL
function getMoodIcon($mood) {
    $icons = [
        "happy-excited" => "../icon/happy_excited.png",
        "angry" => "../icon/angry.png",
        "sad" => "../icon/sad.png",
        "surprised" => "../icon/surprised.png",
        "happy" => "../icon/happy.png",
        "confused" => "../icon/confused.png",
        "worried" => "../icon/worried.png",
        "neutral" => "../icon/neutral.png",
    ];
    return isset($icons[$mood]) ? $icons[$mood] : "icon/default.png";
}

// Function to get mood color
function getMoodColor($mood) {
    $colors = [
        "happy-excited" => "#FFDB58",
        "angry" => "#F44336",
        "sad" => "#2196F3",
        "surprised" => "#4CAF50",
        "happy" => "#FFC107",
        "confused" => "#9C27B0",
        "worried" => "#607D8B",
        "neutral" => "#795548",
    ];
    return isset($colors[$mood]) ? $colors[$mood] : "#EEEEEE";
}

// Version 1: Display data for the selected month
if (isset($_GET['month'])) {
    $selectedMonth = $_GET['month'];
    $stmt = $conn->prepare("SELECT mood, entry_date FROM mood_entries WHERE DATE_FORMAT(entry_date, '%m') = ? ORDER BY entry_date DESC");
    $stmt->bind_param("s", $selectedMonth);
    $stmt->execute();
    $result = $stmt->get_result();
    $moodHistory = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    //show current month
    $selectedMonth = date('m');
    $stmt = $conn->prepare("SELECT mood, entry_date FROM mood_entries WHERE DATE_FORMAT(entry_date, '%m') = ? ORDER BY entry_date DESC");
    $stmt->bind_param("s", $selectedMonth);
    $stmt->execute();
    $result = $stmt->get_result();
    $moodHistory = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mood History</title>
    <link rel="stylesheet" href="mood_sections.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fffde7; /* Lightest yellow background */
            color: #555; /* Darker text for better contrast on yellow */
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-bottom: 20px;
        }

        h2 {
            color: #f57f17; /* Deep orange-yellow for heading */
            text-align: center;
            margin: 20px 0;
            font-size: 24px;
            font-weight: 600;
        }

        .mood-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
            padding: 16px;
            width: 90%;
            max-width: 1000px;
            margin: 0 auto;
            opacity: 0;
            animation: fadeIn 0.5s forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .mood-card {
            background-color: #ffeb3b; /* Bright yellow mood card */
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            border: 1px solid #fdd835; /* Slightly darker yellow border */
        }

        .mood-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .mood-icon {
            width: 80px;
            height: 80px;
            margin-bottom: 12px;
        }

        .mood-date {
            font-size: 18px;
            color: #212121; /* Darker orange for date */
            margin-bottom: 8px;
            font-weight: 500;
        }

        .mood-text {
            font-size: 20px;
            color: #212121; /* Very dark grey/black for text on yellow */
            padding: 8px 16px;
            border-radius: 16px;
            margin-top: 8px;
            font-weight: 500;
            background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent white for text background */
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .pagination button{
            margin: 0 8px;
        }

        .pagination button {
            background-color: #fff;
            color: #555;
            border: 1px solid #ddd;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out, border-color 0.3s ease-in-out;
        }

        .pagination button:hover {
            background-color: #ffeb3b; /* Bright yellow for hover */
            color: #212121;
            border-color: #ffeb3b;
        }

        .pagination button:disabled {
            background-color: #eee;
            color: #aaa;
            border-color: #ddd;
            cursor: not-allowed;
        }

        .month-selector {
            display: flex;
            justify-content: center;
            margin: 20px 0;
            gap: 10px;
            width: 90%;
            max-width: 600px;
        }

        .month-selector button {
            background-color: #fff;
            color: #555;
            border: 1px solid #ddd;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out, border-color 0.3s ease-in-out;
            white-space: nowrap;
        }

        .month-selector button:hover {
            background-color: #ffeb3b; /* Bright yellow for hover */
            color: #212121;
            border-color: #ffeb3b;
        }

        .month-selector button.active {
            background-color: #ffeb3b; /* Active month button */
            color: #212121;
            border-color: #ffeb3b;
            font-weight: 600;
        }

        .view-chart-button {
            background-color: #ffeb3b; /* Bright yellow */
            color: #212121;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            margin-top: 20px;
            transition: background-color 0.3s ease-in-out,
                        box-shadow 0.3s ease-in-out,
                        transform 0.2s ease-in-out;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            display: inline-block;
        }

        .view-chart-button:hover {
            background-color: #fdd835; /* Slightly darker yellow on hover */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .view-chart-button:active {
            background-color: #fbc02d; /* Even darker yellow on active */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            transform: translateY(0);
        }

    </style>
</head>
<body>
    <h2>Mood History</h2>
    <div class="month-selector">
        <button data-month="01" onclick="window.location.href='moodhistory.php?month=01'" <?php if ($selectedMonth == '01') echo 'class="active"'; ?>>January</button>
        <button data-month="02" onclick="window.location.href='moodhistory.php?month=02'" <?php if ($selectedMonth == '02') echo 'class="active"'; ?>>February</button>
        <button data-month="03" onclick="window.location.href='moodhistory.php?month=03'" <?php if ($selectedMonth == '03') echo 'class="active"'; ?>>March</button>
        <button data-month="04" onclick="window.location.href='moodhistory.php?month=04'" <?php if ($selectedMonth == '04') echo 'class="active"'; ?>>April</button>
        <button data-month="05" onclick="window.location.href='moodhistory.php?month=05'" <?php if ($selectedMonth == '05') echo 'class="active"'; ?>>May</button>
        <button data-month="06" onclick="window.location.href='moodhistory.php?month=06'" <?php if ($selectedMonth == '06') echo 'class="active"'; ?>>June</button>
        <button data-month="07" onclick="window.location.href='moodhistory.php?month=07'" <?php if ($selectedMonth == '07') echo 'class="active"'; ?>>July</button>
        <button data-month="08" onclick="window.location.href='moodhistory.php?month=08'" <?php if ($selectedMonth == '08') echo 'class="active"'; ?>>August</button>
        <button data-month="09" onclick="window.location.href='moodhistory.php?month=09'" <?php if ($selectedMonth == '09') echo 'class="active"'; ?>>September</button>
        <button data-month="10" onclick="window.location.href='moodhistory.php?month=10'" <?php if ($selectedMonth == '10') echo 'class="active"'; ?>>October</button>
        <button data-month="11" onclick="window.location.href='moodhistory.php?month=011'" <?php if ($selectedMonth == '11') echo 'class="active"'; ?>>November</button>
        <button data-month="12" onclick="window.location.href='moodhistory.php?month=012'" <?php if ($selectedMonth == '12') echo 'class="active"'; ?>>December</button>
    </div>
    <div class="mood-grid" id="moodGrid">
        <?php if (empty($moodHistory)): ?>
            <p>No mood entries found for the selected month.</p>
        <?php else: ?>
            <?php foreach ($moodHistory as $entry): ?>
                <div class="mood-card" style="background-color: <?php echo getMoodColor($entry['mood']); ?>">
                    <img src="<?php echo getMoodIcon($entry['mood']); ?>" alt="<?php echo htmlspecialchars($entry['mood']); ?>" class="mood-icon">
                    <p class="mood-date"><?php echo date("d F Y", strtotime($entry['entry_date'])); ?></p>
                    <p class="mood-text"><?php echo htmlspecialchars($entry['mood']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div style="text-align: center; margin-top: 20px;">
        <a href="moodchart.php" class="view-chart-button">View Monthly Chart</a>
    </div>
</body>
</html>
