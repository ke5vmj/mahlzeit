{% extends "layouts/main.volt" %}

{% block content %}

{# 1. HEADER & BACK BUTTON #}
<div class="level is-mobile mb-4">
    <div class="level-left">
        <div class="level-item">
            {# Notice the href is now beautifully simple #}
            <a href="{{ backUrl }}" class="button is-white">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
            </a>
        </div>
        <div class="level-item">
            <div>
                <h1 class="title is-4 mb-0">Food Details</h1>
            </div>
        </div>
    </div>
</div>

{# 2. FOOD HEADER #}
<div class="box has-text-centered mb-4 is-relative">

    <div style="position: absolute; top: 0.5rem; right: 0.5rem; display: flex; gap: 0.5rem;">

        {# The Pin/Star Button #}
        <form action="/diary/food/{{ food.getId() }}/pin" method="POST" style="margin: 0;">
            <input type="hidden" name="date" value="{{ date }}">
            <input type="hidden" name="meal_type_id" value="{{ mealTypeId }}">
            <button type="submit" class="button is-small is-ghost px-2">
                {% if isPinned %}
                    <span class="icon has-text-warning"><i class="fas fa-star fa-lg"></i></span>
                {% else %}
                    <span class="icon has-text-grey-light"><i class="far fa-star fa-lg"></i></span>
                {% endif %}
            </button>
        </form>

        {# The Edit Button #}
        <a href="/diary/{{ date }}/food/{{ food.getId() }}/edit/{{ mealTypeId }}" class="button is-small is-ghost px-2">
            <span class="icon has-text-grey"><i class="fas fa-edit fa-lg"></i></span>
        </a>

    </div>

    <h2 class="title is-3 mb-1 mt-3">{{ food.getName() }}</h2>
    {% if food.getBrand() %}
        <p class="subtitle is-6 has-text-grey">{{ food.getBrand() }}</p>
    {% endif %}
    <span class="tag is-primary is-light is-medium mt-2">
        {{ food.getServingSize() }} {{ food.getServingUnit() }}
    </span>
</div>

{# 3. PRIMARY MACROS DASHBOARD (Added IDs for Javascript updates!) #}
<div class="box mb-4">
    <div class="columns is-mobile has-text-centered is-vcentered">
        <div class="column">
            <p class="heading">Calories</p>
            <p class="title is-3 has-text-info" id="val-cals">{{ food.getCalories() }}</p>
        </div>
        <div class="column">
            <p class="heading">Protein</p>
            <p class="title is-4 has-text-success"><span id="val-pro">{{ food.getProteinG() }}</span>g</p>
        </div>
        <div class="column">
            <p class="heading">Carbs</p>
            <p class="title is-4 has-text-warning-dark"><span id="val-carb">{{ food.getCarbsG() }}</span>g</p>
        </div>
        <div class="column">
            <p class="heading">Fat</p>
            <p class="title is-4 has-text-danger"><span id="val-fat">{{ food.getFatG() }}</span>g</p>
        </div>
    </div>
</div>

{# 4. DETAILED NUTRITION DATA #}
<div class="box mb-4">
    <h3 class="title is-5 mb-3">Nutritional Information</h3>
    <table class="table is-fullwidth is-striped is-narrow">
        <tbody>
            <tr>
                <th class="has-text-weight-medium">Dietary Fiber</th>
                <td class="has-text-right">{{ food.getFiberG() }} g</td>
            </tr>
            <tr>
                <th class="has-text-weight-medium">Total Sugars</th>
                <td class="has-text-right">{{ food.getSugarG() }} g</td>
            </tr>
            <tr>
                <th class="has-text-weight-medium">Cholesterol</th>
                <td class="has-text-right">{{ food.getCholesterolMg() }} mg</td>
            </tr>
            <tr>
                <th class="has-text-weight-medium">Sodium</th>
                <td class="has-text-right">{{ food.getSodiumMg() }} mg</td>
            </tr>
            <tr>
                <th class="has-text-weight-medium">Calcium</th>
                <td class="has-text-right">{{ food.getCalciumMg() }} mg</td>
            </tr>
            <tr>
                <th class="has-text-weight-medium">Iron</th>
                <td class="has-text-right">{{ food.getIronMg() }} mg</td>
            </tr>
        </tbody>
    </table>

    {% if food.getIngredients() %}
        <hr>
        <p class="heading has-text-grey">Ingredients</p>
        <p class="is-size-7">{{ food.getIngredients() }}</p>
    {% endif %}
</div>

{# 5. THE LOGGING FORM #}
<div class="box">
    <form action="/diary/log" method="POST">
        <input type="hidden" name="date" value="{{ date }}">
        <input type="hidden" name="meal_type_id" value="{{ mealTypeId }}">
        <input type="hidden" name="food_id" value="{{ food.getId() }}">

        <label class="label">How much did you eat?</label>

        <div class="columns is-mobile is-vcentered mb-2">
            {# Left Input: The Standard Servings Multiplier (This actually submits to the DB) #}
            <div class="column">
                <div class="field">
                    <p class="heading mb-1">Servings</p>
                    <div class="control">
                        <input class="input is-medium has-text-centered" type="number" step="0.01" min="0.01" name="quantity" id="input-servings" value="1" required>
                    </div>
                </div>
            </div>

            <div class="column is-narrow has-text-grey-light is-size-4">
                <i class="fas fa-equals"></i>
            </div>

            {# Right Input: The Exact Measurement Calculator (Helper UI, not submitted) #}
            <div class="column">
                <div class="field">
                    <p class="heading mb-1">Total {{ food.getServingUnit()|capitalize }}</p>
                    <div class="control">
                        <input class="input is-medium has-text-centered has-background-white-ter" type="number" step="0.01" min="0.01" id="input-amount" value="{{ food.getServingSize() }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="field mt-4">
            <div class="control">
                <button type="submit" class="button is-primary is-medium is-fullwidth">
                    <span class="icon"><i class="fas fa-check"></i></span>
                    <span>Add to Diary</span>
                </button>
            </div>
        </div>
    </form>
</div>

{# 6. REAL-TIME MACRO MATH SCRIPT #}
<script>
    // Pull the base values from Phalcon
    const baseServingSize = {{ food.getServingSize() }};
    const baseCals = {{ food.getCalories() }};
    const basePro = {{ food.getProteinG() }};
    const baseCarb = {{ food.getCarbsG() }};
    const baseFat = {{ food.getFatG() }};

    // Grab the inputs
    const inputServings = document.getElementById('input-servings');
    const inputAmount = document.getElementById('input-amount');

    // Grab the display elements
    const valCals = document.getElementById('val-cals');
    const valPro = document.getElementById('val-pro');
    const valCarb = document.getElementById('val-carb');
    const valFat = document.getElementById('val-fat');

    // Helper to update the big numbers at the top
    function updateMacroDisplay(multiplier) {
        valCals.innerText = Math.round(baseCals * multiplier);
        valPro.innerText = +(basePro * multiplier).toFixed(1);
        valCarb.innerText = +(baseCarb * multiplier).toFixed(1);
        valFat.innerText = +(baseFat * multiplier).toFixed(1);
    }

    // Listener 1: If they type in the "Servings" box (e.g. 1.5)
    inputServings.addEventListener('input', function() {
        let mult = parseFloat(this.value) || 0;

        // Update the right box with the total Grams/ML
        inputAmount.value = +(mult * baseServingSize).toFixed(2);

        // Update the UI
        updateMacroDisplay(mult);
    });

    // Listener 2: If they type in the "Amount" box (e.g. 45g)
    inputAmount.addEventListener('input', function() {
        let amt = parseFloat(this.value) || 0;

        // Calculate the multiplier (45g / 100g = 0.45)
        let mult = amt / baseServingSize;

        // Update the left box with the true multiplier for the backend
        inputServings.value = +(mult).toFixed(3);

        // Update the UI
        updateMacroDisplay(mult);
    });
</script>

{% endblock %}