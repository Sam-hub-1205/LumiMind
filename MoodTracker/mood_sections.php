<?php require_once('../sidebar/sidebar.html');  
// Database connection
$conn = new mysqli("localhost", "root", "", "LumiMind");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$currentDate = date("Y-m-d");
$yesterdayDate = date("Y-m-d", strtotime("-1 day"));
$todaysMood = null;
$yesterdaysMood = null;
$motivationMessage = "Select your mood to get a motivation!"; // Default message

// Check if a mood has been recorded for today to display motivation
$checkStmt = $conn->prepare("SELECT mood FROM mood_entries WHERE entry_date = ?");
$checkStmt->bind_param("s", $currentDate);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    $row = $checkResult->fetch_assoc();
    $todaysMood = $row['mood'];

    // Define motivational messages based on mood
    $motivations = [
        "happy-excited" => "Your enthusiasm is contagious! Keep shining!",
        "angry" => "It's okay to feel angry. Take a deep breath and find a constructive outlet.",
        "sad" => "It's alright to feel sad. Remember that tough times don't last, but tough people do.",
        "surprised" => "Embrace the unexpected! New possibilities might be unfolding.",
        "happy" => "Enjoy this wonderful feeling! Share your happiness with others.",
        "confused" => "Feeling lost is part of the journey. Seek clarity and don't be afraid to ask for help.",
        "worried" => "Acknowledge your worries, but don't let them control you. Focus on what you can influence.",
        "neutral" => "A calm and balanced state. Use this energy wisely for your next endeavor.",
    ];

    if (isset($motivations[$todaysMood])) {
        $motivationMessage = $motivations[$todaysMood];
    }
}

// Get yesterday's mood
$yesterdayStmt = $conn->prepare("SELECT mood FROM mood_entries WHERE entry_date = ?");
$yesterdayStmt->bind_param("s", $yesterdayDate);
$yesterdayStmt->execute();
$yesterdayResult = $yesterdayStmt->get_result();

if ($yesterdayResult->num_rows > 0) {
    $row = $yesterdayResult->fetch_assoc();
    $yesterdaysMood = $row['mood'];
}

$checkStmt->close();
$yesterdayStmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LumiMind</title>
    <link rel="stylesheet" href="../css/mood_sections.css">
   <style>
        .motivation-popup, .error-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff8e1; /* Light yellow */
            border: 1px solid #ffe57f; /* A slightly darker yellow */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            text-align: center;
        }
        .motivation-popup button, .error-popup button {
            margin-top: 10px;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            background-color: #ffeb3b; /* A brighter yellow */
            cursor: pointer;
        }
        .error-popup {
            background-color: #ffe082; /* A lighter yellow */
            border-color: #ffb300;
            color: #d32f2f;
        }
        .error-popup h3 {
            color: #d32f2f;
        }

        .bottom-right-image {
            position: fixed;
            bottom: 250px;
            right: 450px;
            width: 50px;
            height: 150px;
            z-index: 100;
        }

        .mood-history, .motivation {
            padding: 25px;
            border-radius: 20px;
            background-color: #fffde7; /* Very light yellow */
            text-align: center;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
            margin-top: 30px;
        }

        .mood-history:hover, .motivation:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .mood-history h3, .motivation h3 {
            font-size: 1.6em;
            margin-bottom: 20px;
            color: #f57f17; /* A deep orange-yellow */
            font-family: 'Poppins', sans-serif;
            letter-spacing: 1px;
            text-shadow: 2px 2px 3px rgba(0,0,0,0.05);
        }

        .yesterday-mood {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 25px;
            border-radius: 15px;
            border: 3px solid #ffeb3b; /* A bright yellow */
            padding: 15px;
            background-color: #fff9c4; /* A very pale yellow */
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }

        .yesterday-mood .mood-display img {
            width: 70px;
            height: auto;
            transition: transform 0.3s ease;
        }

        .yesterday-mood .mood-display img:hover {
            transform: scale(1.2);
        }

        .yesterday-mood .mood-text {
            margin-left: 15px;
            font-weight: bold;
            color: #e65100; /* A dark orange */
            font-size: 1.2em;
            font-family: 'Open Sans', sans-serif;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.05);
        }

        .view-more {
            padding: 15px 30px;
            border: none;
            border-radius: 30px;
            background-color: #ffeb3b; /* A bright yellow */
            color: #212121;
            font-size: 1.2em;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.1s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 1px;
        }

        .view-more:hover {
            background-color: #fdd835; /* A slightly darker yellow */
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .view-more:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* New Styles for a more creative design */
        .mood-history, .motivation {
            background: linear-gradient(135deg, #fffde7, #fff59d); /* Lighter yellow gradient */
            border: 2px solid #ffee58; /* A more pronounced yellow border */
            box-shadow: 5px 5px 15px rgba(0,0,0,0.1);
        }

        .mood-history h3, .motivation h3 {
            color: #f57f17; /* Deep orange-yellow */
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            font-size: 1.8em;
        }

        .yesterday-mood {
            background-color: rgba(255,248,220,0.8); /* Very pale yellow */
            border: 2px dashed #fdd835; /* A darker yellow dashed border */
        }

        .yesterday-mood .mood-text {
            color: #e65100; /* Dark orange */
            font-size: 1.3em;
            text-shadow: 1px 1px 1px rgba(255,255,255,0.9);
        }

        .view-more {
            background-color: #ffeb3b; /* Bright yellow */
            color: #212121;
            border: none;
            box-shadow: 3px 3px 7px rgba(0,0,0,0.1);
        }

        .view-more:hover {
            background-color: #fdd835; /* Slightly darker yellow */
            transform: scale(1.05);
            box-shadow: 5px 5px 10px rgba(0,0,0,0.15);
        }

        /* Improved "Tell Us Your Mood" Section */
        .mood-input {
            padding: 30px;
            border-radius: 20px;
            background-color: #fff;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            text-align: center;
            transition: background-color 0.3s ease, transform 0.1s ease;
            border: 1px solid #ffee58; /* Yellow border */
        }

        .mood-input:hover {
            background-color: #fffaf0; /* Very light yellow */
            transform: translateY(-2px);
        }

        .mood-input h2 {
            font-size: 2em;
            color: #ffb300; /* A strong yellow */
            margin-bottom: 25px;
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 2px;
            text-shadow: 2px 2px 3px rgba(0,0,0,0.05);
        }

        .mood-faces {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 10px;
        }

        .mood-face {
            width: 80px;
            height: 80px;
            border: none;
            border-radius: 50%;
            background-color: #ffeb3b; /* Bright yellow */
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 0;
            display: flex; /* For centering the image */
            align-items: center;
            justify-content: center;
        }

        .mood-face:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.15);
        }

        .mood-face img {
            width: 60px;
            height: auto;
        }

        /* Improved Motivation Section */
        .motivation {
            padding: 25px;
            border-radius: 20px;
            background-color: #fffde7; /* Very light yellow */
            text-align: center;
            margin-top: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #ffee58; /* Yellow border */
        }

        .motivation h3 {
            font-size: 1.5em;
            color: #f57f17; /* Deep orange-yellow */
            margin-bottom: 20px;
            font-family: 'Poppins', sans-serif;
            letter-spacing: 1px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.05);
        }

        .motivation p {
            font-size: 1.1em;
            color: #e65100; /* Dark orange */
            line-height: 1.7;
            font-family: 'Open Sans', sans-serif;
            text-align: justify;
        }

        #animated-chick {
            border-radius: 15px;
            border: 5px solid #ffc107;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Open+Sans:wght@400;700&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="left-side">
            <section class="mood-input">
                <h2>Tell Us Your Mood</h2>
                <?php if (isset($_GET['success'])): ?>
                    <p style="color: green;">Your mood has been recorded!</p>
                    <div id="motivation-popup" class="motivation-popup">
                        <h3>Your Motivation</h3>
                        <p><?php echo $motivationMessage; ?></p>
                        <button onclick="closeMotivationPopup()">OK</button>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            document.getElementById('motivation-popup').style.display = 'block';
                        });
                        function closeMotivationPopup() {
                            document.getElementById('motivation-popup').style.display = 'none';
                        }
                    </script>
                <?php endif; ?>

                <?php if (isset($_GET['error']) && $_GET['error'] === 'duplicate'): ?>
                    <div id="error-popup" class="error-popup">
                        <h3>Oops!</h3>
                        <p>You have already recorded your mood for today.</p>
                        <button onclick="closeErrorPopup()">OK</button>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            document.getElementById('error-popup').style.display = 'block';
                        });
                        function closeErrorPopup() {
                            document.getElementById('error-popup').style.display = 'none';
                        }
                    </script>
                <?php endif; ?>

                <form action="process_mood.php" method="POST">
                    <div class="mood-faces">
                        <button type="submit" class="mood-face" name="mood" value="happy-excited"><img src="../icon/happy_excited.png" alt="Happy Excited"></button>
                        <button type="submit" class="mood-face" name="mood" value="angry"><img src="../icon/angry.png" alt="Angry"></button>
                        <button type="submit" class="mood-face" name="mood" value="sad"><img src="../icon/sad.png" alt="Sad"></button>
                        <button type="submit" class="mood-face" name="mood" value="surprised"><img src="../icon/surprised.png" alt="Surprised"></button>
                        <button type="submit" class="mood-face" name="mood" value="happy"><img src="../icon/happy.png" alt="Happy"></button>
                        <button type="submit" class="mood-face" name="mood" value="confused"><img src="../icon/confused.png" alt="Confused"></button>
                        <button type="submit" class="mood-face" name="mood" value="worried"><img src="../icon/worried.png" alt="Worried"></button>
                        <button type="submit" class="mood-face" name="mood" value="neutral"><img src="../icon/neutral.png" alt="Neutral"></button>
                    </div>
                </form>
            </section>

            <div class="bottom-left">
                <section class="motivation">
                    <h3>Motivation</h3>
                    <p><?php echo $motivationMessage; ?></p>
                </section>

                <section class="mood-history">
                    <h3>Mood History</h3>
                    <div class="yesterday-mood">
                        <div class="mood-display">
                            <?php if ($yesterdaysMood): ?>
                                <?php
                                $moodIcon = '';
                                switch ($yesterdaysMood) {
                                    case 'happy-excited': $moodIcon = '../icon/happy_excited.png'; break;
                                    case 'angry':      $moodIcon = '../icon/angry.png'; break;
                                    case 'sad':        $moodIcon = '../icon/sad.png'; break;
                                    case 'surprised':    $moodIcon = '../icon/surprised.png'; break;
                                    case 'happy':      $moodIcon = '../icon/happy.png'; break;
                                    case 'confused':     $moodIcon = '../icon/confused.png'; break;
                                    case 'worried':      $moodIcon = '../icon/worried.png'; break;
                                    case 'neutral':      $moodIcon = '../icon/neutral.png'; break;
                                    default:         $moodIcon = '../icon/duck.png';
                                }
                                ?>
                                <img src="<?php echo $moodIcon; ?>" alt="Yesterday's Mood">
                            <?php else: ?>
                                <img src="../icon/duck.png" alt="Yesterday's Mood">
                            <?php endif; ?>
                        </div>
                        <div class="mood-text">
                            <?php if ($yesterdaysMood): ?>
                                Yesterday's Mood: <?php echo ucfirst($yesterdaysMood); ?>
                            <?php else: ?>
                                No mood recorded yesterday.
                            <?php endif; ?>
                        </div>
                    </div>
                    <button class="view-more" onclick="window.location.href='moodhistory.php'">View More</button>
                </section>
            </div>
        </div>

        <section class="sticky-note">
            <div class="sticky-header">
                <h3>Sticky Note</h3>
                <button class="add-note">+</button>
            </div>
            <div class="note-items">
                <form action="process_note.php" method="POST" id="note-form">
                    <div id="note-list">
                        <label><input type="checkbox" name="note[]" value="drink_water"> Drink More Water</label><br>
                        <label><input type="checkbox" name="note[]" value="jogging"> Jogging</label><br>
                    </div>
                    <button type="submit">Save Notes</button>
                </form>
            </div>
        </section>
    </div>

    <div class="bottom-right-image">
        <img id="animated-chick" src="https://media2.giphy.com/media/v1.Y2lkPTc5MGI3NjExb20zZHRlN2YwdThkZ2J4Y3Jycmo4YWFrbmh0M2Q1YjMzb3E4aTNsNiZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/hC2mA1FWFs2OowO60p/giphy.gif" alt="Animated Chick">
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const noteItemsContainer = document.getElementById('note-list');
            const addNoteButton = document.querySelector('.sticky-note .add-note');

            addNoteButton.addEventListener('click', () => {
                const newNoteText = prompt('Enter a new sticky note:');
                if (newNoteText) {
                    const newLabel = document.createElement('label');
                    const newCheckbox = document.createElement('input');
                    newCheckbox.type = 'checkbox';
                    newCheckbox.name = 'note[]';
                    newCheckbox.value = newNoteText.toLowerCase().replace(/\s+/g, '_');
                    newLabel.appendChild(newCheckbox);
                    newLabel.appendChild(document.createTextNode(` ${newNoteText}`));
                    const newBr = document.createElement('br');
                    noteItemsContainer.appendChild(newLabel);
                    noteItemsContainer.appendChild(newBr);
                }
            });

            noteItemsContainer.addEventListener('change', (event) => {
                if (event.target.type === 'checkbox') {
                    const label = event.target.parentNode;
                    label.classList.toggle('checked', event.target.checked);
                }
            });
        });
    </script>
</body>
</html>
