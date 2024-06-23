<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = [
    'status' => 'error',
    'data' => null,
    'message' => 'An unknown error occurred.'
];

try {
    // Database connection details
    $host = 'localhost';
    $db = 'online_quiz';
    $user = 'root';
    $pass = '';

    // Establish database connection
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Validate and sanitize testId
    $testId = isset($_GET['testId']) ? intval($_GET['testId']) : 0; // Ensure testId is an integer
    if ($testId <= 0) {
        throw new Exception('Invalid or missing Test ID.');
    }

    $testData = [
        'test' => null,
        'questions' => []
    ];

    // Fetch the test details
    $testStmt = $pdo->prepare("SELECT * FROM `test_detail` WHERE `test_id` = ?");
    $testStmt->execute([$testId]);
    $test = $testStmt->fetch(PDO::FETCH_ASSOC);

    if ($test) {
        // Fetch the questions for the test
        $questionsStmt = $pdo->prepare("SELECT * FROM `questions` WHERE `test_id` = ?");
        $questionsStmt->execute([$testId]);
        $questions = $questionsStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($questions as &$question) {
            // Fetch the options for each question
            $optionsStmt = $pdo->prepare("SELECT * FROM `options` WHERE `question_id` = ?");
            $optionsStmt->execute([$question['id']]);
            $options = $optionsStmt->fetchAll(PDO::FETCH_ASSOC);

            $question['options'] = $options;
        }

        $testData['test'] = $test;
        $testData['questions'] = $questions;

        $response['status'] = 'success';
        $response['data'] = $testData;
        $response['message'] = 'Test data retrieved successfully.';
    } else {
        throw new Exception('Test not found.');
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Output the JSON response
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
