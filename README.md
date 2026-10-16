# Mahlzeit 🍎

*Still a WIP* - Mahlzeit is a high-performance, self-hosted meal and calorie tracking application built on the Phalcon PHP framework. Designed with a clean, mobile-first interface using Bulma CSS, it provides advanced biometric tracking, dynamic macro calculations, and lightning-fast food discovery via full-text search engines.

## Key Features

* **Dynamic Calorie Budgets:** Automatically calculates TDEE and BMR using the Mifflin-St Jeor equation based on your biological profile and weight goals.
* **Macro & Micronutrient Tracking:** Real-time diary math for Protein, Carbs, Fat, Fiber, Sugar, and essential vitamins.
* **Advanced Food Discovery:**
* Native integration with Solr, Meilisearch, or RedisSearch for millisecond full-text lookups.
* Built-in barcode scanner utilizing your device's camera.
* "My Foods" database for custom recipes.


* **Reusable Meal Templates:** Save frequently eaten meals to log them again with a single click.
* **Exercise & Activity Logging:** Built-in MET-based calorie burn calculator with a massive library of activities.
* **Data Visualization:** 30-day carry-forward progress charting using Chart.js.
* **Customizable Diary:** Create, rename, and reorganize your daily meal buckets (e.g., Breakfast, Lunch, Second Breakfast).

---

## Tech Stack

* **Backend:** PHP 8.x, Phalcon Framework
* **Frontend:** Volt Templating, Bulma CSS, vanilla JavaScript
* **Database:** MySQL / MariaDB
* **Search Infrastructure:** Interchangeable adapters for Apache Solr, Meilisearch, or RedisSearch
* **Caching/Sessions:** Memcached

---

## Installation & Setup

**1. Clone the repository and install dependencies:**

```bash
git clone https://github.com/yourusername/mahlzeit.git
cd mahlzeit
composer install

```

**2. Configure the Environment:**
Copy the example environment file and update your database and search engine credentials.

```bash
cp .env.example .env

```

**3. Initialize the Database:**
Run the Phalcon migrations to build the schema (Users, Diary Entries, Foods, etc.).

```bash
phalcon migration run --migrations=app/Migrations/

```

**4. Setup the Search Index:**
Depending on your `.env` configuration (Solr, Redis, or Meilisearch), ensure your search container is running. The application will automatically push schema updates (like filterable attributes) upon its first connection.

---

## CLI Tools (`bin/console`)

Mahlzeit includes a powerful command-line interface for managing data imports, exports, and search indexing. Run these commands from the root directory of the project.

### Data Import & Search Management

| Command | Description |
| --- | --- |
| `php bin/console import foods <file.json> <provider>` | Streams and imports a massive food database (Supports `fooddata_api` and `open_foods_api`). Automatically pushes to the search index. |
| `php bin/console import reindex` | Fetches all foods from the MySQL database in chunks and re-syncs them to your active search provider. |
| `php bin/console exercise_data activities` | Seeds the database with the built-in catalog of MET-based physical activities. Safely upserts existing data. |

### Custom Food Portability

You can easily backup or share your custom-created foods between users or environments.

| Command | Description |
| --- | --- |
| `php bin/console custom_food export <user_id> <path/file.json>` | Strips database IDs and exports a clean, portable JSON file of a specific user's custom foods. |
| `php bin/console custom_food import <user_id> <path/file.json>` | Imports a custom food JSON payload, assigns it to the target user, and syncs it to the search engine. |

---

## Architecture Overview

Mahlzeit is structured around Phalcon's MVC pattern with heavy reliance on Service Providers (`app/Providers/`) to maintain a clean Dependency Injection container.

* **Controllers:** Located in `app/Controllers/`. Enforces public/private routing and manages business logic.
* **Models:** Located in `app/Models/`. Standard Phalcon ORM classes mapping to the MySQL schema.
* **Views:** Located in `app/views/`. Written in `.volt` with a strictly mobile-responsive Bulma layout.
* **Search Adapters:** Located in `app/Search/Adapters/`. Implements `SearchAdapterInterface` to allow hot-swapping between Solr, Meilisearch, and Redis without changing controller logic.
* **Tasks:** Located in `app/Tasks/`. CLI scripts executed via `bin/console`.


### Search Infrastructure Setup

Mahlzeit supports three different search engines. Depending on which adapter you configured in your `.env`, you must initialize the schema before running your first import.

**If using Apache Solr:**
Solr requires you to explicitly create a core to hold the documents. If you are running Solr via Docker, execute:

```bash
docker compose exec solr solr create_core -c mahlzeit_foods

```

**If using Redis (RediSearch):**
Redis requires the index and schema to be defined upfront so it knows how to map your Hashes. Connect to your Redis CLI (`redis-cli`) and run:

```text
FT.CREATE idx:foods ON HASH PREFIX 1 "food:" SCHEMA id NUMERIC name TEXT WEIGHT 5.0 brand TEXT WEIGHT 2.0 ingredients TEXT calories NUMERIC user_id TAG

```

*(Note: We use `TAG` for the `user_id` to allow for blazing-fast privacy filtering between global and personal custom foods).*

**If using Meilisearch:**
Zero CLI setup is required! Meilisearch handles index creation dynamically. The moment you run your first `bin/console import foods` command, Mahlzeit will connect via the API, create the `foods` index, and automatically apply the necessary filterable attributes for the `user_id`.