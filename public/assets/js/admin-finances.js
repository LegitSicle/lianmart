let doughnutChart = null;
let barChart = null;

function formatK(value) {
    return value >= 1000 ? `${+(value / 1000).toFixed(1)}k` : `${value}`;
}

async function sendAJAX(to, json) {
    const response = await fetch(to, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify(json)
    });

    if (!response.ok) {
        throw new Error(`Server returned HTTP ${response.status}`);
    }

    return await response.json();
}

async function loadHeadInfo(event) {
    const totalProducts = document.getElementById("totalProducts");
    const totalOrders = document.getElementById("totalOrders");
    const totalCustomers = document.getElementById("totalCustomers");
    const totalRevenue = document.getElementById("totalRevenue");

    const productGrowth = document.getElementById("productGrowth");
    const revenueGrowth = document.getElementById("revenueGrowth");
    const orderGrowth = document.getElementById("orderGrowth");
    const customerGrowth = document.getElementById("customerGrowth");


    let optionValue = 1;
    if (event && event.target && event.target.value) {
        optionValue = event.target.value;
    }

    const headInfo = await sendAJAX("/lianmart/api/admin/finance/head", {
        option: optionValue
    });

    if (!headInfo.success) {
        throw new Error(`Invalid Option! ${optionValue}`);
    }

    function displayGrowth(element, type) {
        let growthValue = 0;
        const lastTotal = headInfo[`lastTotal${type}`];
        const currentTotal = headInfo[`total${type}`];
        
        if ((lastTotal === null || lastTotal == 0) && (currentTotal === null || currentTotal == 0)) {
            growthValue = 0;
        } else if (lastTotal === null || lastTotal == 0) {
            growthValue = 100;
        } else if (currentTotal === null || currentTotal == 0) {
            growthValue = 0;
        } else {
            growthValue = Number(((currentTotal - lastTotal) / lastTotal).toFixed(2));
        }

        if (growthValue > 0) {
            element.textContent = `\u2191${Math.abs(growthValue)}%`;
            element.classList.remove("down");
        } else if (growthValue < 0) {
            element.textContent = `\u2193${Math.abs(growthValue)}%`;
            element.classList.add("down");
        } else {
            element.textContent = `\u2193${Math.abs(growthValue)}%`;
            element.classList.add("down");
        }
    }

    totalProducts.textContent = headInfo.totalProducts;
    totalOrders.textContent = headInfo.totalOrders;
    totalCustomers.textContent = headInfo.totalCustomers;
    totalRevenue.textContent = headInfo.totalRevenue ?? 0;

    displayGrowth(productGrowth, "Products");
    displayGrowth(revenueGrowth, "Revenue");
    displayGrowth(orderGrowth, "Orders");
    displayGrowth(customerGrowth, "Customers");

    const now = new Date();
    let timePassed = "";
    switch (optionValue) {
        case "1":
            const daysPassed = now.getDay();
            timePassed = `${daysPassed} days`;
            break;
        case "2":
            const dayOfMonth = now.getDate();
            const weeksPassedInMonth = Math.ceil(dayOfMonth / 7);
            timePassed = `${weeksPassedInMonth} weeks`;
            break;
        case "3": 
            const monthsPassedInYear = now.getMonth();
            timePassed = `${monthsPassedInYear} months`;
            break;
    }

    const timespans = document.querySelectorAll('.timespan');
    for (const timespan of timespans) {
        timespan.textContent = timePassed;
    }
}

async function loadBars(event) {
    let optionValue = 1;
    if (event && event.target && event.target.value) {
        optionValue = event.target.value;
    }
    
    const salesOverview = await sendAJAX("/lianmart/api/admin/finance/sales", {
        option: optionValue
    });

    if (!salesOverview.success) {
        throw new Error(`Invalid Option! ${optionValue}`);
    }

    if (barChart) {
        barChart.destroy();
    }

    barChart = new Chart(document.getElementById("bar-chart"), {
        type: "bar",
        data: {
            labels: salesOverview.dates,
            datasets: [{
                label: "Sales",
                data: salesOverview.sales,
                borderWidth: 1,
                backgroundColor: ['#246B4A']
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => `₱${formatK(value)}`
                    }
                }
            }
        }
    });
}

async function loadDoughnut(event) {
    let optionValue = 1;
    if (event && event.target && event.target.value) {
        optionValue = event.target.value;
    }
    
    const orderData = await sendAJAX("/lianmart/api/admin/finance/orders", {
        option: optionValue
    });

    if (!orderData.success) {
        throw new Error(`Invalid Option! ${optionValue}`);
    }

    if (doughnutChart) {
        doughnutChart.destroy();
    }

    doughnutChart = new Chart(document.getElementById("doughnut-chart"), {
        type: "doughnut",
        data: {
            labels: ["Completed", "Processing", "Cancelled"],
            datasets: [{
                label: "Order Status",
                data: [
                    orderData.made.Completed ?? 0, 
                    orderData.made.Processing ?? 0, 
                    orderData.made.Cancelled ?? 0
                ],
                backgroundColor: [
                    '#246B4A',
                    '#FFCE3D',
                    '#FF0005'
                ],
                borderWidth: 1
            }]
        },
        options: {
            plugins: {
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.label}: ${ctx.parsed}`
                    }
                },
                legend: {
                    position: 'bottom',
                    labels: {
                        generateLabels: function(chart) {
                            const labels = chart.data.labels;
                            const values = chart.data.datasets[0].data;
                            const bgColors = chart.data.datasets[0].backgroundColor;

                            return values.map((value, index) => ({
                                text:  labels[index] + ": " + value,
                                fillStyle: bgColors[index],
                            }));
                        }
                    }
                },
                annotation: {
                    annotations: {
                        dLabel: {
                            type: 'doughnutLabel',
                            content: ({chart}) => [
                                chart.getDatasetMeta(0).total,
                                'Total'
                            ],
                            font: [
                                {family: 'Google Sans Flex', size: 40},
                                {family: 'Google Sans Flex', size: 20}
                            ],
                            color: ['black', 'grey']
                        }
                    }
                }
            }
        }
    });
}

document.addEventListener("DOMContentLoaded", loadDoughnut);
document.addEventListener("DOMContentLoaded", loadBars);
document.getElementById("ordersMade").addEventListener("change", loadDoughnut);
document.getElementById("salesOverview").addEventListener("change", loadBars);
document.getElementById("headInfo").addEventListener("change", loadHeadInfo);