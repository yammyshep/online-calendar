<?php
	// Set output to json
	header("Content-Type: application/json");

	$json = file_get_contents('php://input');
	$data = json_decode($json);

	$output = Array();

	require_once '../config.php';

	// If either email or pass is missing
	if ($data->email == "" || $data->pass == "") {
		$output['success'] = false;
		$output['error'] = "email and password required";
		// Set bad request and output error
		http_response_code(400);
		echo(json_encode($output));
		exit();
	}

	$email = $data->email;
	$pass = $data->pass;

	// Establish a connection to the database using mysqli
	$conn = new mysqli($auth_host, $auth_username, $auth_password, $auth_database);

	$stmt = $conn->prepare("SELECT id, pass FROM users WHERE email=?");
	$stmt->bind_param("s", $email);

	$stmt->execute();
	$stmt->store_result();

	$stmt->bind_result($res_id, $res_pass);

	// If nothing comes back from the database, username or password is incorrect or does not exist
	if ($stmt->num_rows == 0) {
		$output['success'] = false;
		$output['error'] = "bad email/password";
		// Set bad request and output error
		http_response_code(401);
		echo(json_encode($output));
		exit();
	}

	$stmt->fetch();

	if (password_verify($pass, $res_pass)) {
		session_start();
		$_SESSION['authed'] = true;
		$_SESSION['user']['id'] = $res_id;
		$_SESSION['user']['email'] = $email;
		$_SESSION['last_access'] = time(); // Update to prevent php's GC from wiping session


		$output['success'] = true;
		$output['user']['id'] = $res_id;
		$output['user']['email'] = $email;
		http_response_code(200);
		echo(json_encode($output));
	} else {
		$output['success'] = false;
		$output['error'] = "bad email/password";
		// Set bad request and output error
		http_response_code(401);
		echo(json_encode($output));
	}

?>