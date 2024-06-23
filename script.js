document.addEventListener('DOMContentLoaded', function () {
    let currentQuestionIndex = 0; // Index to track the current question
    let questions = []; // Array to store loaded questions

    // Get the test ID from the URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const testId = urlParams.get('testId');

    if (!testId) {
        alert('Test ID is missing!');
        return;
    }

    // Fetch the test data using AJAX
    fetch(`get_test.php?testId=${testId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'error') {
                throw new Error(data.message);
            }

            if (!data.data.test || !data.data.questions) {
                throw new Error('Invalid test data received');
            }

            // Store questions data
            questions = data.data.questions;

            // Display the test details
            document.querySelector('.test-name').textContent = data.data.test.test_name;
            document.querySelector('.test-id span:last-child').textContent = data.data.test.test_id;

            // Load the first question
            loadQuestion(currentQuestionIndex);
        })
        .catch(error => {
            console.error('Error fetching test data:', error);
            alert(`Error fetching test data: ${error.message}`);
        });

    function loadQuestion(index) {
        const quizContainer = document.querySelector('.test-quiz-container');
        quizContainer.innerHTML = '';

        const question = questions[index];
        const questionElement = document.createElement('div');
        questionElement.classList.add('quiz-question');
        questionElement.style.display = 'block'; // Show the current question

        const questionText = document.createElement('div');
        questionText.classList.add('question-text');
        questionText.textContent = `Question ${index + 1}: ${question.question_text}`;

        const optionsContainer = document.createElement('div');
        optionsContainer.classList.add('options');

        question.options.forEach(option => {
            const optionElement = document.createElement('div');
            optionElement.classList.add('option');

            const optionInput = document.createElement('input');
            optionInput.type = 'radio';
            optionInput.name = `question_${question.id}`;
            optionInput.value = option.id;

            const optionLabel = document.createElement('label');
            optionLabel.textContent = option.option_text;

            optionElement.appendChild(optionInput);
            optionElement.appendChild(optionLabel);
            optionsContainer.appendChild(optionElement);
        });

        questionElement.appendChild(questionText);
        questionElement.appendChild(optionsContainer);
        quizContainer.appendChild(questionElement);

        updateQuestionMarkers(); // Update question markers based on current question
    }

    function updateQuestionMarkers() {
        const totalQuestions = questions.length;
        const answeredCount = questions.filter(q => q.answered).length;
        const skippedCount = questions.filter(q => q.skipped).length;

        document.querySelector('.completed-question').textContent = answeredCount;
        document.querySelector('.na-question').textContent = skippedCount;
        document.querySelector('.una-question').textContent = totalQuestions - (answeredCount + skippedCount);
    }

    window.saveAnswer = function () {
        const selectedOption = document.querySelector(`input[name="question_${questions[currentQuestionIndex].id}"]:checked`);
        if (selectedOption) {
            questions[currentQuestionIndex].answered = true;
            questions[currentQuestionIndex].skipped = false;
            updateQuestionMarkers();
            showNextQuestion();
        } else {
            alert('Please select an option before saving.');
        }
    };

    window.skipQuestion = function () {
        questions[currentQuestionIndex].skipped = true;
        questions[currentQuestionIndex].answered = false;
        updateQuestionMarkers();
        showNextQuestion();
    };

    function showNextQuestion() {
        currentQuestionIndex++;
        if (currentQuestionIndex < questions.length) {
            loadQuestion(currentQuestionIndex);
        } else {
            // Hide question container and show submit button when all questions are answered or skipped
            document.querySelector('.test-quiz-container').style.display = 'none';
            document.querySelector('.submit-button').style.display = 'block';
        }
    }

    window.submitQuiz = function () {
        const answers = {};
        questions.forEach(question => {
            if (question.answered) {
                const selectedOption = document.querySelector(`input[name="question_${question.id}"]:checked`);
                if (selectedOption) {
                    answers[question.id] = selectedOption.value;
                }
            }
        });
    
        console.log('Submitting answers:', answers); // Log the answers
    
        fetch('submit_answers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                testId: testId,
                answers: answers,
            }),
        })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Network response was not ok: ${response.status} - ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Server response data:', data); // Log the response data
                if (data.status === 'error') {
                    throw new Error(data.message);
                }
                window.location.href = `result.php?testId=${testId}&score=${data.score}&total=${data.total}`;
            })
            .catch(error => {
                console.error('Error submitting quiz:', error);
                alert(`Error submitting quiz: ${error.message}`);
            });
    };
})    