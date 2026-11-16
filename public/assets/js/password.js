function toggleShow(id) {
    passwordField = document.querySelector(id);
    showButton = document.querySelector(`${id}-button`);
    if (passwordField.type === "password") {
        passwordField.type = "text";
        showButton.src = showButton.src.replace("hide", "show");
    } else if (passwordField.type === "text") {
        passwordField.type = "password";
        showButton.src = showButton.src.replace("show", "hide");
    }
}