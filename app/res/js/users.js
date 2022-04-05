function validateEmail(email) {
    let errorbox = $("#loginbox-error");
    errorbox.removeClass("errors");
    errorbox.html("");
    if (!/^[\w!#$%&'*+\-\/=?^`{}|~]+@\w+\.\w+/.test(email)) {
        errorbox.html("Invalid email!");
        errorbox.addClass("errors");
        return false;
    } else {
        return true;
    }
}

function validatePassword(pass) {
    let errorbox = $("#loginbox-error");
    errorbox.removeClass("errors");
    errorbox.html("");
    if (pass !== "") {
        return true;
    } else {
        errorbox.html("Password cannot be empty!");
        errorbox.addClass("errors");
    }
}

function authenticate() {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "/api/userauth.php", true);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                // Login success, redirect
                window.location = "/";
            } else {
                // Login failure, show error
                document.getElementById("loginbox-error").innerHTML = "Error: "+JSON.parse(this.response).error;
                document.getElementById("loginbox-error").classList.add("errors");
                document.getElementById("loginbox").classList.add("shake");
                setTimeout(function () {
                    $("#loginbox").removeClass("shake");
                }, 500);
            }
        }
    }
    let email = document.getElementById("email").value;
    let pass = document.getElementById("pass").value;
    let data = JSON.stringify({"email": email, "pass": pass});
    xhr.send(data);
}

function onEmailUpdate() {
    validateEmail($("#email").val());
}

function onPassUpdate() {
    validatePassword($("#pass").val());
}

function onSubmitClick() {
    if (validateEmail($("#email").val())) {
        if (validatePassword($("#pass").val())) {
            authenticate();
            return;
        }
    }
    $("#loginbox").addClass("shake");
    setTimeout(function () {
        $("#loginbox").removeClass("shake");
    }, 500);
}

function onSignupClick() {
    $("#user-creator").addClass("show-window");
}

function createAccount() {
    $("#loader").addClass("show-loader");
    $("#user-creator-error").html("");
    $("#user-creator-error").removeClass("errors");
    let email = $("#cracc-email").val();
    let pass1 = $("#cracc-pass").val();
    let pass2 = $("#cracc-pass-conf").val();

    if (validateEmail(email) &&
    validatePassword(pass1)) {
        if (pass1 === pass2) {
            let userObj = {user: {email: email, pass: pass1}};
            $.ajax({
                url: "/api/user.php",
                method: "POST",
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                data: JSON.stringify(userObj),
                success: function () {
                    $("#loader").removeClass("show-loader");
                    $("#user-creator").removeClass("show-window");
                    alert("Account created, please sign in.");
                }
            }).fail(function () {
                $("#loader").removeClass("show-loader");
                alert("Unable to create your account. Please try again later.");
            });
        } else {
            $("#loader").removeClass("show-loader");
            $("#user-creator-error").html("Passwords do not match!");
            $("#user-creator-error").addClass("errors");
            return;
        }
    }
    $("#loader").removeClass("show-loader");
    $("#user-creator-error").html($("#loginbox-error").html());
    $("#user-creator-error").attr("class", $("#loginbox-error").attr("class"));
    $("#loginbox-error").html("");
    $("#loginbox-error").removeClass("errors");
}

function updateEmail() {
    $("#loader").addClass("show-loader");
    $("#user-editor-error").html("");
    $("#user-editor-error").removeClass("errors");
    let id = $("#edit-user-id").val();
    let email1 = $("#edit-user-email-1").val();
    let email2 = $("#edit-user-email-2").val();

    if (email1 !== email2) {
        $("#loader").removeClass("show-loader");
        $("#user-editor-error").html("Email addresses must match!");
        $("#user-editor-error").addClass("errors");
        return;
    }

    if (!validateEmail(email1)) {
        $("#loader").removeClass("show-loader");
        $("#user-editor-error").html("Email address is invalid!");
        $("#user-editor-error").addClass("errors");
        return;
    }

    let userObj = {
        user: {
            id: id,
            email: email1
        }
    };

    $.ajax({
        url: "/api/user.php",
        method: "POST",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        data: JSON.stringify(userObj),
        success: function () {
            alert("Email address changed.");
            window.location = "/";
        }
    }).fail(function () {
        $("#loader").removeClass("show-loader");
        alert("Unable to change email. Please try again later.");
    });
}

function updatePassword() {
    $("#loader").addClass("show-loader");
    $("#user-editor-error").html("");
    $("#user-editor-error").removeClass("errors");
    let id = $("#edit-user-id").val();
    let pass1 = $("#edit-user-pass-1").val();
    let pass2 = $("#edit-user-pass-2").val();

    if (pass1 !== pass2) {
        $("#loader").removeClass("show-loader");
        $("#user-editor-error").html("Passwords must match!");
        $("#user-editor-error").addClass("errors");
        return;
    }

    if (!validatePassword(pass1)) {
        $("#loader").removeClass("show-loader");
        $("#user-editor-error").html("Password must not be empty!");
        $("#user-editor-error").addClass("errors");
        return;
    }

    let userObj = {
        user: {
            id: id,
            pass: pass1
        }
    };

    $.ajax({
        url: "/api/user.php",
        method: "POST",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        data: JSON.stringify(userObj),
        success: function () {
            alert("Password changed.");
            window.location = "/";
        }
    }).fail(function () {
        $("#loader").removeClass("show-loader");
        alert("Unable to change password. Please try again later.");
    });
}
