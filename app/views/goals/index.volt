{% extends "layouts/main.volt" %}

{% block content %}
<div class="level is-mobile mb-4">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title is-4 mb-0">Nutrition Goals</h1>
        </div>
    </div>
</div>

<script>
// Quick JS to live-update the total so the user knows if they hit 100%
document.querySelectorAll('.macro-input').forEach(input => {
    input.addEventListener('input', () => {
        let total = 0;
        document.querySelectorAll('.macro-input').forEach(i => total += (parseInt(i.value) || 0));

        const totalSpan = document.getElementById('macro-total');
        totalSpan.innerText = total;

        if (total === 100) {
            totalSpan.className = 'has-text-success';
        } else {
            totalSpan.className = 'has-text-danger';
        }
    });
});
</script>

<div class="columns is-centered">
    <div class="column is-half-tablet">

        <div class="box has-background-primary-light has-text-centered mb-5" style="border: 1px solid #00d1b2;">
            <p class="heading has-text-primary-dark">Your Maintenance Calories (TDEE)</p>
            <p class="title is-2 has-text-primary-dark">{{ tdee }}</p>
            <p class="is-size-7 has-text-grey mt-2">
                This is how many calories you burn per day based on your age, height, weight, and activity level.
            </p>
        </div>

        <div class="box p-5">
            <form action="/goals/save" method="POST">
                <input type="hidden" name="tdee" value="{{ tdee }}">

                <h2 class="title is-5 mb-4">Set Your Target</h2>

                <div class="field mb-4">
                    <label class="label">Target Weight (lbs)</label>
                    <div class="control has-icons-left">
                        <input class="input is-medium" type="number" step="0.1" name="target_weight_lbs" value="{{ targetWeight }}" placeholder="e.g. 165" required>
                        <span class="icon is-small is-left"><i class="fas fa-bullseye"></i></span>
                    </div>
                </div>

                <div class="field mb-6">
                    <label class="label">Weekly Pace</label>
                    <div class="control">
                        <div class="select is-medium is-fullwidth">
                            <select name="rate_offset" required>
                                <option value="-1000" {% if rateOffset == -1000 %}selected{% endif %}>Lose 2 lbs per week (Hard)</option>
                                <option value="-500" {% if rateOffset == -500 %}selected{% endif %}>Lose 1 lb per week (Recommended)</option>
                                <option value="-250" {% if rateOffset == -250 %}selected{% endif %}>Lose 0.5 lbs per week (Easy)</option>
                                <option value="0" {% if rateOffset == 0 %}selected{% endif %}>Maintain Current Weight</option>
                                <option value="250" {% if rateOffset == 250 %}selected{% endif %}>Gain 0.5 lbs per week (Lean Bulk)</option>
                                <option value="500" {% if rateOffset == 500 %}selected{% endif %}>Gain 1 lb per week (Bulk)</option>
                            </select>
                        </div>
                    </div>
                </div>

            <hr>
            <h2 class="title is-5 mb-4">Macronutrient Split</h2>
            <p class="is-size-7 has-text-grey mb-4">Adjust your daily protein, carbohydrate, and fat targets. Must equal 100%.</p>

            <div class="columns is-mobile mb-2">
                <div class="column">
                    <div class="field">
                        <label class="label is-small">Protein %</label>
                        <div class="control">
                            <input class="input macro-input" type="number" name="macro_protein_pct" value="{{ proteinPct }}" min="0" max="100" required>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="field">
                        <label class="label is-small">Carbs %</label>
                        <div class="control">
                            <input class="input macro-input" type="number" name="macro_carbs_pct" value="{{ carbsPct }}" min="0" max="100" required>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="field">
                        <label class="label is-small">Fat %</label>
                        <div class="control">
                            <input class="input macro-input" type="number" name="macro_fat_pct" value="{{ fatPct }}" min="0" max="100" required>
                        </div>
                    </div>
                </div>
            </div>

                <div class="has-text-right mb-5">
                    <span class="is-size-7 has-text-weight-bold">Total: <span id="macro-total" class="has-text-success">100</span>%</span>
                </div>

                <div class="field">
                    <button type="submit" class="button is-primary is-medium is-fullwidth">
                        <span class="icon"><i class="fas fa-save"></i></span>
                        <span>Update Calorie Budget</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
{% endblock %}