{% extends "layouts/main.volt" %}

{% block content %}
<div class="columns is-centered mt-4">
    <div class="column is-half-tablet">
        <div class="box p-5">
            <h1 class="title is-3 has-text-centered mb-2">Welcome to Mahlzeit!</h1>
            <p class="subtitle is-6 has-text-centered has-text-grey mb-5">
                Let's set up your baseline so we can calculate your personalized daily calorie goals.
            </p>

            <form action="/onboarding/save" method="POST">

                <div class="columns is-mobile">
                    <div class="column">
                        <div class="field">
                            <label class="label">Date of Birth</label>
                            <div class="control has-icons-left">
                                <input class="input" type="date" name="dob" required>
                                <span class="icon is-small is-left"><i class="fas fa-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">Biological Sex</label>
                            <div class="control has-icons-left">
                                <div class="select is-fullwidth">
                                    <select name="gender" required>
                                        <option value="" disabled selected>Select...</option>
                                        <option value="M">Male</option>
                                        <option value="F">Female</option>
                                    </select>
                                </div>
                                <span class="icon is-small is-left"><i class="fas fa-venus-mars"></i></span>
                            </div>
                            <p class="help">Used for accurate BMR calculations.</p>
                        </div>
                    </div>
                </div>

                <hr>

                <label class="label">Height</label>
                <div class="columns is-mobile mb-0">
                    <div class="column">
                        <div class="field">
                            <div class="control has-icons-right">
                                <input class="input" type="number" name="height_ft" placeholder="Feet" min="3" max="8" required>
                                <span class="icon is-small is-right has-text-grey">ft</span>
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <div class="control has-icons-right">
                                <input class="input" type="number" name="height_in" placeholder="Inches" min="0" max="11" required>
                                <span class="icon is-small is-right has-text-grey">in</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="field mb-4 mt-3">
                    <label class="label">Current Weight</label>
                    <div class="control has-icons-left has-icons-right">
                        <input class="input" type="number" step="0.1" name="weight_lbs" placeholder="e.g. 175.5" required>
                        <span class="icon is-small is-left"><i class="fas fa-weight"></i></span>
                        <span class="icon is-small is-right has-text-grey">lbs</span>
                    </div>
                </div>

                <hr>

                <div class="field mb-6">
                    <label class="label">Daily Activity Level</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="activity_level" required>
                                <option value="1.2">Sedentary (Office job, little to no exercise)</option>
                                <option value="1.375">Lightly Active (Light exercise 1-3 days/week)</option>
                                <option value="1.55">Moderately Active (Moderate exercise 3-5 days/week)</option>
                                <option value="1.725">Very Active (Hard exercise 6-7 days/week)</option>
                                <option value="1.9">Extra Active (Very hard physical job or training twice a day)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <button type="submit" class="button is-primary is-medium is-fullwidth">
                        Complete Profile & Start Tracking
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
{% endblock %}