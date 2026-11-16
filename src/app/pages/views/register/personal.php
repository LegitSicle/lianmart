<?php
use app\models\CreatePersonal;

function createInput(string $type, string $name, ?CreatePersonal $personal, string $placeholder = "", string $min = ""): string {
    $html = "<input type=\"{$type}\" id=\"{$name}\" name=\"{$name}\" class=\"field-input\"";
    $html .= isset($placeholder) ? " placeholder=\"{$placeholder}\"" : "";
    $html .= isset($min) ? " min=\"{$min}\"" : "";
    $html .= isset($personal) ? " value=\"" . safe_encode($personal->{$name}) . "\">\n" : ">\n";
    if (isset($personal) && $personal->hasError($name)) {
        $html .= "<p class=\"field-error\">" . safe_encode($personal->getFirstError($name)) . "</p>";
    }
    return $html;
}

/** @var CreatePersonal $model */
?>
<div class="right-content">
    <h1 class="main-heading">Create your account</h1>
    <p class="main-paragraph">Portal access and delivery details, set up in three short steps.</p>
    <div class="steps">
        <div class="step">
            <p class="step-number">1</p>
            <p>Account</p>
        </div>
        <div class="gap-line"></div>
        <div class="step">
            <p class="step-number current-step">2</p>
            <p>Personal</p>
        </div>
        <div class="gap-line"></div>
        <div class="step">
            <p class="step-number">3</p>
            <p>Delivery</p>
        </div>
    </div>
    <div>
        <h2>Personal Details</h2>
        <p>These help us identify your account and contact you about orders.</p>
    </div>
    <form method="post">
        <div class="form-grid">
            <div>
                <label for="firstname" class="field-label"><strong>First Name</strong></label>
                <br>
                <?php echo createInput("text", "firstname", $model, "Juan") ?>
            </div>
            <div>
                <label for="lastname" class="field-label"><strong>Last Name</strong></label>
                <br>
                <?php echo createInput("text", "lastname", $model, "Dela Cruz") ?>
            </div>
            <div>
                <label for="birthdate" class="field-label"><strong>Birthdate</strong> &lpar; Optional &rpar;</label>
                <br>
                <?php echo createInput("date", "birthdate", $model, "", "1900-01-01") ?>
            </div>
            <div>
                <label for="phoneNumber" class="field-label"><strong>Mobile Number</strong></label>
                <br>
                <?php echo createInput("tel", "phoneNumber", $model, "9171234567") ?>
            </div>
        </div>
        <div class="submit-buttons">
            <button type="submit" name="action" value="back" class="styled-button other-option back">&larr; Back</button>
            <button type="submit" name="action" value="continue" class="styled-button primary">Continue</button>
        </div>
    </form>
    <p class="redirect">Already have an account? <a href="/lianmart/login"><strong>Sign in</strong></a></p>
</div>