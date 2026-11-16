<?php
use app\models\LoginForm;

function createInput(string $type, string $name, ?LoginForm $login, string $placeholder = ""): string {
    $html = "<input type=\"{$type}\" id=\"{$name}\" name=\"{$name}\" placeholder=\"{$placeholder}\" class=\"field-input\"";
    $html .= isset($login) ? " value=\"" . safe_encode($login->{$name}) . "\">\n" : ">\n";
    if (isset($login) && $login->hasError($name)) {
        $html .= "<p class=\"field-error\">" . safe_encode($login->getFirstError($name)) . "</p>";
    }
    return $html;
}

function createPassword(string $name, ?LoginForm $login, string $iconPath, string $placeholder = ""): string {
    $html = "<div class=\"password-field\">\n";
    $html .= "<input type=\"password\" id=\"{$name}\" name=\"{$name}\" placeholder=\"{$placeholder}\" class=\"field-input password\"";
    $html .= isset($login) ? " value=\"" . safe_encode($login->{$name}) . "\">\n" : ">\n";
    $html .= "<button type=\"button\" onclick=\"toggleShow('#{$name}')\"><img width=\"25\" src=\"$iconPath/hide.svg\" alt=\"show\" id=\"{$name}-button\"></button>\n";
    $html .= "</div>";
    if (isset($login) && $login->hasError($name)) {
        $html .= "<p class=\"field-error\">" . safe_encode($login->getFirstError($name)) . "</p>";
    }
    return $html;
}

/** @var LoginForm $model */
?>
<div class="right-content">
    <h1 class="main-heading">Welcome Back!</h1>
    <p class="main-paragraph">Sign in to see your orders and shipments.</p>
    <form method="post">
        <div>
            <label for="email" class="field-label"><strong>Email</strong></label>
            <br>
            <?php echo createInput("email", "email", $model, "name@example.com") ?>
        </div>
        <div>
            <label for="password" class="field-label"><strong>Password</strong></label>
            <br>
            <?php echo createPassword("password", $model, "/lianmart/assets/images/options", "Enter your password") ?>
        </div>
        <div class="form-additionals">
            <div>
                <input type="checkbox" id="keep">
                <label for="keep">Keep me signed in</label>
            </div>
            <a href="/lianmart/recovery" class="recover">Forgot password?</a>
        </div>
        <input type="submit" value="Sign In" class="styled-button primary">
    </form>
    <p class="redirect">Don't have an account? <a href="/lianmart/register"><strong>Create one</strong></a></p>
</div>
<script src="/lianmart/assets/js/password.js"></script>