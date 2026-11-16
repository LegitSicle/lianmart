const min = document.getElementById("min");
const max = document.getElementById("max");
const low = document.getElementById("low");
const high = document.getElementById("high");
const track = document.getElementById("track");
const minGap = 10;

function updatePriceSlider() {
    let lowValue = parseInt(low.value);
    let highValue = parseInt(high.value);

    if (highValue - lowValue < minGap) {
        if (this === low) {
            low.value = highValue - minGap;
            lowValue = highValue - minGap;
        } else {
            high.value = lowValue + minGap;
            highValue = lowValue + minGap;
        }
    }

    min.textContent = lowValue;
    max.textContent = highValue;

    const percentMin = (lowValue / low.max) * 100;
    const percentMax = (highValue / high.max) * 100;

    track.style.left = percentMin + '%';
    track.style.right = (100 - percentMax) + '%';
}

low.addEventListener("input", updatePriceSlider);
high.addEventListener("input", updatePriceSlider);
updatePriceSlider();