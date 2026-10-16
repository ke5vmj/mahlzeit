{% extends "layouts/main.volt" %}

{% block content %}
<div class="level is-mobile mb-4">
    <div class="level-left">
        <div class="level-item">
            <a href="/diary/{{ date }}/add-food/{{ mealTypeId }}?tab=templates" class="button is-white">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
            </a>
        </div>
        <div class="level-item">
            <div>
                <h1 class="title is-4 mb-0">Template Details</h1>
            </div>
        </div>
    </div>
</div>

<div class="box has-text-centered mb-5 is-relative">
    {# Delete Button #}
    <div style="position: absolute; top: 0.5rem; right: 0.5rem;">
        <form action="/templates/{{ template.id }}/delete" method="POST" style="margin: 0;">
            <input type="hidden" name="date" value="{{ date }}">
            <input type="hidden" name="meal_type_id" value="{{ mealTypeId }}">
            <button type="submit" class="button is-small is-ghost has-text-danger px-2" onclick="return confirm('Are you sure you want to delete this template?');">
                <span class="icon"><i class="fas fa-trash fa-lg"></i></span>
            </button>
        </form>
    </div>

    <h2 class="title is-3 mb-1 mt-3">{{ template.name }}</h2>
    <p class="subtitle is-6 has-text-grey">Created on {{ template.created_at }}</p>
</div>

<div class="panel is-primary">
    <div class="panel-heading">Foods in this Meal</div>

    {% set totalCals = 0 %}
    {% set items = template.getRelated('Items') %}

    {% for item in items %}
        {% set food = item.getRelated('Food') %}
        {% set itemCals = (food.getCalories() * item.quantity) %}
        {% set totalCals = totalCals + itemCals %}

        <div class="panel-block has-background-white is-flex is-justify-content-space-between">
            <div>
                <p class="has-text-dark has-text-weight-medium">{{ food.getName() }}</p>
                <p class="is-size-7 has-text-grey">
                    {{ (food.getServingSize() * item.quantity) }} {{ food.getServingUnit() }}
                    (x{{ item.quantity }} servings)
                </p>
            </div>
            <div class="has-text-right">
                <span class="has-text-info has-text-weight-bold">{{ itemCals }} cal</span>
            </div>
        </div>
    {% endfor %}

    <div class="panel-block has-background-light is-flex is-justify-content-space-between py-3">
        <span class="has-text-weight-bold">Total Estimated Calories</span>
        <span class="has-text-weight-bold is-size-5">{{ totalCals }}</span>
    </div>

    <div class="panel-block p-4">
        <form action="/templates/log" method="POST" style="width: 100%;">
            <input type="hidden" name="date" value="{{ date }}">
            <input type="hidden" name="meal_type_id" value="{{ mealTypeId }}">
            <input type="hidden" name="template_id" value="{{ template.id }}">
            <button type="submit" class="button is-primary is-medium is-fullwidth">
                <span class="icon"><i class="fas fa-check"></i></span>
                <span>Log this Meal</span>
            </button>
        </form>
    </div>
</div>
{% endblock %}