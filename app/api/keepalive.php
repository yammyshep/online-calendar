<?php
// Set output to json
header("Content-Type: application/json");

session_start();
$out = Array();

if ($_SESSION['authed']) {
	$_SESSION['last_access'] = time();
	http_response_code(200);
	$out['success'] = true;
	$out['message'] = "OK";
	$out['user']['id'] = $_SESSION['user']['id'];
	$out['user']['email'] = $_SESSION['user']['email'];
} else {
	http_response_code(401);
	$out['success'] = false;
	$out['message'] = "session expired/not authorized";
}

echo(json_encode($out));