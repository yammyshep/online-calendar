<?php
session_start();
if (!$_SESSION["authed"]) {
?>
<!DOCTYPE html>
<html>
<head>
	<title>Calendar Login</title>
    <link rel="stylesheet" href="/res/css/styles.css">
    <script src="/res/js/jquery-3.6.0.min.js"></script>
    <script src="/res/js/modalCommon.js"></script>
    <script src="/res/js/users.js"></script>
</head>
<body>
	<div id="loginbox">
		<form>
			<h1>Calendar Login</h1>
			<div id="loginbox-error" class="errorholder"></div>
			<label for="email">Email:</label><br>
			<input id="email" onchange="onEmailUpdate()" placeholder="johndoe@example.com"/><br>
			<label for="pass">Password:</label><br>
			<input id="pass" type="password" placeholder="************" onchange="onPassUpdate()"/><br>
			<button id="submit-button" type="button" onclick="onSubmitClick()">Log In</button>
            or <button type="button" onclick="onSignupClick()">Create Account</button>
		</form>
	</div>
    <div id="user-creator" class="modal-window editor">
        <div>
            <div class="modal-close" onclick="onModalClose('user-creator')">&times;</div>
            <h1>Create Account</h1>
            <div id="user-creator-error" class="errorholder"></div>
            <table>
                <tr><td><label for="cracc-email">Email:</label></td><td><input id="cracc-email" placeholder="johndoe@example.com"/></td></tr>
                <tr><td><label for="cracc-pass">Password:</label></td><td><input id="cracc-pass" type="password" placeholder="************"></td></tr>
                <tr><td><label for="cracc-pass-conf">Confirm:</label></td><td><input id="cracc-pass-conf" type="password" placeholder="************"></td></tr>
            </table>
            <div class="modal-save" onclick="createAccount()">Create</div>
        </div>
    </div>
    <div class="loader" id="loader">
        <div>
            <img src="/res/img/spinner.svg">
        </div>
    </div>
</body>
</html>
<?php
} else {
	header("Location: /");
?>
	<p>Redirecting...</p>
<?php
}
?>