<div class="mail-image-container">
    <img src="/lianmart/assets/images/mail.svg">
</div>
<div class="form-container">
    <div class="form-header">
        <h2>Contact Us</h2>
    </div>
    <form>
        <div class="form-name-container">
            <div class="first-name-container">
                <label>First Name</label>
                <input type="text" name="first-name" required>
            </div>
            <div class="last-name-container">
                <label>Last Name</label>
                <input type="text" name="last-name" required>
            </div>
        </div>
        <div class="form-email-container">
            <label>Email address</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-phone-container">
            <label>Phone number</label>
            <input 
            type="tel" 
            id="phone" 
            name="phone" 
            maxlength="11" 
            pattern="[0-9]{11}"
            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
            >
        </div>
        <div class="form-message-container">
            <label>Message</label>
            <textarea name="message" required ></textarea>
        </div>
        <div class="form-action-container">
            <button type="reset">Clear</button>
            <button type="submit">Submit</button>
        </div>
    </form>
</div>