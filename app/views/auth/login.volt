{% extends "layouts/main.volt" %}

{% block content %}
<div class="columns is-centered mt-5">
    <div class="column is-half-tablet is-one-third-desktop">
        <div class="box">
            <h1 class="title has-text-centered mb-5">Welcome Back</h1>

            <form action="/login" method="POST">
                <div class="field mb-4">
                    <label class="label">Email</label>
                    <div class="control has-icons-left">
                        <input class="input is-medium" type="email" name="email" required autofocus>
                        <span class="icon is-small is-left"><i class="fas fa-envelope"></i></span>
                    </div>
                </div>

                <div class="field mb-5">
                    <label class="label">Password</label>
                    <div class="control has-icons-left">
                        <input class="input is-medium" type="password" name="password" required>
                        <span class="icon is-small is-left"><i class="fas fa-lock"></i></span>
                    </div>
                </div>

                <div class="field">
                    <button type="submit" class="button is-link is-fullwidth is-medium">Log In</button>
                </div>
            </form>

            <p class="has-text-centered mt-4">
                Don't have an account? <a href="/register">Sign up</a>
            </p>
        </div>
    </div>
</div>
{% endblock %}