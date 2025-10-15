<?php
session_start();
header('Content-Type: application/json');

// Only accept POST to logout
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['status' => 'method_not_allowed']);
	exit;
}

// Destroy the session
session_unset();
session_destroy();

echo json_encode(['status' => 'ok']);
