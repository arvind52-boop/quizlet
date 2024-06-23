<?php
$host = 'localhost';
$db = 'online_quiz';
$user = 'root';
$pass = '';

// Establish PDO connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $testName = $_POST['testName'];
    $testId = $_POST['testId'];
    $numQuestions = $_POST['numQuestions'];
    $questions = $_POST['questions'];

    try {
        // Insert test into 'tests' table
        $stmt = $pdo->prepare("INSERT INTO `test_detail` (test_id, test_name) VALUES (?, ?)");
        $stmt->execute([$testId, $testName]);
        $testId = $pdo->lastInsertId(); // Get the ID of the newly inserted test

        // Insert questions and options into 'questions' and 'options' tables
        foreach ($questions as $question) {
            $questionText = $question['question'];
            $correctOption = $question['correct'];

            // Insert question into 'questions' table
            $stmt = $pdo->prepare("INSERT INTO questions (test_id, question_text, correct_option) VALUES (?, ?, ?)");
            $stmt->execute([$testId, $questionText, $correctOption]);
            $questionId = $pdo->lastInsertId(); // Get the ID of the newly inserted question

            // Insert options into 'options' table
            foreach ($question['options'] as $optionText) {
                $stmt = $pdo->prepare("INSERT INTO options (question_id, option_text) VALUES (?, ?)");
                $stmt->execute([$questionId, $optionText]);
            }
        }

        // Redirect to a confirmation page or any other relevant page
        header("Location: create_test.html"); // Redirect to create another test or a different page
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
