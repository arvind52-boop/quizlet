<?php
// Enable error reporting and logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

try {
    // Database connection details
    $host = 'localhost';
    $db = 'online_quiz';
    $user = 'root';
    $pass = '';

    // Establish database connection
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve JSON input and decode
    $input = json_decode(file_get_contents('php://input'), true);
    $testId = isset($input['testId']) ? intval($input['testId']) : 0;
    $answers = isset($input['answers']) ? $input['answers'] : [];

    if ($testId <= 0) {
        throw new Exception('Invalid Test ID');
    }

    // Fetch all questions along with their options and correct answers
    $stmt = $pdo->prepare("SELECT q.id AS question_id, q.question_text, q.correct_option, o.id AS option_id, o.option_text
                           FROM questions q
                           JOIN options o ON q.id = o.question_id
                           WHERE q.test_id = :test_id");
    $stmt->execute([':test_id' => $testId]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize result structure
    $results = [];
    $score = 0;
    foreach ($questions as $question) {
        $questionId = $question['question_id'];
        $correctOption = $question['correct_option'];
        $selectedOption = isset($answers[$questionId]) ? intval($answers[$questionId]) : null;

        // Calculate score if selected option is correct
        if ($selectedOption === $correctOption) {
            $score += 4; // Add score for correct answer
        }

        // Prepare question details
        if (!isset($results[$questionId])) {
            $results[$questionId] = [
                'question_text' => $question['question_text'],
                'correct_option' => $correctOption,
                'selected_option' => $selectedOption,
                'options' => []
            ];
        }

        // Add option details
        $results[$questionId]['options'][] = [
            'option_id' => $question['option_id'],
            'option_text' => $question['option_text']
        ];
    }

    // Return JSON response with results
    echo json_encode([
        'status' => 'success',
        'score' => $score,
        'total' => count($questions),
        'results' => $results
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to process request: ' . $e->getMessage()]);
}
?>
