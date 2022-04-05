<?php
/*
 * deals with operations regarding users
 * GET retrieves from db, POST creates/updates, DELETE deletes
 */

header("Content-Type: application/json");
$out = array();

require_once '../config.php';

// Establish a connection to the database using mysqli
$conn = new mysqli($auth_host, $auth_username, $auth_password, $auth_database);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	session_start();

	// Verify user is logged in
	if (!$_SESSION['authed']) {
		http_response_code(401);
		$out['success'] = false;
		$out['message'] = "session expired/not authorized";
		echo(json_encode($out));
		exit();
	}

	// Update session to keepalive
	$_SESSION['last_access'] = time();

} else if ($_SERVER['REQUEST_METHOD'] === "POST") {
	$json = file_get_contents('php://input');
	$data = json_decode($json);

	// If user specified an id
	if (isset($data->user->id)) {
		// Update user
		session_start();

		// Verify user is logged in
		if (!$_SESSION['authed'] || $_SESSION['user']['id'] != $data->user->id) {
			http_response_code(401);
			$out['success'] = false;
			$out['message'] = "session expired/not authorized";
			$conn->close();
			echo(json_encode($out));
			exit();
		}

		// Update session to keepalive
		$_SESSION['last_access'] = time();

		if (isset($data->user->email)) {
			$emailExp = "/^[\w!#$%&'*+\-\/=?^`{}|~]+@\w+\.\w+/";
			if (!preg_match($emailExp, $data->user->email)) {
				http_response_code(400);
				$out['success'] = false;
				$out['message'] = "invalid email";
				$conn->close();
				echo(json_encode($out));
				exit();
			}

			$email = $data->user->email;

			$stmt = $conn->prepare("UPDATE users SET email=? WHERE id=?");
			$stmt->bind_param("si", $email, $_SESSION['user']['id']);
			$stmt->execute();

			if ($stmt->affected_rows > 0) {
				$out['success'] = true;
				$out['message'] = "user updated";
				$_SESSION['user']['email'] = $email;
				$out['user'] = array("id"=>$_SESSION['user']['id'], "email"=>$email);
			} else {
				http_response_code(500);
				$out['success'] = false;
				$out['message'] = "database error";
				$conn->close();
				echo(json_encode($out));
				exit();
			}
		}

		if (isset($data->user->pass)) {
			$pass = password_hash($data->user->pass, PASSWORD_DEFAULT);

			$stmt = $conn->prepare("UPDATE users SET pass=? WHERE id=?");
			$stmt->bind_param("si", $pass, $_SESSION['user']['id']);
			$stmt->execute();

			if ($stmt->affected_rows > 0) {
				$out['success'] = true;
				$out['message'] = "user updated";
				$out['user'] = array("id"=>$_SESSION['user']['id'], "email"=>$_SESSION['user']['email']);
				$_SESSION['authed'] = false; // Deauthorize when changing password
			} else {
				http_response_code(500);
				$out['success'] = false;
				$out['message'] = "database error";
			}
		}

		if (!isset($data->user->email) && !isset($data->user->pass)) {
			http_response_code(400);
			$out['success'] = false;
			$out['message'] = "email or password required";
		}
	} else {
		// Create user
		if (isset($data->user->email) && isset($data->user->pass)) {
			$emailExp = "/^[\w!#$%&'*+\-\/=?^`{}|~]+@\w+\.\w+/";
			if (!preg_match($emailExp, $data->user->email)) {
				http_response_code(400);
				$out['success'] = false;
				$out['message'] = "invalid email";
				$conn->close();
				echo(json_encode($out));
				exit();
			}

			$email = $data->user->email;

			$pass = password_hash($data->user->pass, PASSWORD_DEFAULT);

			$stmt = $conn->prepare("INSERT INTO users (email, pass) VALUES (?,?)");
			$stmt->bind_param("ss", $email, $pass);
			$stmt->execute();

			if ($stmt->affected_rows > 0) {
				$out['success'] = true;
				$out['message'] = "account created";
				$out['user'] = array("id"=>$conn->insert_id, "email"=>$email);
			} else {
				http_response_code(500);
				$out['success'] = false;
				$out['message'] = "database error";
			}
		} else {
			http_response_code(400);
			$out['success'] = false;
			$out['message'] = "email and password required";
		}
	}
} else if ($_SERVER['REQUEST_METHOD'] === "DELETE") {
	session_start();

	// Verify user is logged in
	if (!$_SESSION['authed']) {
		http_response_code(401);
		$out['success'] = false;
		$out['message'] = "session expired/not authorized";
		$conn->close();
		echo(json_encode($out));
		exit();
	}

	// Update session to keepalive
	$_SESSION['last_access'] = time();

	if ((!$_GET['confirmDeletion'] || !$_GET['email']) ||
		$_GET['email'] !== $_SESSION['user']['email']) {
		http_response_code(400);
		$out['success'] = false;
		$out['message'] = "account deletion must be confirmed";
		$conn->close();
		echo(json_encode($out));
		exit();
	}

	$stmt = $conn->prepare("DELETE users FROM users WHERE id=?");
	$stmt->bind_param("i", $_SESSION['user']['id']);
	$stmt->execute();

	if ($stmt->affected_rows > 0) {
		$out['success'] = true;
		$out['message'] = "account deleted for ".$_SESSION['user']['email'];
		$_SESSION['authed'] = false;
		$_SESSION['user'] = array();
	} else {
		http_response_code(500);
		$out['success'] = false;
		$out['message'] = "database error";
	}

} else {
	http_response_code(405);
	$out['success'] = false;
	$out['message'] = "method not allowed";
}

$conn->close();
echo(json_encode($out));
