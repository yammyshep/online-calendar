<?php
/*
 * Logs out a logged-in user
 */
header("Content-Type: application/json");
session_start();
$out = array();
if ($_SESSION['authed']) {
	$_SESSION['authed'] = false;
	$out['success'] = true;
	$out['message'] = $_SESSION['user']['email']." signed out";
} else {
	http_response_code(401);
	$out['success'] = false;
	$out['message'] = "not authorized";
}

echo(json_encode($out));
