{% extends "layouts/main.volt" %}

{% block content %}
{# 1. HEADER & BACK BUTTON #}
<div class="level is-mobile mb-4">
    <div class="level-left">
        <div class="level-item">
            <a href="/diary/{{ date }}" class="button is-white">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
            </a>
        </div>
        <div class="level-item">
            <h1 class="title is-4 mb-0">Edit Entry</h1>
        </div>
    </div>
</div>

{# 2. FOOD HEADER #}
<div class="box has-text-centered mb-4">
    <h2 class="title is-3 mb-1">{{ food.getName() }}</h2>
    {% if food.getBrand() %}
        <p class="subtitle is-6 has-text-grey">{{ food.getBrand() }}</p>
    {% endif %}
    <span class="tag is-primary is-light is-medium mt-2">
        {{ food.getServingSize() }} {{ food.getServingUnit() }}
    </span>
</div>

{# 3. PRIMARY MACROS DASHBOARD #}
<div class="box mb-4">
    <div class="columns is-mobile has-text-centered is-vcentered">
        <div class="column">
            <p class="heading">Calories</p>
            <p class="title is-3 has-text-info" id="val-cals">{{ (food.getCalories() * entry.getQuantity()) }}</p>
        </div>
        <div class="column">
            <p class="heading">Protein</p>
            <p class="title is-4 has-text-success"><span id="val-pro">{{ "%.1f"|format(food.getProteinG() * entry.getQuantity()) }}</span>g</p>
        </div>
        <div class="column">
            <p class="heading">Carbs</p>
            <p class="title is-4 has-text-warning-dark"><span id="val-carb">{{ "%.1f"|format(food.getCarbsG() * entry.getQuantity()) }}</span>g</p>
        </div>
        <div class="column">
            <p class="heading">Fat</p>
            <p class="title is-4 has-text-danger"><span id="val-fat">{{ "%.1f"|format(food.getFatG() * entry.getQuantity()) }}</span>g</p>
        </div>
    </div>
</div>

{# 4. THE EDIT FORM #}
<div class="box">
    <form action="/diary/entry/{{ entry.getId() }}/update" method="POST">
        <label class="label">Update your amount</label>

        <div class="columns is-mobile is-vcentered mb-2">
            <div class="column">
                <div class="field">
                    <p class="heading mb-1">Servings</p>
                    <div class="control">
                        <input class="input is-medium has-text-centered" type="number" step="0.01" min="0.01" name="quantity" id="input-servings" value="{{ entry.getQuantity() }}" required>
                    </div>
                </div>
            </div>

            <div class="column is-narrow has-text-grey-light is-size-4">
                <i class="fas fa-equals"></i>
            </div>

            <div class="column">
                <div class="field">
                    <p class="heading mb-1">Total {{ food.getServingUnit()|capitalize }}</p>
                    <div class="control">
                        <input class="input is-medium has-text-centered has-background-white-ter" type="number" step="0.01" min="0.01" id="input-amount" value="{{ (food.getServingSize() * entry.getQuantity()) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="field mt-4">
            <div class="control">
                <button type="submit" class="button is-primary is-medium is-fullwidth">
                    <span class="icon"><i class="fas fa-save"></i></span>
                    <span>Save Changes</span>
                </button>
            </div>
        </div>
    </form>
</div>

{# 5. REAL-TIME MACRO MATH SCRIPT #}
<script>
    // Pull the base values from Phalcon
    const baseServingSize = {{ food.getServingSize() }};
    const baseCals = {{ food.getCalories() }};
    const basePro = {{ food.getProteinG() }};
    const baseCarb = {{ food.getCarbsG() }};
    const baseFat = {{ food.getFatG() }};

    const inputServings = document.getElementById('input-servings');
    const inputAmount = document.getElementById('input-amount');

    const valCals = document.getElementById('val-cals');
    const valPro = document.getElementById('val-pro');
    const valCarb = document.getElementById('val-carb');
    const valFat = document.getElementById('val-fat');

    function updateMacroDisplay(multiplier) {
        valCals.innerText = Math.round(baseCals * multiplier);
        valPro.innerText = +(basePro * multiplier).toFixed(1);
        valCarb.innerText = +(baseCarb * multiplier).toFixed(1);
        valFat.innerText = +(baseFat * multiplier).toFixed(1);
    }

    inputServings.addEventListener('input', function() {
        let mult = parseFloat(this.value) || 0;
        inputAmount.value = +(mult * baseServingSize).toFixed(2);
        updateMacroDisplay(mult);
    });

    inputAmount.addEventListener('input', function() {
        let amt = parseFloat(this.value) || 0;
        let mult = amt / baseServingSize;
        inputServings.value = +(mult).toFixed(3);
        updateMacroDisplay(mult);
    });
</script>
{% endblock %}