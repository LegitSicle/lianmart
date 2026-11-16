const low = document.getElementById('low');
const high = document.getElementById('high');
const fill = document.getElementById('fill');

const minVal = parseInt(low.min);
const maxVal = parseInt(low.max);

function updateSlider() {
    let lVal = parseInt(lo.value);
    let hVal = parseInt(hi.value);

    // Prevent min thumb from crossing over max thumb
    if (lVal > hVal) {
        lo.value = hVal;
        lVal = hVal;
    }

    // Calculate percentages for the fill track
    const leftPercent = ((lVal - minVal) / (maxVal - minVal)) * 100;
    const rightPercent = 100 - ((hVal - minVal) / (maxVal - minVal)) * 100;

    fill.style.left = leftPercent + '%';
    fill.style.right = rightPercent + '%';
}

lo.addEventListener('input', updateSlider);
hi.addEventListener('input', updateSlider);

// Run once on load to set initial positions
updateSlider();