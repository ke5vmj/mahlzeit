{% extends "layouts/main.volt" %}

{% block content %}
<div class="level is-mobile mb-5">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title is-4 mb-0">Your Trends</h1>
            <p class="subtitle is-6 has-text-grey ml-3 mt-1">Last 30 Days</p>
        </div>
    </div>
</div>

<div class="columns is-desktop">

    {# 1. WEIGHT TREND LINE CHART #}
    <div class="column is-half">
        <div class="box p-5">
            <h2 class="title is-5 mb-4 has-text-primary-dark">
                <span class="icon mr-2"><i class="fas fa-weight"></i></span>
                Weight Progress
            </h2>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="weightChart"></canvas>
            </div>
        </div>
    </div>

    {# 2. CALORIE INTAKE BAR CHART #}
    <div class="column is-half">
        <div class="box p-5">
            <h2 class="title is-5 mb-4 has-text-info-dark">
                <span class="icon mr-2"><i class="fas fa-utensils"></i></span>
                Calorie Intake
            </h2>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="calorieChart"></canvas>
            </div>
        </div>
    </div>

</div>

{# 3. FORECAST TRAJECTORY CHART (NEW) #}
<div class="columns is-desktop">
    <div class="column is-full">
        <div class="box p-5">
            <h2 class="title is-5 mb-2 has-text-warning-dark">
                <span class="icon mr-2"><i class="fas fa-chart-line"></i></span>
                30-Day Trajectory Forecast
            </h2>
            <p class="mb-5 has-text-grey">
                Based on your recent tracked average of <strong>{{ avgIntake }} kcal/day</strong> and an estimated TDEE of <strong>{{ tdee }} kcal/day</strong>,
                you are on track to <strong>{{ trendText }}</strong> over the next 30 days.
            </p>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="trajectoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

{# Include Chart.js via CDN #}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Grab the JSON data injected by Phalcon
    const labels = {{ chartLabels }};
    const weightData = {{ chartWeights }};
    const calorieData = {{ chartCalories }};
    const calorieBudget = {{ calorieBudget }};

    // --- WEIGHT CHART SETUP ---
    const ctxWeight = document.getElementById('weightChart').getContext('2d');
    new Chart(ctxWeight, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Weight (lbs)',
                data: weightData,
                borderColor: '#00d1b2',
                backgroundColor: 'rgba(0, 209, 178, 0.1)',
                borderWidth: 3,
                pointBackgroundColor: '#00d1b2',
                pointRadius: 3,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: false,
                    grace: '5%'
                }
            }
        }
    });

    // --- CALORIE CHART SETUP ---
    const barColors = calorieData.map(cals => cals > calorieBudget ? '#f14668' : '#3e8ed0');
    const ctxCalorie = document.getElementById('calorieChart').getContext('2d');
    new Chart(ctxCalorie, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Calories Consumed',
                data: calorieData,
                backgroundColor: barColors,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        afterLabel: function() {
                            return `Target: ${calorieBudget} cal`;
                        }
                    }
                }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // --- TRAJECTORY CHART SETUP (NEW) ---
    const projLabels = {{ projLabels }};
    const projWeights = {{ projWeights }};
    const targetWeights = {{ targetWeights }};

    // Check if target weights array is empty, otherwise build dataset
    const trajectoryDatasets = [{
        label: 'Projected Trajectory (lbs)',
        data: projWeights,
        borderColor: '#ffdd57', // Bulma Warning Color
        backgroundColor: 'rgba(255, 221, 87, 0.1)',
        borderWidth: 3,
        borderDash: [5, 5], // Dashed line implies 'projection'
        pointBackgroundColor: '#ffdd57',
        fill: true,
        tension: 0.3
    }];

    if (targetWeights && targetWeights.length > 0) {
        trajectoryDatasets.push({
            label: 'Goal Weight (lbs)',
            data: targetWeights,
            borderColor: '#48c774', // Bulma Success Color
            borderWidth: 2,
            pointRadius: 0, // Hide points for a clean benchmark line
            fill: false,
            tension: 0
        });
    }

    const ctxProj = document.getElementById('trajectoryChart').getContext('2d');
    new Chart(ctxProj, {
        type: 'line',
        data: {
            labels: projLabels,
            datasets: trajectoryDatasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    grace: '5%'
                }
            }
        }
    });
</script>
{% endblock %}