{% extends "layouts/main.volt" %}

{% block content %}

<div class="level is-mobile mb-4">
    <div class="level-left">
        <div class="level-item">
            <a href="/diary/{{ date }}/food/{{ food.getId() }}/details/{{ mealTypeId }}" class="button is-white">
                <span class="icon"><i class="fas fa-times"></i></span>
            </a>
        </div>
        <div class="level-item">
            <h1 class="title is-4 mb-0">Edit Food</h1>
        </div>
    </div>
</div>

<div class="box">
    <form action="/diary/{{ date }}/food/{{ food.getId() }}/edit/{{ mealTypeId }}" method="POST">

            <h3 class="title is-5 mb-3">Basic Info</h3>

            <div class="field">
                <label class="label">Name</label>
                <div class="control">
                    <input class="input" type="text" name="name" value="{{ food.getName() }}" required>
                </div>
            </div>

            <div class="field">
                <label class="label">Brand (Optional)</label>
                <div class="control">
                    <input class="input" type="text" name="brand" value="{{ food.getBrand() }}">
                </div>
            </div>

            <div class="columns is-mobile">
                <div class="column">
                    <div class="field">
                        <label class="label">Serving Size</label>
                        <div class="control">
                            <input class="input" type="number" step="0.01" name="serving_size" value="{{ food.getServingSize() }}" required>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="field">
                        <label class="label">Unit</label>
                        <div class="control">
                            <input class="input" type="text" name="serving_unit" value="{{ food.getServingUnit() }}" placeholder="g, ml, slice" required>
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
                            <input class="input" type="number" name="calories" value="{{ food.getCalories() }}" required>
                        </div>
                    </div>
                </div>
                <div class="column is-half-mobile is-one-quarter-tablet">
                    <div class="field">
                        <label class="label has-text-success">Protein (g)</label>
                        <div class="control">
                            <input class="input" type="number" step="0.1" name="protein_g" value="{{ food.getProteinG() }}" required>
                        </div>
                    </div>
                </div>
                <div class="column is-half-mobile is-one-quarter-tablet">
                    <div class="field">
                        <label class="label has-text-warning-dark">Carbs (g)</label>
                        <div class="control">
                            <input class="input" type="number" step="0.1" name="carbs_g" value="{{ food.getCarbsG() }}" required>
                        </div>
                    </div>
                </div>
                <div class="column is-half-mobile is-one-quarter-tablet">
                    <div class="field">
                        <label class="label has-text-danger">Fat (g)</label>
                        <div class="control">
                            <input class="input" type="number" step="0.1" name="fat_g" value="{{ food.getFatG() }}" required>
                        </div>
                    </div>
                </div>
            </div>

            <hr>
            <h3 class="title is-5 mb-3">Micronutrients</h3>

            <div class="columns is-mobile is-multiline">
                <div class="column is-half-mobile is-one-third-tablet">
                    <div class="field">
                        <label class="label">Fiber (g)</label>
                        <div class="control">
                            <input class="input" type="number" step="0.1" name="fiber_g" value="{{ food.getFiberG() }}">
                        </div>
                    </div>
                </div>
                <div class="column is-half-mobile is-one-third-tablet">
                    <div class="field">
                        <label class="label">Sugar (g)</label>
                        <div class="control">
                            <input class="input" type="number" step="0.1" name="sugar_g" value="{{ food.getSugarG() }}">
                        </div>
                    </div>
                </div>
                <div class="column is-half-mobile is-one-third-tablet">
                    <div class="field">
                        <label class="label">Cholesterol (mg)</label>
                        <div class="control">
                            <input class="input" type="number" name="cholesterol_mg" value="{{ food.getCholesterolMg() }}">
                        </div>
                    </div>
                </div>
                <div class="column is-half-mobile is-one-third-tablet">
                    <div class="field">
                        <label class="label">Sodium (mg)</label>
                        <div class="control">
                            <input class="input" type="number" name="sodium_mg" value="{{ food.getSodiumMg() }}">
                        </div>
                    </div>
                </div>
                <div class="column is-half-mobile is-one-third-tablet">
                    <div class="field">
                        <label class="label">Calcium (mg)</label>
                        <div class="control">
                            <input class="input" type="number" name="calcium_mg" value="{{ food.getCalciumMg() }}">
                        </div>
                    </div>
                </div>
                <div class="column is-half-mobile is-one-third-tablet">
                    <div class="field">
                        <label class="label">Iron (mg)</label>
                        <div class="control">
                            <input class="input" type="number" name="iron_mg" value="{{ food.getIronMg() }}">
                        </div>
                    </div>
                </div>
            </div>

            <hr>
            <h3 class="title is-5 mb-3">Ingredients</h3>
            <div class="field">
                <div class="control">
                    <textarea class="textarea" name="ingredients" rows="3" placeholder="List ingredients here...">{{ food.getIngredients() }}</textarea>
                </div>
            </div>

            <div class="field mt-5">
                <div class="control">
                    <button type="submit" class="button is-primary is-fullwidth is-medium">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
</div>

{% endblock %}