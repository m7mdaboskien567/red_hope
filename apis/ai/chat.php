<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../database/config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$pdo = getDB();


$envFile = __DIR__ . '/../../wp-private/.env';
$apiKey = '';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        list($name, $value) = explode('=', $line, 2);
        if (trim($name) === 'AI_API_KEY') {
            $apiKey = trim($value);
            break;
        }
    }
}

if (!$apiKey) {
    echo json_encode(['success' => false, 'message' => 'AI Config Error']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$userMessage = $data['message'] ?? '';
$sessionId = $data['sessionId'] ?? null;
$userId = $_SESSION['user_id'];

if (empty($userMessage)) {
    echo json_encode(['success' => false, 'message' => 'Empty message']);
    exit();
}

try {

    if (!$sessionId) {
        $stmt = $pdo->prepare("INSERT INTO ai_chat_sessions (user_id, created_at, updated_at) VALUES (?, NOW(), NOW())");
        $stmt->execute([$userId]);
        $sessionId = $pdo->lastInsertId();
        $isNewSession = true;
    } else {
        $isNewSession = false;
        $stmt = $pdo->prepare("UPDATE ai_chat_sessions SET updated_at = NOW() WHERE session_id = ?");
        $stmt->execute([$sessionId]);
    }


    $stmt = $pdo->prepare("INSERT INTO ai_chat_messages (session_id, sender, message_content, sent_at) VALUES (?, 'User', ?, NOW())");
    $stmt->execute([$sessionId, $userMessage]);


    $aiConfig = require __DIR__ . '/../../includes/ai_config.php';
    $models = $aiConfig['models'];

    function callGemini($payload, $apiKey, $models, $maxRetries = 2)
    {
        $debugLog = [];
        foreach ($models as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $apiKey;
            for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'x-goog-api-key: ' . $apiKey]);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);

                $debugLog[] = [
                    'model' => $model,
                    'attempt' => $attempt + 1,
                    'httpCode' => $httpCode,
                    'curlError' => $curlError,
                    'responsePreview' => substr($response, 0, 300)
                ];

                if ($httpCode === 200) {
                    return ['code' => 200, 'body' => json_decode($response, true), 'debug' => $debugLog];
                }
                if ($httpCode === 429 && $attempt < $maxRetries - 1) {
                    sleep(3);
                    continue;
                }
                break;
            }
        }
        return ['code' => 429, 'body' => null, 'debug' => $debugLog];
    }


    $docsContext = "";
    $metaFile = __DIR__ . '/../../assets/uploads/ai_docs_meta.json';
    if (file_exists($metaFile)) {
        $meta = json_decode(file_get_contents($metaFile), true);
        if ($meta) {
            foreach ($meta as $id => $fileData) {
                if (($fileData['status'] ?? '') === 'active') {
                    $filePath = __DIR__ . '/../../assets/uploads/' . $id;
                    if (file_exists($filePath)) {
                        $content = file_get_contents($filePath);

                        $docsContext .= "\n\n--- Document: " . $fileData['original_name'] . " ---\n" . substr($content, 0, 8000);
                    }
                }
            }
        }
    }

    $systemInstruction = "You are HopeAI, a helpful RedHope health assistant. Use Markdown (###, **, *). Concise responses.";
    if ($docsContext) {
        $systemInstruction .= "\n\nUse the following knowledge base to answer questions if relevant:\n" . $docsContext;
    }

    $payload = [
        "system_instruction" => ["parts" => [["text" => $systemInstruction]]],
        "contents" => [["parts" => [["text" => $userMessage]]]],
        "generationConfig" => ["temperature" => 0.7, "maxOutputTokens" => 1200]
    ];

    $result = callGemini($payload, $apiKey, $models);

    if ($result['code'] === 200) {
        $aiResponse = $result['body']['candidates'][0]['content']['parts'][0]['text'] ?? 'Sorry, I could not generate a response.';

        // Save AI Message
        $stmt = $pdo->prepare("INSERT INTO ai_chat_messages (session_id, sender, message_content, sent_at) VALUES (?, 'AI', ?, NOW())");
        $stmt->execute([$sessionId, $aiResponse]);

        // REMOVED: Blocking title generation. 
        // We will generate titles lazily via a separate endpoint or just use the first few words of the user message initially.
        $generatedTitle = null;
        if ($isNewSession) {
            $generatedTitle = mb_substr($userMessage, 0, 40) . (mb_strlen($userMessage) > 40 ? '...' : '');
            $uStmt = $pdo->prepare("UPDATE ai_chat_sessions SET title = ? WHERE session_id = ?");
            $uStmt->execute([$generatedTitle, $sessionId]);
        }

        echo json_encode(['success' => true, 'reply' => $aiResponse, 'sessionId' => $sessionId, 'title' => $generatedTitle]);
    } else {
        echo json_encode(['success' => false, 'message' => "AI is busy. Please try again in a few seconds.", 'debug' => $result['debug'] ?? []]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>