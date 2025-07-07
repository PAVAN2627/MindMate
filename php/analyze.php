<?php
session_start();
include 'config.php';

$user_id = $_SESSION['user_id'] ?? null;
$text    = trim($_POST['entry'] ?? '');

if (!$user_id)       die('User not logged in.');
if ($text === '')    die('No mood text provided.');

/* Optional: add columns if they don’t exist (idempotent) */
$conn->query("
  ALTER TABLE mood_entries
    ADD COLUMN IF NOT EXISTS ai_quote TEXT,
    ADD COLUMN IF NOT EXISTS analyzed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
");

/* 1️⃣  Fetch the user’s latest entry */
$stmt = $conn->prepare("
    SELECT id, mood_text, sentiment
    FROM mood_entries
    WHERE user_id = ?
    ORDER BY analyzed_at DESC
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$latest = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* 2️⃣  Decide whether to insert or just update quote */
$is_same_text = $latest && (strcasecmp(trim($latest['mood_text']), $text) === 0);
$flask    = 'http://127.0.0.1:5000';
$sentiment = $latest['sentiment'] ?? 'unknown';   // default

/* Get sentiment only if this is a brand‑new entry */
if (!$is_same_text) {
    $sentResp = file_get_contents(
        $flask.'/analyze',
        false,
        stream_context_create([
            'http'=>[
                'method'=>'POST',
                'header'=>"Content-Type: application/json\n",
                'content'=>json_encode(['text'=>$text])
            ]
        ])
    );
    if ($sentResp) {
        $sentiment = json_decode($sentResp,true)['sentiment'] ?? $sentiment;
    }
}

/* 3️⃣  Always get a fresh quote based on (current) sentiment */
$quote = 'Stay strong. Better days are coming!';
$quoteResp = file_get_contents(
    $flask.'/generate_quote',
    false,
    stream_context_create([
        'http'=>[
            'method'=>'POST',
            'header'=>"Content-Type: application/json\n",
            'content'=>json_encode(['mood'=>$sentiment])
        ]
    ])
);
if ($quoteResp) {
    $quote = json_decode($quoteResp,true)['quote'] ?? $quote;
}

/* 4️⃣  Insert new row OR update quote only */
if ($is_same_text) {
    /* Update quote for the existing latest record */
    $upd = $conn->prepare("
        UPDATE mood_entries
        SET ai_quote = ?
        WHERE id = ?
    ");
    $upd->bind_param("si", $quote, $latest['id']);
    $upd->execute();
    $upd->close();
} else {
    /* Insert brand‑new record */
    $ins = $conn->prepare("
        INSERT INTO mood_entries
          (user_id, mood_text, sentiment, ai_quote, analyzed_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $ins->bind_param("isss", $user_id, $text, $sentiment, $quote);
    $ins->execute();
    $ins->close();
}

/* 5️⃣  Redirect back to dashboard */
header("Location: dashboard.php");
exit;
?>
