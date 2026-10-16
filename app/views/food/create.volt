{% extends "layouts/main.volt" %}

{% block content %}
<div class="level is-mobile mb-4">
    <div class="level-left">
        <div class="level-item">
            <a href="/diary/{{ date }}/add-food/{{ mealTypeId }}" class="button is-white">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
            </a>
        </div>
        <div class="level-item">
            <h1 class="title is-4 mb-0">Create Custom Food</h1>
        </div>
    </div>
</div>

<div class="box">
    <form action="/diary/{{ date }}/food/create/{{ mealTypeId }}" method="POST">

        <h3 class="title is-5 mb-3">Basic Info</h3>

        <div class="field">
            <label class="label">Name</label>
            <div class="control">
                <input class="input" type="text" name="name" placeholder="e.g. Grandma's Lasagna" required autofocus>
            </div>
        </div>

        <div class="field">
            <label class="label">Brand (Optional)</label>
            <div class="control">
                <input class="input" type="text" name="brand" placeholder="e.g. Homemade">
            </div>
        </div>

        <div class="columns is-mobile">
            <div class="column">
                <div class="field">
                    <label class="label">Serving Size</label>
                    <div class="control">
                        <input class="input" type="number" step="0.01" name="serving_size" placeholder="1" required>
                    </div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="label">Unit</label>
                    <div class="control">
                        <input class="input" type="text" name="serving_unit" placeholder="slice, cup, g" required>
                    </div>
                </div>
            </div>
        </div>

        <hr>
        <h3 class="title is-5 mb-3">Macros (per serving)</h3>

        <div class="columns is-mobile is-multiline">
            <div class="column is-half-mobile is-one-quarter-tablet">
                <div class="field">
                    <label class="label has-text-info">Calories</label>
                    <div class="control">
                        <input class="input" type="number" name="calories" placeholder="0" required>
                    </div>
                </div>
            </div>
            <div class="column is-half-mobile is-one-quarter-tablet">
                <div class="field">
                    <label class="label has-text-success">Protein (g)</label>
                    <div class="control">
                        <input class="input" type="number" step="0.1" name="protein_g" placeholder="0" required>
                    </div>
                </div>
            </div>
            <div class="column is-half-mobile is-one-quarter-tablet">
                <div class="field">
                    <label class="label has-text-warning-dark">Carbs (g)</label>
                    <div class="control">
                        <input class="input" type="number" step="0.1" name="carbs_g" placeholder="0" required>
                    </div>
                </div>
            </div>
            <div class="column is-half-mobile is-one-quarter-tablet">
                <div class="field">
                    <label class="label has-text-danger">Fat (g)</label>
                    <div class="control">
                        <input class="input" type="number" step="0.1" name="fat_g" placeholder="0" required>
                    </div>
                </div>
            </div>
        </div>

        <hr>
        <h3 class="title is-5 mb-3">Micronutrients (Optional)</h3>

        <div class="columns is-mobile is-multiline">
            <div class="column is-half-mobile is-one-third-tablet">
                <div class="field">
                    <label class="label">Fiber (g)</label>
                    <div class="control">
                        <input class="input" type="number" step="0.1" name="fiber_g">
                    </div>
                </div>
            </div>
            <div class="column is-half-mobile is-one-third-tablet">
                <div class="field">
                    <label class="label">Sugar (g)</label>
                    <div class="control">
                        <input class="input" type="number" step="0.1" name="sugar_g">
                    </div>
                </div>
            </div>
            <div class="column is-half-mobile is-one-third-tablet">
                <div class="field">
                    <label class="label">Cholesterol (mg)</label>
                    <div class="control">
                        <input class="input" type="number" name="cholesterol_mg">
                    </div>
                </div>
            </div>
            <div class="column is-half-mobile is-one-third-tablet">
                <div class="field">
                    <label class="label">Sodium (mg)</label>
                    <div class="control">
                        <input class="input" type="number" name="sodium_mg">
                    </div>
                </div>
            </div>
            <div class="column is-half-mobile is-one-third-tablet">
                <div class="field">
                    <label class="label">Calcium (mg)</label>
                    <div class="control">
                        <input class="input" type="number" step="any" name="calcium_mg">
                    </div>
                </div>
            </div>
            <div class="column is-half-mobile is-one-third-tablet">
                <div class="field">
                    <label class="label">Iron (mg)</label>
                    <div class="control">
                        <input class="input" type="number" step="any" name="iron_mg">
                    </div>
                </div>
            </div>
        </div>

        <div class="field mt-5">
            <div class="control">
                <button type="submit" class="button is-primary is-fullwidth is-medium">
                    <span class="icon"><i class="fas fa-plus"></i></span>
                    <span>Create Food</span>
                </button>
            </div>
        </div>
    </form>
</div>
{% endblock %}