<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = [
    'status' => 'error',
    'score' => 0,
    'total' => 0,
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

    // Fetch the correct answers from the database to calculate the score
    $stmt = $pdo->prepare("SELECT id AS question_id, correct_option FROM questions WHERE test_id = :test_id");
    $stmt->execute([':test_id' => $testId]);
    $correctAnswers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate the score
    $score = 0;
    foreach ($correctAnswers as $correctAnswer) {
        $questionId = $correctAnswer['question_id'];
        if (isset($_POST[$questionId]) && $_POST[$questionId] == $correctAnswer['correct_option']) {
            $score += 4; // 4 points for a correct answer
        }
    }

    $response['status'] = 'success';
    $response['score'] = $score;
    $response['total'] = count($correctAnswers);
    $response['message'] = 'Score calculated successfully.';
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Output the JSON response
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
