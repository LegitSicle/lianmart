<?php
use app\models\CreateAccount;

function createInput(string $type, string $name, ?CreateAccount $account, string $placeholder = ""): string {
    $html = "<input type=\"{$type}\" id=\"{$name}\" name=\"{$name}\" placeholder=\"{$placeholder}\" class=\"field-input\"";
    $html .= isset($account) ? " value=\"" . safe_encode($account->{$name}) . "\">\n" : ">\n";
    if (isset($account) && $account->hasError($name)) {
        $html .= "<p class=\"field-error\">" . safe_encode($account->getFirstError($name)) . "</p>";
    }
    return $html;
}

function createPassword(string $name, ?CreateAccount $account, string $iconPath, string $placeholder = ""): string {
    $html = "<div class=\"password-field\">\n";
    $html .= "<input type=\"password\" id=\"{$name}\" name=\"{$name}\" placeholder=\"{$placeholder}\" class=\"field-input password\"";
    $html .= isset($account) ? " value=\"" . safe_encode($account->{$name}) . "\">\n" : ">\n";
    $html .= "<button type=\"button\" onclick=\"toggleShow('#{$name}')\"><img width=\"25\" src=\"$iconPath/hide.svg\" alt=\"show\" id=\"{$name}-button\"></button>\n";
    $html .= "</div>";
    if (isset($account) && $account->hasError($name)) {
        $html .= "<p class=\"field-error\">" . safe_encode($account->getFirstError($name)) . "</p>";
    }
    return $html;
}

/** @var CreateAccount $model */
?>
<div class="right-content">
    <h1 class="main-heading">Create your account</h1>
    <p class="main-paragraph">Portal access and delivery details, set up in three short steps.</p>
    <div class="steps">
        <div class="step">
            <p class="step-number current-step">1</p>
            <p>Account</p>
        </div>
        <div class="gap-line"></div>
        <div class="step">
            <p class="step-number">2</p>
            <p>Personal</p>
        </div>
        <div class="gap-line"></div>
        <div class="step">
            <p class="step-number">3</p>
            <p>Delivery</p>
        </div>
    </div>
    <div>
        <h2>Account Details</h2>
        <p>Use this email and password every time you sign in.</p>
    </div>
    <form method="post">
        <div>
            <label for="email" class="field-label"><strong>Email</strong></label>
            <br>
            <?php echo createInput("email", "email", $model, "name@example.com") ?>
        </div>
        <div>
            <label for="password" class="field-label"><strong>Password</strong> &lpar; At least 8 characters &rpar;</label>
            <br>
            <?php echo createPassword("password", $model, "/lianmart/assets/images/options", "Enter your password") ?>
        </div>
        <div>
            <label for="confirmPassword" class="field-label"><strong>Confirm Password</strong></label>
            <br>
            <?php echo createPassword("confirmPassword", $model, "/lianmart/assets/images/options", "Confirm Password") ?>
        </div>
        <button type="submit" name="action" value="continue" class="styled-button primary">Continue</button>
    </form>
    <p class="redirect">Already have an account? <a href="/lianmart/login"><strong>Sign in</strong></a></p>
</div>
<script src="/lianmart/assets/js/password.js"></script>