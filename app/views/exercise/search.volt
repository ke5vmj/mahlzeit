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
            <div>
                <h1 class="title is-4 mb-0">Find Exercise</h1>
            </div>
        </div>
    </div>
</div>

{# 2. THE SEARCH BAR #}
<div class="field mb-5">
    <p class="control has-icons-left">
        <input class="input is-medium is-rounded" id="exerciseSearch" type="text" placeholder="Search for an activity...">
        <span class="icon is-left"><i class="fas fa-search"></i></span>
    </p>
</div>

{# 3. THE GROUPED EXERCISE LIST #}
<div class="box p-0 is-clipped" id="exerciseList">

    {% for categoryName, items in groupedActivities %}
        {# Wrap each category in a container so JS can hide the whole block if empty #}
        <div class="category-group">

            {# The Category Header #}
            <p class="panel-heading is-size-6 has-background-white-ter has-text-grey py-2 px-4 mb-0" style="border-bottom: 1px solid #ededed;">
                {{ categoryName }}
            </p>

            {# The Exercises in this Category #}
            {% for activity in items %}
                <a class="panel-block is-flex is-justify-content-space-between py-3 px-4 exercise-item"
                   data-search="{{ activity.name|lower }} {{ activity.category|lower }}"
                   href="/diary/{{ date }}/exercise/add/{{ activity.id }}"
                   style="border-bottom: 1px solid #ededed;">
                    <div>
                        <p class="has-text-dark has-text-weight-bold">{{ activity.name }}</p>
                    </div>
                    <div class="has-text-right">
                        <span class="icon has-text-primary"><i class="fas fa-chevron-right"></i></span>
                    </div>
                </a>
            {% endfor %}

        </div>
    {% endfor %}

    {# Empty state #}
    <div id="noResults" class="panel-block p-5 has-text-centered has-text-grey is-hidden">
        <p>No activities found.</p>
    </div>
</div>

{# 4. LIVE FILTER SCRIPT (Upgraded for Groups!) #}
<script>
    const searchInput = document.getElementById('exerciseSearch');
    const categoryGroups = document.querySelectorAll('.category-group');
    const noResults = document.getElementById('noResults');

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        let totalVisibleItems = 0;

        categoryGroups.forEach(group => {
            let visibleInGroup = 0;
            const items = group.querySelectorAll('.exercise-item');

            // 1. Check every item in this specific category
            items.forEach(item => {
                const searchableText = item.getAttribute('data-search');
                if (searchableText.includes(query)) {
                    item.style.display = 'flex';
                    visibleInGroup++;
                    totalVisibleItems++;
                } else {
                    item.style.display = 'none';
                }
            });

            // 2. If no items in this category match, hide the category header entirely!
            if (visibleInGroup === 0) {
                group.style.display = 'none';
            } else {
                group.style.display = 'block';
            }
        });

        // 3. Show/hide the global "No results" message
        if (totalVisibleItems === 0) {
            noResults.classList.remove('is-hidden');
        } else {
            noResults.classList.add('is-hidden');
        }
    });
</script>

{% endblock %}