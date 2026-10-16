<?php

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

class InitSchemaMigration_100 extends Migration {
    
    public function up()
    {
        // 1. Users
        $this->morphTable('users', [
            'columns' => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'notNull' => true, 'autoIncrement' => true, 'primary' => true]),
                new Column('email', ['type' => Column::TYPE_VARCHAR, 'size' => 255, 'notNull' => true]),
                new Column('password', ['type' => Column::TYPE_VARCHAR, 'size' => 255, 'notNull' => true]),
                new Column('height_cm', ['type' => Column::TYPE_INTEGER, 'notNull' => false]),
                new Column('current_weight_kg', ['type' => Column::TYPE_DECIMAL, 'size' => 5, 'scale' => 2, 'notNull' => false]),
                new Column('created_at', ['type' => Column::TYPE_DATETIME, 'notNull' => true, 'default' => 'CURRENT_TIMESTAMP']),
                new Column('dob', ['type' => Column::TYPE_DATE, 'notNull' => false]),
                new Column('gender', ['type' => Column::TYPE_VARCHAR, 'size' => 10, 'notNull' => false]), // 'M', 'F', etc.
                // 1.2 = Sedentary, 1.55 = Active, etc.
                new Column('activity_level', ['type' => Column::TYPE_DECIMAL, 'size' => 4, 'scale' => 3, 'notNull' => true, 'default' => 1.2]), 
            ],
            'indexes' => [
                new Index('users_email_unique', ['email'], 'UNIQUE')
            ]
        ]);

        // 2. Meal Types (Configurable Buckets)
        $this->morphTable('meal_types', [
            'columns' => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'notNull' => true, 'autoIncrement' => true, 'primary' => true]),
                new Column('user_id', ['type' => Column::TYPE_INTEGER, 'notNull' => true]),
                new Column('name', ['type' => Column::TYPE_VARCHAR, 'size' => 100, 'notNull' => true]),
                new Column('sort_order', ['type' => Column::TYPE_INTEGER, 'notNull' => true, 'default' => 0]),
            ],
            'references' => [
                new Reference('fk_meal_types_user', [
                    'referencedTable' => 'users',
                    'columns' => ['user_id'],
                    'referencedColumns' => ['id'],
                    'onDelete' => 'CASCADE'
                ])
            ]
        ]);

        // 3. Foods
        $this->morphTable('foods', [
            'columns' => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'notNull' => true, 'autoIncrement' => true, 'primary' => true]),
                new Column('barcode', ['type' => Column::TYPE_VARCHAR, 'size' => 48, 'notNull' => true]),
                new Column('user_id', ['type' => Column::TYPE_INTEGER, 'notNull' => false]), // NULL = Global
                new Column('name', ['type' => Column::TYPE_VARCHAR, 'size' => 200, 'notNull' => true]),
                new Column('brand', ['type' => Column::TYPE_VARCHAR, 'size' => 100, 'notNull' => false]),
                new Column('calories', ['type' => Column::TYPE_INTEGER, 'notNull' => true]),
                new Column('protein_g', ['type' => Column::TYPE_DECIMAL, 'size' => 6, 'scale' => 2, 'notNull' => true, 'default' => 0]),
                new Column('fat_g', ['type' => Column::TYPE_DECIMAL, 'size' => 6, 'scale' => 2, 'notNull' => true, 'default' => 0]),
                new Column('carbs_g', ['type' => Column::TYPE_DECIMAL, 'size' => 6, 'scale' => 2, 'notNull' => true, 'default' => 0]),
                new Column('serving_size', ['type' => Column::TYPE_DECIMAL, 'size' => 6, 'scale' => 2, 'notNull' => true, 'default' => 100]),
                new Column('serving_unit', ['type' => Column::TYPE_VARCHAR, 'size' => 50, 'notNull' => true, 'default' => 'g']),

                new Column('fiber_g', ['type' => Column::TYPE_DECIMAL, 'size' => 6, 'scale' => 2, 'notNull' => true]),
                new Column('sugar_g', ['type' => Column::TYPE_DECIMAL, 'size' => 6, 'scale' => 2, 'notNull' => false]),
                new Column('sodium_mg', ['type' => Column::TYPE_INTEGER, 'notNull' => true]),
                new Column('cholesterol_mg', ['type' => Column::TYPE_INTEGER, 'notNull' => false]),

                new Column('calcium_mg', ['type' => Column::TYPE_INTEGER, 'notNull' => false, 'default' => 0]),
                new Column('iron_mg', ['type' => Column::TYPE_INTEGER, 'notNull' => false, 'default' => 0]),

                new Column('ingredients', ['type' => Column::TYPE_TEXT, 'notNull' => false]),
            ],
            'indexes' => [
                // The first parameter is the index name, the second is the column(s)
                new Index('idx_barcode', ['barcode']),
                new Index('idx_user_id', ['user_id'])
            ],
        ]);

        // 4. Diary Entries
        $this->morphTable('diary_entries', [
            'columns' => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'notNull' => true, 'autoIncrement' => true, 'primary' => true]),
                new Column('user_id', ['type' => Column::TYPE_INTEGER, 'notNull' => true]),
                new Column('meal_type_id', ['type' => Column::TYPE_INTEGER, 'notNull' => true]),
                new Column('food_id', ['type' => Column::TYPE_INTEGER, 'notNull' => true]),
                new Column('date', ['type' => Column::TYPE_DATE, 'notNull' => true]),
                new Column('quantity', ['type' => Column::TYPE_DECIMAL, 'size' => 6, 'scale' => 2, 'notNull' => true, 'default' => 1]),
            ],
            'indexes' => [
                new Index('idx_diary_date', ['user_id', 'date'])
            ],
            'references' => [
                new Reference('fk_diary_meal', [
                    'referencedTable' => 'meal_types',
                    'columns' => ['meal_type_id'],
                    'referencedColumns' => ['id'],
                    'onDelete' => 'CASCADE'
                ]),
                new Reference('fk_diary_food', [
                    'referencedTable' => 'foods',
                    'columns' => ['food_id'],
                    'referencedColumns' => ['id'],
                    'onDelete' => 'CASCADE'
                ])
            ]
        ]);

        // 5. User Favorites (Pinned/Recent)
        $this->morphTable('user_favorites', [
            'columns' => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'notNull' => true, 'autoIncrement' => true, 'primary' => true]),
                new Column('user_id', ['type' => Column::TYPE_INTEGER, 'notNull' => true]),
                new Column('food_id', ['type' => Column::TYPE_INTEGER, 'notNull' => true]),
                new Column('is_pinned', ['type' => Column::TYPE_BOOLEAN, 'notNull' => true, 'default' => 0]),
                new Column('last_eaten_at', ['type' => Column::TYPE_DATETIME, 'notNull' => true]),
            ],
            'indexes' => [
                new Index('idx_fav_unique', ['user_id', 'food_id'], 'UNIQUE')
            ]
        ]);

        // 6. User Goals (Historical tracking of targets)
        $this->morphTable('user_goals', [
            'columns' => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'notNull' => true, 'autoIncrement' => true, 'primary' => true]),
                new Column('user_id', ['type' => Column::TYPE_INTEGER, 'notNull' => true]),
                // Rate: Negative for loss (-0.5), 0 for maintain, Positive for bulk (+0.5)
                new Column('weekly_goal_rate_kg', ['type' => Column::TYPE_DECIMAL, 'size' => 4, 'scale' => 2, 'notNull' => true, 'default' => 0]), 
                new Column('target_weight_kg', ['type' => Column::TYPE_DECIMAL, 'size' => 5, 'scale' => 2, 'notNull' => false]),
                
                // Targets
                new Column('daily_calorie_target', ['type' => Column::TYPE_INTEGER, 'notNull' => true]),
                new Column('macro_protein_pct', ['type' => Column::TYPE_INTEGER, 'notNull' => true, 'default' => 20]),
                new Column('macro_fat_pct', ['type' => Column::TYPE_INTEGER, 'notNull' => true, 'default' => 30]),
                new Column('macro_carbs_pct', ['type' => Column::TYPE_INTEGER, 'notNull' => true, 'default' => 50]),
                
                // When this goal was set (active from this date forward)
                new Column('created_at', ['type' => Column::TYPE_DATETIME, 'notNull' => true, 'default' => 'CURRENT_TIMESTAMP']),
            ],
            'indexes' => [
                new Index('idx_user_goal_history', ['user_id', 'created_at'])
            ],
            'references' => [
                new Reference('fk_goal_user', [
                    'referencedTable' => 'users',
                    'columns' => ['user_id'],
                    'referencedColumns' => ['id'],
                    'onDelete' => 'CASCADE'
                ])
            ]
        ]);

        // 7. Weight Logs (For the progress graph)
        $this->morphTable('weight_logs', [
            'columns' => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'notNull' => true, 'autoIncrement' => true, 'primary' => true]),
                new Column('user_id', ['type' => Column::TYPE_INTEGER, 'notNull' => true]),
                new Column('date', ['type' => Column::TYPE_DATE, 'notNull' => true]),
                new Column('weight_kg', ['type' => Column::TYPE_DECIMAL, 'size' => 5, 'scale' => 2, 'notNull' => true]),
            ],
            'indexes' => [
                // Unique so they only have one official weight logged per day
                new Index('idx_weight_user_date', ['user_id', 'date'], 'UNIQUE') 
            ],
            'references' => [
                new Reference('fk_weight_user', [
                    'referencedTable' => 'users',
                    'columns' => ['user_id'],
                    'referencedColumns' => ['id'],
                    'onDelete' => 'CASCADE'
                ])
            ]
        ]);

        // 8. Activities Catalog (The METs Database)
        $this->morphTable('activities', [
            'columns' => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'notNull' => true, 'autoIncrement' => true, 'primary' => true]),
                new Column('name', ['type' => Column::TYPE_VARCHAR, 'size' => 150, 'notNull' => true]), // e.g., "Running, 6 mph"
                new Column('category', ['type' => Column::TYPE_VARCHAR, 'size' => 50, 'notNull' => true]), // e.g., "Cardio", "Strength"
                new Column('met_value', ['type' => Column::TYPE_DECIMAL, 'size' => 5, 'scale' => 2, 'notNull' => true]),
            ]
        ]);

        // 9. Exercise Logs (The User's Daily Diary)
        $this->morphTable('exercise_logs', [
            'columns' => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'notNull' => true, 'autoIncrement' => true, 'primary' => true]),
                new Column('user_id', ['type' => Column::TYPE_INTEGER, 'notNull' => true]),
                new Column('date', ['type' => Column::TYPE_DATE, 'notNull' => true]),
                // Nullable if they type a custom exercise
                new Column('activity_id', ['type' => Column::TYPE_INTEGER, 'notNull' => false]),
                new Column('custom_name', ['type' => Column::TYPE_VARCHAR, 'size' => 150, 'notNull' => false]),
                new Column('duration_minutes', ['type' => Column::TYPE_INTEGER, 'notNull' => true]),
                // We hard-save the calories burned so historical data doesn't change if their weight changes later
                new Column('calories_burned', ['type' => Column::TYPE_INTEGER, 'notNull' => true]),
            ],
            'indexes' => [
                new Index('idx_exercise_date', ['user_id', 'date'])
            ],
            'references' => [
                new Reference('fk_exercise_user', [
                    'referencedTable' => 'users',
                    'columns' => ['user_id'],
                    'referencedColumns' => ['id'],
                    'onDelete' => 'CASCADE'
                ]),
                new Reference('fk_exercise_activity', [
                    'referencedTable' => 'activities',
                    'columns' => ['activity_id'],
                    'referencedColumns' => ['id'],
                    'onDelete' => 'SET NULL' // If we delete an activity from the global DB, keep the user's log but orphan the ID
                ])
            ]
        ]);

        // 1. The Parent Template
        $this->morphTable('meal_templates', [
            'columns' => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'notNull' => true, 'autoIncrement' => true, 'primary' => true]),
                new Column('user_id', ['type' => Column::TYPE_INTEGER, 'notNull' => true]),
                new Column('name', ['type' => Column::TYPE_VARCHAR, 'size' => 150, 'notNull' => true]),
                new Column('created_at', ['type' => Column::TYPE_DATETIME, 'notNull' => true, 'default' => 'CURRENT_TIMESTAMP']),
            ],
            'indexes' => [
                new Index('idx_template_user', ['user_id'])
            ]
        ]);

        // 2. The Items inside the Template
        $this->morphTable('meal_template_items', [
            'columns' => [
                new Column('id', ['type' => Column::TYPE_INTEGER, 'notNull' => true, 'autoIncrement' => true, 'primary' => true]),
                new Column('template_id', ['type' => Column::TYPE_INTEGER, 'notNull' => true]),
                new Column('food_id', ['type' => Column::TYPE_INTEGER, 'notNull' => true]),
                new Column('quantity', ['type' => Column::TYPE_DECIMAL, 'size' => 6, 'scale' => 2, 'notNull' => true, 'default' => 1]),
            ],
            'indexes' => [
                new Index('idx_template_item_parent', ['template_id'])
            ]
        ]);
    }
}