<?php
use app\models\database\Addresses;

function createInput(string $type, string $name, ?Addresses $delivery, string $placeholder = ""): string {
    $html = "<input type=\"{$type}\" id=\"{$name}\" name=\"{$name}\" placeholder=\"{$placeholder}\" class=\"field-input\"";
    $html .= isset($delivery) ? " value=\"" . safe_encode($delivery->{$name}) . "\">\n" : ">\n";
    if (isset($delivery) && $delivery->hasError($name)) {
        $html .= "<p class=\"field-error\">" . safe_encode($delivery->getFirstError($name)) . "</p>";
    }
    return $html;
}

/** @var Addresses $model */
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
            <p class="step-number">2</p>
            <p>Personal</p>
        </div>
        <div class="gap-line"></div>
        <div class="step">
            <p class="step-number current-step">3</p>
            <p>Delivery</p>
        </div>
    </div>
    <div>
        <h2>Delivery Details</h2>
        <p>This is the address we&apos;ll use for your active delivery profile.</p>
    </div>
    <form method="post">
        <div>
            <div class="two-fields">
                <div>
                    <label for="province" class="field-label"><strong>Province</strong></label>
                    <br>
                    <?php echo createInput("text", "province", $model, "Pangasinan") ?>
                </div>
                <div>
                    <label for="city" class="field-label"><strong>City/Municipality</strong></label>
                    <br>
                    <?php echo createInput("text", "city", $model, "Dagupan City") ?>
                </div>
            </div>
            <div>
                <label for="barangay" class="field-label"><strong>Barangay</strong></label>
                <br>
                <?php echo createInput("text", "barangay", $model, "Pantal") ?>
            </div>
            <div>
                <label for="street" class="field-label"><strong>Street</strong></label>
                <br>
                <?php echo createInput("text", "street", $model, "Street, subdivision, or village") ?>
            </div>
            <div class="two-fields">
                <div>
                    <label for="houseNumber" class="field-label"><strong>House No. / Unit</strong> &lpar; Optional &rpar;</label>
                    <br>
                    <?php echo createInput("text", "houseNumber", $model, "Blk 1 Lot 2") ?>
                </div>
                <div>
                    <label for="zipCode" class="field-label"><strong>Zip Code</strong> &lpar; Optional &rpar;</label>
                    <br>
                    <?php echo createInput("text", "zipCode", $model, "2401") ?>
                </div>
            </div>
            <div>
                <label for="landmark" class="field-label"><strong>Landmark</strong></label>
                <br>
                <?php echo createInput("text", "landmark", $model, "Besides the hospital, near the restaurant") ?>
            </div>
        </div>
        <div class="submit-buttons">
            <button type="submit" name="action" value="back" class="styled-button other-option back">&larr; Back</button>
            <button type="submit" name="action" value="continue" class="styled-button primary">Continue</button>
        </div>
    </form>
    <p class="redirect">Already have an account? <a href="/lianmart/login"><strong>Sign in</strong></a></p>
</div>