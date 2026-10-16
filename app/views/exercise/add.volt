{% extends "layouts/main.volt" %}

{% block content %}

<div class="level is-mobile mb-4">
    <div class="level-left">
        <div class="level-item">
            <a href="/diary/{{ date }}/exercise/search" class="button is-white">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
            </a>
        </div>
        <div class="level-item">
            <h1 class="title is-4 mb-0">Log Workout</h1>
        </div>
    </div>
</div>

<div class="box">

    {# Display the chosen activity proudly #}
    <div class="has-text-centered mb-5">
        <span class="icon is-large has-text-primary mb-2">
            <i class="fas fa-running fa-2x"></i>
        </span>
        <h2 class="title is-3 mb-1">{{ activity.name }}</h2>
        <p class="subtitle is-6 has-text-grey">{{ activity.category }}</p>
    </div>

    <hr>

    <form action="/diary/exercise/log" method="POST">
        <input type="hidden" name="date" value="{{ date }}">
        <input type="hidden" name="activity_id" value="{{ activity.id }}">

        <div class="field mb-6 mt-5">
            <label class="label has-text-centered">Duration (Minutes)</label>
            <div class="control has-icons-left">
                <input class="input is-large has-text-centered" type="number" name="duration_minutes" value="30" min="1" required>
                <span class="icon is-left">
                    <i class="fas fa-stopwatch"></i>
                </span>
            </div>
        </div>

        <div class="field">
            <div class="control">
                <button type="submit" class="button is-primary is-medium is-fullwidth">
                    <span class="icon"><i class="fas fa-check"></i></span>
                    <span>Save to Diary</span>
                </button>
            </div>
        </div>
    </form>
</div>

{% endblock %}