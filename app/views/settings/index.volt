{% extends "layouts/main.volt" %}

{% block content %}
<div class="level is-mobile mb-5">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title is-4 mb-0">Settings</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="/diary" class="button is-white">Done</a>
        </div>
    </div>
</div>

<div class="columns is-centered">
    <div class="column is-half-tablet">

        <div class="box p-0 is-clipped mb-6">
            <div class="has-background-white-ter px-4 py-3 border-bottom">
                <h2 class="title is-6 mb-0 has-text-grey">Your Meal Categories</h2>
            </div>

            {# List the current meal types #}
            {% for meal in meals %}
            <div class="panel-block is-flex is-justify-content-space-between py-4 px-4 has-background-white">
                <span class="has-text-weight-medium">{{ meal.getName() }}</span>

                {# The Delete Form #}
                <form action="/settings/meal/{{ meal.getId() }}/delete" method="POST" style="margin: 0;">
                    <button type="submit" class="button is-small is-ghost has-text-danger" onclick="return confirm('Delete this meal category?');">
                        <span class="icon"><i class="fas fa-trash"></i></span>
                    </button>
                </form>
            </div>
            {% endfor %}

            {# Add a New Meal Form #}
            <div class="has-background-light px-4 py-4">
                <form action="/settings/meal/add" method="POST">
                    <div class="field has-addons mb-0">
                        <div class="control is-expanded">
                            <input class="input" type="text" name="name" placeholder="e.g. Post-Workout Shake" required>
                        </div>
                        <div class="control">
                            <button type="submit" class="button is-primary">Add</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    {# BIOLOGICAL PROFILE FORM #}
            <div class="box mb-6">
                <h2 class="title is-5 mb-4">Biological Profile</h2>

                <form action="/settings/profile/update" method="POST">

                    <div class="columns is-mobile">
                        <div class="column">
                            <div class="field">
                                <label class="label is-small">Date of Birth</label>
                                <div class="control has-icons-left">
                                    <input class="input" type="date" name="dob" value="{{ user.getDob() }}" required>
                                    <span class="icon is-small is-left"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="column">
                            <div class="field">
                                <label class="label is-small">Sex</label>
                                <div class="control has-icons-left">
                                    <div class="select is-fullwidth">
                                        <select name="gender" required>
                                            <option value="M" {% if user.getGender() == 'M' %}selected{% endif %}>Male</option>
                                            <option value="F" {% if user.getGender() == 'F' %}selected{% endif %}>Female</option>
                                        </select>
                                    </div>
                                    <span class="icon is-small is-left"><i class="fas fa-venus-mars"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <label class="label is-small">Height</label>
                    <div class="columns is-mobile mb-0">
                        <div class="column">
                            <div class="field">
                                <div class="control has-icons-right">
                                    <input class="input" type="number" name="height_ft" value="{{ heightFt }}" min="3" max="8" required>
                                    <span class="icon is-small is-right has-text-grey">ft</span>
                                </div>
                            </div>
                        </div>
                        <div class="column">
                            <div class="field">
                                <div class="control has-icons-right">
                                    <input class="input" type="number" name="height_in" value="{{ heightIn }}" min="0" max="11" required>
                                    <span class="icon is-small is-right has-text-grey">in</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="field mb-5 mt-3">
                        <label class="label is-small">Daily Activity Level</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="activity_level" required>
                                    <option value="1.2" {% if user.getActivityLevel() == 1.2 %}selected{% endif %}>Sedentary (Office job, little/no exercise)</option>
                                    <option value="1.375" {% if user.getActivityLevel() == 1.375 %}selected{% endif %}>Lightly Active (Light exercise 1-3 days/week)</option>
                                    <option value="1.55" {% if user.getActivityLevel() == 1.55 %}selected{% endif %}>Moderately Active (Moderate exercise 3-5 days/week)</option>
                                    <option value="1.725" {% if user.getActivityLevel() == 1.725 %}selected{% endif %}>Very Active (Hard exercise 6-7 days/week)</option>
                                    <option value="1.9" {% if user.getActivityLevel() == 1.9 %}selected{% endif %}>Extra Active (Very hard job/training)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <button type="submit" class="button is-primary is-fullwidth">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>

        {# A handy logout button at the bottom of the settings page #}
        <div class="has-text-centered">
            <a href="/logout" class="button is-danger is-light is-fullwidth">
                <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
                <span>Log Out</span>
            </a>
        </div>

    </div>
</div>
{% endblock %}