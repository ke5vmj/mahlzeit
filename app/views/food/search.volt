{% extends "layouts/main.volt" %}

{% block content %}

{# 1. HEADER & BACK BUTTON #}
<div class="level is-mobile mb-4">
    <div class="level-left">
        <div class="level-item">
            <a href="/" class="button is-white">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
            </a>
        </div>
        <div class="level-item">
            <div>
                <h1 class="title is-4 mb-0">Add Food</h1>
                <p class="subtitle is-6 has-text-grey">to {{ mealName }}</p>
            </div>
        </div>
    </div>
</div>

{# 2. SEARCH & QUICK ACTIONS #}
<div class="mb-5">
    {# The Search Bar with Embedded Barcode Scanner #}
    <form action="/diary/{{ date }}/add-food/{{ mealTypeId }}" method="GET" class="mb-3">
        <div class="field">
            <div class="control has-icons-left has-icons-right is-relative">
                {# Added extra padding-right so text doesn't type under the barcode icon #}
                <input class="input is-medium is-rounded" style="padding-right: 3rem;" type="text" name="q" value="{{ searchQuery }}" placeholder="Search for a food...">
                <span class="icon is-left has-text-grey"><i class="fas fa-search"></i></span>

                {# The embedded scan button (pointer-events: auto makes it clickable!) #}
                <a href="/diary/{{ date }}/food/scan/{{ mealTypeId }}" class="icon is-right has-text-primary" style="pointer-events: auto; right: 8px; height: 100%;">
                    <i class="fas fa-barcode fa-lg"></i>
                </a>
            </div>
        </div>
    </form>

    {# Action Pill Row #}
    <div class="is-flex is-justify-content-flex-end">
        <a href="/diary/{{ date }}/food/create/{{ mealTypeId }}" class="button is-small is-rounded is-primary is-light has-text-weight-medium">
            <span class="icon"><i class="fas fa-plus"></i></span>
            <span>Create Custom Food</span>
        </a>
    </div>

</div>

{# 3. NAVIGATION TABS #}
<div class="tabs is-fullwidth is-toggle is-toggle-rounded mb-5">
  <ul>
    <li class="{% if activeTab == 'templates' %}is-active{% endif %}">
        <a href="/diary/{{ date }}/add-food/{{ mealTypeId }}?tab=templates">
          <span class="icon is-small"><i class="fas fa-layer-group"></i></span>
          <span>Meals</span>
        </a>
    </li>
    <li class="{% if activeTab == 'recent' %}is-active{% endif %}">
      <a href="/diary/{{ date }}/add-food/{{ mealTypeId }}?tab=recent">
        <span class="icon is-small"><i class="fas fa-clock"></i></span>
        <span>Recent</span>
      </a>
    </li>
    <li class="{% if activeTab == 'pinned' %}is-active{% endif %}">
      <a href="/diary/{{ date }}/add-food/{{ mealTypeId }}?tab=pinned">
        <span class="icon is-small"><i class="fas fa-star"></i></span>
        <span>Pinned</span>
      </a>
    </li>
    <li class="{% if activeTab == 'my_foods' %}is-active{% endif %}">
      <a href="/diary/{{ date }}/add-food/{{ mealTypeId }}?tab=my_foods">
        <span class="icon is-small"><i class="fas fa-user-edit"></i></span>
        <span>My Foods</span>
      </a>
    </li>
  </ul>
</div>

{# 4. THE FOOD LIST #}
<div class="box p-0 is-clipped">

    {% if activeTab == 'templates' %}
            {% if templates is defined and templates|length > 0 %}
                {% for template in templates %}
                    <div class="panel-block is-flex is-justify-content-space-between py-3 px-4 p-0-mobile">

                        {# Make the left side clickable to view the details #}
                        <a href="/templates/{{ template.id }}/details?date={{ date }}&meal_type_id={{ mealTypeId }}" class="is-flex-grow-1 has-text-dark" style="display: block; width: 100%;">
                            <p class="has-text-weight-bold">{{ template.name }}</p>
                            <p class="is-size-7 has-text-grey">
                                {{ template.getRelated('Items')|length }} items
                            </p>
                        </a>

                        <div class="has-text-right">
                            <form action="/templates/log" method="POST" style="margin: 0;">
                                <input type="hidden" name="date" value="{{ date }}">
                                <input type="hidden" name="meal_type_id" value="{{ mealTypeId }}">
                                <input type="hidden" name="template_id" value="{{ template.id }}">
                                <button type="submit" class="button is-primary is-small">
                                    <span class="icon"><i class="fas fa-plus"></i></span>
                                    <span>Log</span>
                                </button>
                            </form>
                        </div>
                    </div>
                {% endfor %}
            {% else %}
                <div class="panel-block p-5 has-text-centered has-text-grey-light is-italic">
                    <p>No saved meals yet. Save a meal from your diary to see it here!</p>
                </div>
            {% endif %}
        {% elseif foods is defined and foods|length > 0 %}

            {% for food in foods %}
                <div class="panel-block is-flex is-justify-content-space-between py-3 px-4 p-0-mobile">
                    {# Left Side: Clickable area for details #}
                    <a href="/diary/{{ date }}/food/{{ food.getId() }}/details/{{ mealTypeId }}" class="is-flex-grow-1 has-text-dark" style="display: block; width: 100%;">
                        <p class="has-text-weight-bold">{{ food.getName() }}</p>
                        <p class="is-size-7 has-text-grey">
                            {% if food.getBrand() %}{{ food.getBrand() }} • {% endif %}
                            {{ food.getServingSize() }} {{ food.getServingUnit() }}
                        </p>
                    </a>

                    {# Right Side: Quick Add #}
                    <div class="has-text-right is-flex is-align-items-center">
                        <p class="has-text-info has-text-weight-bold mr-3">{{ food.getCalories() }} cal</p>
                        <form action="/diary/log" class="quick-add-form" method="POST" style="margin: 0;">
                            <input type="hidden" name="date" value="{{ date }}">
                            <input type="hidden" name="meal_type_id" value="{{ mealTypeId }}">
                            <input type="hidden" name="food_id" value="{{ food.getId() }}">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="button is-white has-text-info p-0" style="border: none; background: transparent;">
                                <span class="icon is-medium"><i class="fas fa-plus-circle fa-lg"></i></span>
                            </button>
                        </form>
                    </div>
                </div>
            {% endfor %}

            {# PAGINATION CONTROLS #}
            {% if currentPage > 1 or hasNextPage %}
                <div class="panel-block p-4 is-flex is-justify-content-space-between has-background-white-ter">

                    {# Previous Button #}
                    {% if currentPage > 1 %}
                        <a href="/diary/{{ date }}/add-food/{{ mealTypeId }}?tab={{ activeTab }}&q={{ searchQuery|url_encode }}&page={{ currentPage - 1 }}" class="button is-small is-white">
                            <span class="icon"><i class="fas fa-chevron-left"></i></span>
                            <span>Previous</span>
                        </a>
                    {% else %}
                        <div style="width: 85px;"></div> {# Spacer to keep "Page X" centered #}
                    {% endif %}

                    {# Current Page Indicator #}
                    <span class="is-size-7 has-text-grey-light mt-1">Page {{ currentPage }}</span>

                    {# Next Button #}
                    {% if hasNextPage %}
                        <a href="/diary/{{ date }}/add-food/{{ mealTypeId }}?tab={{ activeTab }}&q={{ searchQuery|url_encode }}&page={{ currentPage + 1 }}" class="button is-small is-white">
                            <span>Next</span>
                            <span class="icon"><i class="fas fa-chevron-right"></i></span>
                        </a>
                    {% else %}
                        <div style="width: 85px;"></div>
                    {% endif %}

                </div>
            {% endif %}

        {% elseif searchQuery != '' %}

            {# Empty state for bad searches #}
            <div class="panel-block p-5 has-text-centered has-text-grey">
                <p>No foods found matching "<strong>{{ searchQuery }}</strong>".</p>
            </div>

        {% else %}

            {# Default state before they search #}
            <div class="panel-block p-5 has-text-centered has-text-grey-light is-italic">
                <p>Type a food name above to search your database.</p>
            </div>

        {% endif %}

</div>

<script>
    document.querySelectorAll('.quick-add-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            // Stop the browser from leaving the page
            e.preventDefault();

            const btn = this.querySelector('button[type="submit"]');
            const icon = btn.querySelector('i');

            // Save the original icon class so we can revert it later
            const originalIconClass = icon.className;

            // 1. Visual Feedback: Loading State
            icon.className = 'fas fa-spinner fa-spin fa-lg';
            btn.classList.replace('has-text-info', 'has-text-grey');

            // Gather the form inputs (date, food_id, meal_type_id, etc.)
            const formData = new FormData(this);

            try {
                // Send the POST request in the background
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        // This header is what tells Phalcon's isAjax() to return true
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    // 2. Visual Feedback: Success State!
                    icon.className = 'fas fa-check-circle fa-lg';
                    btn.classList.replace('has-text-grey', 'has-text-success');

                    // 3. Reset the button back to a plus sign after 1.5 seconds
                    setTimeout(() => {
                        icon.className = originalIconClass;
                        btn.classList.replace('has-text-success', 'has-text-info');
                    }, 1500);
                } else {
                    throw new Error('Network response was not ok');
                }
            } catch (error) {
                console.error("Failed to log food:", error);
                // Revert button on error
                icon.className = originalIconClass;
                btn.classList.replace('has-text-grey', 'has-text-info');
                alert("Failed to add food to diary. Please try again.");
            }
        });
    });
</script>

{% endblock %}