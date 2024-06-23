<?php
if (isset($_GET['testId'], $_GET['score'], $_GET['total'])) {
    $testId = $_GET['testId'];
    $score = $_GET['score'];
    $total = $_GET['total'];
    
    // Calculate percentage
    $percentage = ($total > 0) ? round(($score / ($total * 4)) * 100, 2) : 0;
    
    // Log redirection data
    file_put_contents('php://stderr', "Redirecting with: testId=$testId, score=$score, total=$total, percentage=$percentage\n");
    
    // Redirect to result.html with parameters
    header("Location: result.html?testId=$testId&score=$score&total=$total&percentage=$percentage");
    exit;
} else {
    echo "Error: Missing parameters.";
}
?>
