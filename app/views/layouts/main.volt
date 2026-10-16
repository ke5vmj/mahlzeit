<!DOCTYPE html>
<html lang="en" class="has-navbar-fixed-top" data-theme="light">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mahlzeit | Calorie Tracker</title>

    {# Output the CSS collection defined in ControllerBase #}
    {{ assets.outputCss('headerCss') }}
  </head>
  <body>

    {# Global Navbar #}
    <nav class="navbar is-primary is-fixed-top" role="navigation" aria-label="main navigation">
      <div class="app-container" style="width: 100%; display: flex; justify-content: space-between;">
          <div class="navbar-brand">
            <a class="navbar-item" href="/">
              <span class="icon is-medium"><i class="fas fa-weight-hanging"></i></span>
              <span class="is-size-4 has-text-weight-bold ml-2">Mahlzeit</span>
            </a>
          </div>

          <div class="navbar-menu is-active" style="box-shadow: none; background: transparent;">
            <div class="navbar-end is-flex-direction-row is-align-items-center" style="display: flex;">

                {# AUTHENTICATION CHECK #}
                {% if session.has('AUTH_USER_ID') %}

                    {# Logged In Links #}
                    <a href="/diary" class="navbar-item"><i class="fas fa-book-open mr-1"></i> Log</a>
                    <a href="/goals" class="navbar-item"><i class="fas fa-bullseye mr-1"></i> Goals</a>
                    <a href="/trends" class="navbar-item"><i class="fas fa-chart-line mr-1"></i> Trends</a>
                    <a href="/settings" class="navbar-item"><i class="fas fa-user mr-1"></i> Me</a>

                {% else %}

                    {# Logged Out Links #}
                    <div class="navbar-item">
                        <div class="buttons mb-0">
                            <a class="button is-primary is-inverted is-small has-text-weight-bold" href="/register">Sign up</a>
                            <a class="button is-primary is-dark is-small" href="/login">Log in</a>
                        </div>
                    </div>

                {% endif %}

            </div>
          </div>
      </div>
    </nav>

    {# Main Content Area #}
    <section class="section">
        <div class="app-container">
            {# This single line will render any flash messages! #}
            {{ flashSession.output() }}

            {% block content %}{% endblock %}
        </div>
    </section>

  {# --- DATABASE PROFILER DEBUG BOX --- #}
  {% if dbProfiles is defined and dbProfiles|length > 0 %}
  <section class="section pt-0">
    <div class="app-container">
      <article class="message is-dark is-small">
        <div class="message-header">
          <p>
            <span class="icon"><i class="fas fa-database"></i></span>
            <span>DB Profiler ({{ dbTotalQueries }} queries)</span>
          </p>
        </div>
        <div class="message-body p-0" style="max-height: 300px; overflow-y: auto;">
          <table class="table is-fullwidth is-striped is-hoverable is-narrow mb-0">
            <thead>
              <tr>
                <th style="width: 50px;">#</th>
                <th>SQL Statement</th>
                <th style="width: 120px;">Time (sec)</th>
              </tr>
            </thead>
            <tbody>
              {% for index, profile in dbProfiles %}
                <tr>
                  <td>{{ index + 1 }}</td>
                  <td><code style="background: transparent; color: inherit; padding: 0;">{{ profile.getSQLStatement() }}</code></td>
                  <td class="has-text-weight-bold {% if profile.getTotalElapsedSeconds() > 0.1 %}has-text-danger{% endif %}">
                    {{ "%.5f"|format(profile.getTotalElapsedSeconds()) }}
                  </td>
                </tr>
              {% endfor %}
            </tbody>
          </table>
        </div>
      </article>
    </div>
  </section>
  {% endif %}

  </body>
</html>