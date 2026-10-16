{% extends "layouts/main.volt" %}

{% block content %}

<div class="level is-mobile mb-4">
    <div class="level-left">
        <div class="level-item">
            <a href="/diary/{{ date }}" class="button is-white">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
            </a>
        </div>
        <div class="level-item">
            <h1 class="title is-4 mb-0">Log Weight</h1>
        </div>
    </div>
</div>

<div class="box">
    <form action="/diary/weight/log" method="POST">
        <input type="hidden" name="date" value="{{ date }}">

        <div class="field mb-6">
            <label class="label">Body Weight (lbs)</label>
            <div class="control has-icons-left">
                <input class="input is-medium" type="number" step="0.1" name="weight_lbs" value="{{ currentWeight }}" placeholder="e.g. 175.5" required autofocus>
                <span class="icon is-small is-left">
                    <i class="fas fa-weight"></i>
                </span>
            </div>
        </div>

        <div class="field">
            <div class="control">
                <button type="submit" class="button is-primary is-medium is-fullwidth">
                    <span class="icon"><i class="fas fa-save"></i></span>
                    <span>Save Weight</span>
                </button>
            </div>
        </div>
    </form>
</div>

{% endblock %}