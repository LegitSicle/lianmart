const timerId = setInterval(() => {
    const timer = document.getElementById("timer");
    let timerValue = parseInt(timer.textContent);
    timerValue--;

    if (timerValue < 0) {
        clearInterval(timerId);
        window.location.href = "/lianmart/home";
    } else {
        timer.textContent = timerValue;
    }
}, 1000);