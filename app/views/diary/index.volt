{% extends "layouts/main.volt" %}

{% block content %}

{# 1. DATE NAVIGATOR #}
<nav class="level is-mobile mb-5">
  <div class="level-item has-text-centered">
    <a href="/diary/{{ prevDate }}" class="button is-white"><i class="fas fa-chevron-left"></i></a>
  </div>
  <div class="level-item has-text-centered">
    <div>
      <p class="heading">Diary</p>
      <p class="title is-5">{{ date }}</p>
    </div>
  </div>
  <div class="level-item has-text-centered">
    <a href="/diary/{{ nextDate }}" class="button is-white"><i class="fas fa-chevron-right"></i></a>
  </div>
</nav>

{# 2. THE EQUATION (Budget - Food + Exercise = Net) #}
{% set net = budget - totals['calories'] + totalExerciseCalories %}
{# Budget is still static for now, but Food and Exercise are fully dynamic! #}
{# MACRO TRACKING SUMMARY #}
<div class="columns is-mobile has-text-centered mb-5">

    {# Protein (4 cals/g) #}
    <div class="column">
        <div class="box p-3 has-background-white">
            <p class="heading is-size-7-mobile has-text-info">Protein</p>
            <p class="title is-5 is-size-6-mobile mb-1">
                {{ "%.0f"|format(totals['protein']) }} <span class="has-text-grey is-size-6 is-size-7-mobile">/ {{ macroTargets['protein'] }}g</span>
            </p>
            {# Quick visual progress bar #}
            <progress class="progress is-info is-small m-0" value="{{ totals['protein'] }}" max="{{ macroTargets['protein'] }}"></progress>
        </div>
    </div>

    {# Carbs (4 cals/g) #}
    <div class="column">
        <div class="box p-3 has-background-white">
            <p class="heading is-size-7-mobile has-text-success">Carbs</p>
            <p class="title is-5 is-size-6-mobile mb-1">
                {{ "%.0f"|format(totals['carbs']) }} <span class="has-text-grey is-size-6 is-size-7-mobile">/ {{ macroTargets['carbs'] }}g</span>
            </p>
            <progress class="progress is-success is-small m-0" value="{{ totals['carbs'] }}" max="{{ macroTargets['carbs'] }}"></progress>
        </div>
    </div>

    {# Fat (9 cals/g) #}
    <div class="column">
        <div class="box p-3 has-background-white">
            <p class="heading is-size-7-mobile has-text-warning-dark">Fat</p>
            <p class="title is-5 is-size-6-mobile mb-1">
                {{ "%.0f"|format(totals['fat']) }} <span class="has-text-grey is-size-6 is-size-7-mobile">/ {{ macroTargets['fat'] }}g</span>
            </p>
            <progress class="progress is-warning is-small m-0" value="{{ totals['fat'] }}" max="{{ macroTargets['fat'] }}"></progress>
        </div>
    </div>

</div>

<div class="box has-background-light mb-6 px-2">
    <div class="columns is-mobile has-text-centered is-vcentered is-gapless">
        <div class="column">
            <p class="heading is-size-7-mobile">Budget</p>
            <p class="title is-5 is-size-6-mobile has-text-grey">{{ budget }}</p>
        </div>
        <div class="column is-narrow"><i class="fas fa-minus fa-xs has-text-grey-light"></i></div>
        <div class="column">
            <p class="heading is-size-7-mobile">Food</p>
            <p class="title is-5 is-size-6-mobile has-text-info">{{ totals['calories'] }}</p>
        </div>
        <div class="column is-narrow"><i class="fas fa-plus fa-xs has-text-grey-light"></i></div>
        <div class="column">
            <p class="heading is-size-7-mobile">Exercise</p>
            <p class="title is-5 is-size-6-mobile has-text-warning-dark">{{ totalExerciseCalories }}</p>
        </div>
        <div class="column is-narrow"><i class="fas fa-equals fa-xs has-text-grey-light"></i></div>
        <div class="column">
            <p class="heading is-size-7-mobile">{% if net >= 0 %}Under{% else %}Over{% endif %}</p>
            <p class="title is-4 is-size-5-mobile {% if net >= 0 %}has-text-success{% else %}has-text-danger{% endif %}">{{ net|abs }}</p>
        </div>
    </div>
</div>

{# 3. MEAL BUCKETS #}
{% for mealId, meal in meals %}
<div class="panel is-info mb-5">
  <div class="panel-heading is-flex is-justify-content-space-between is-align-items-center">
      <div>
        <span>{{ meal['name'] }}</span>
        {% if meal['calories'] > 0 %}
            <span class="tag is-white is-rounded has-text-weight-bold ml-2">{{ meal['calories'] }} cal</span>
        {% else %}
            <span class="tag is-info is-light is-rounded has-text-weight-bold ml-2">0 cal</span>
        {% endif %}
      </div>

      {# Only show the save icon if there's food to save! #}
      {% if meal['entries'] is defined and meal['entries']|length > 0 %}
          <button class="button is-small is-info is-inverted is-outlined js-modal-trigger" data-target="save-meal-modal-{{ mealId }}" title="Save as Template">
              <span class="icon"><i class="fas fa-save"></i></span>
          </button>
      {% endif %}
    </div>

  {# Logged Foods Loop #}
  {% if meal['entries'] is defined and meal['entries']|length > 0 %}
      {% for entry in meal['entries'] %}
      {# Changed from <a> to <div> so the form is valid HTML #}
        <div class="panel-block has-background-white">
          <span class="panel-icon"><i class="fas fa-utensils" aria-hidden="true"></i></span>
          <div style="width: 100%; display: flex; justify-content: space-between; align-items: center;">
              <span>
                  <span class="has-text-dark">{{ entry['food_name'] }}</span>
                  <span class="is-size-7 has-text-grey is-block">{{ entry['serving_text'] }}</span>
              </span>

              {# Right Side: Calories & Delete Button #}
              <div class="is-flex is-align-items-center">
                  <span class="has-text-grey has-text-weight-bold mr-3">{{ entry['calories'] }}</span>

                    {# --- NEW: Edit Link --- #}
                    <a href="/diary/entry/{{ entry['entry_id'] }}/edit" class="button is-small is-ghost has-text-grey px-2">
                        <span class="icon"><i class="fas fa-edit"></i></span>
                    </a>

                  {# The tiny Delete Form #}
                  <form action="/diary/entry/{{ entry['entry_id'] }}/delete" method="POST" style="margin: 0;">
                      {# Added a Javascript confirm prompt to prevent accidental clicks #}
                      <button type="submit" class="button is-small is-ghost has-text-danger px-2" onclick="return confirm('Remove this food?');">
                          <span class="icon"><i class="fas fa-trash"></i></span>
                      </button>
                  </form>
              </div>

          </div>
        </div>
      {% endfor %}
  {% else %}
      {# Empty State #}
      <div class="panel-block has-background-white-ter has-text-grey-light is-italic is-size-7">
        No food logged yet
      </div>
  {% endif %}

  <div class="panel-block">
    <a href="/diary/{{ date }}/add-food/{{ mealId }}" class="button is-info is-light is-fullwidth is-small">
      <span class="icon"><i class="fas fa-plus"></i></span>
      <span>Add Food</span>
    </a>
  </div>
</div>
{% endfor %}

{# 4. EXERCISE BUCKET #}
<div class="panel is-warning mb-5">
  <div class="panel-heading">
    <span>Exercise</span>

    {% if totalExerciseCalories > 0 %}
        <span class="tag is-white is-rounded has-text-weight-bold">{{ totalExerciseCalories }} cal</span>
    {% else %}
        <span class="tag is-warning is-light is-rounded has-text-weight-bold">0 cal</span>
    {% endif %}
  </div>

  {% if exercises is defined and exercises|length > 0 %}
      {% for exercise in exercises %}
      <div class="panel-block has-background-white">
        <span class="panel-icon"><i class="fas fa-running has-text-warning-dark" aria-hidden="true"></i></span>
        <div style="width: 100%; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <span class="has-text-weight-medium">{{ exercise['activity_name'] }}</span>
                <br>
                <span class="is-size-7 has-text-grey">{{ exercise['duration'] }} min</span>
            </div>

            {# Right Side: Calories & Delete Button #}
            <div class="is-flex is-align-items-center">
                <span class="has-text-warning-dark has-text-weight-bold mr-3">{{ exercise['calories_burned'] }}</span>

                {# Delete Form #}
                <form action="/diary/exercise/{{ exercise['log_id'] }}/delete" method="POST" style="margin: 0;">
                    <button type="submit" class="button is-small is-ghost has-text-danger px-2" onclick="return confirm('Remove this exercise?');">
                        <span class="icon"><i class="fas fa-trash"></i></span>
                    </button>
                </form>
            </div>
        </div>
      </div>
      {% endfor %}
  {% else %}
      <div class="panel-block has-background-white-ter has-text-grey-light is-italic is-size-7">
        No exercise logged yet
      </div>
  {% endif %}

  <div class="panel-block">
    <a href="/diary/{{ date }}/exercise/search" class="button is-warning is-light is-fullwidth is-small">
      <span class="icon"><i class="fas fa-plus"></i></span>
      <span>Add Exercise</span>
    </a>
  </div>
</div>

{# 5. WEIGHT BUCKET #}
<div class="panel is-primary mb-6">
  <div class="panel-heading">
    <span>Weight Tracking</span>
    <span class="tag is-white is-rounded has-text-weight-bold">Goal: {{ targetWeightLbs }} lbs</span>
  </div>

  <div class="panel-block has-background-white">
    <span class="panel-icon"><i class="fas fa-weight has-text-primary-dark" aria-hidden="true"></i></span>
    <div style="width: 100%; display: flex; justify-content: space-between; align-items: center;">
        <span class="has-text-dark has-text-weight-medium">Logged Weight</span>

        {% if loggedWeight %}
            <span class="has-text-primary-dark has-text-weight-bold">{{ loggedWeight }} lbs</span>
        {% else %}
            <span class="has-text-grey is-italic is-size-7">Not logged today</span>
        {% endif %}
    </div>
  </div>

  {# --- RECENT WEIGHT HISTORY TABLE --- #}
  {% if recentWeights is defined and recentWeights|length > 0 %}
  <div class="panel-block has-background-light p-0">
      <table class="table is-fullwidth is-narrow is-striped m-0" style="background: transparent;">
          <thead>
              <tr>
                  <th class="has-text-grey is-size-7 pl-4 is-borderless">Recent History</th>
                  <th class="has-text-grey is-size-7 has-text-right pr-4 is-borderless">Weight</th>
              </tr>
          </thead>
          <tbody>
              {% for rw in recentWeights %}
              <tr>
                  <td class="pl-4 is-vcentered">
                      {% if rw['raw_date'] == date %}
                          <span class="tag is-primary is-light is-small">Today</span>
                      {% else %}
                          <span class="is-size-7">{{ rw['date_formatted'] }}</span>
                      {% endif %}
                  </td>
                  <td class="has-text-right pr-4 is-vcentered has-text-grey-dark">{{ rw['weight_lbs'] }} lbs</td>
              </tr>
              {% endfor %}
          </tbody>
      </table>
  </div>
  {% endif %}

  <div class="panel-block">
    <a href="/diary/{{ date }}/log-weight" class="button is-primary is-light is-fullwidth is-small">
      <span class="icon"><i class="fas fa-{% if loggedWeight %}edit{% else %}plus{% endif %}"></i></span>
      <span>{% if loggedWeight %}Update{% else %}Log{% endif %} Weight</span>
    </a>
  </div>
</div>

{# MODALS FOR SAVING TEMPLATES #}
{% for mealId, meal in meals %}
    {% if meal['entries'] is defined and meal['entries']|length > 0 %}
    <div id="save-meal-modal-{{ mealId }}" class="modal">
      <div class="modal-background"></div>
      <div class="modal-card">
        <header class="modal-card-head">
          <p class="modal-card-title is-size-5">Save '{{ meal['name'] }}' Template</p>
          <button class="delete" aria-label="close"></button>
        </header>
        <section class="modal-card-body">
          <form action="/templates/save-from-diary" method="POST" id="form-save-{{ mealId }}">
            <input type="hidden" name="date" value="{{ date }}">
            <input type="hidden" name="meal_type_id" value="{{ mealId }}">

            <div class="field">
                <label class="label">Template Name</label>
                <div class="control">
                    <input class="input is-medium" type="text" name="name" placeholder="e.g., Standard Weekday Breakfast" required>
                </div>
            </div>
          </form>
        </section>
        <footer class="modal-card-foot is-justify-content-flex-end">
          <button class="button is-light cancel-btn">Cancel</button>
          <button type="submit" form="form-save-{{ mealId }}" class="button is-primary">Save Template</button>
        </footer>
      </div>
    </div>
    {% endif %}
{% endfor %}

{# BULMA MODAL SCRIPT #}
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Functions to open and close a modal
    function openModal($el) { $el.classList.add('is-active'); }
    function closeModal($el) { $el.classList.remove('is-active'); }

    // Add click event on buttons to open a specific modal
    (document.querySelectorAll('.js-modal-trigger') || []).forEach(($trigger) => {
        const modal = $trigger.dataset.target;
        const $target = document.getElementById(modal);
        $trigger.addEventListener('click', () => { openModal($target); });
    });

    // Add click event on various child elements to close the parent modal
    (document.querySelectorAll('.modal-background, .modal-close, .modal-card-head .delete, .cancel-btn') || []).forEach(($close) => {
        const $target = $close.closest('.modal');
        $close.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal($target);
        });
    });
});
</script>

{% endblock %}