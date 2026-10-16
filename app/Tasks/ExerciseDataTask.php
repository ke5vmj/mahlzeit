<?php
declare(strict_types=1);

namespace App\Tasks;

use Phalcon\Cli\Task;
use App\Models\Activities;

class ExerciseDataTask extends Task {

    private array $exercises = [
        // ── WALKING ──────────────────────────────────────────────────────────────
        1  => ['name' => 'Walking, slow (2 mph)',                          'met' => 2.8,  'category' => 'Walking'],
        2  => ['name' => 'Walking, brisk (3.5 mph)',                       'met' => 4.3,  'category' => 'Walking'],
        3  => ['name' => 'Walking, very brisk (4.5 mph)',                  'met' => 6.3,  'category' => 'Walking'],
        4  => ['name' => 'Walking, uphill (3.5 mph, 5% grade)',            'met' => 5.3,  'category' => 'Walking'],
        5  => ['name' => 'Walking, uphill (3.5 mph, 10% grade)',           'met' => 8.0,  'category' => 'Walking'],
        6  => ['name' => 'Walking, with loaded backpack (>20 lb)',         'met' => 7.0,  'category' => 'Walking'],
        7  => ['name' => 'Nordic walking (poles, brisk)',                  'met' => 6.8,  'category' => 'Walking'],
        8  => ['name' => 'Treadmill walking (incline, 3.5 mph, 10%)',      'met' => 8.0,  'category' => 'Walking'],

        // ── RUNNING ──────────────────────────────────────────────────────────────
        9  => ['name' => 'Running, jogging (5 mph)',                       'met' => 8.3,  'category' => 'Running'],
        10 => ['name' => 'Running (6 mph)',                                'met' => 9.8,  'category' => 'Running'],
        11 => ['name' => 'Running (7.5 mph)',                              'met' => 11.8, 'category' => 'Running'],
        12 => ['name' => 'Running (10 mph)',                               'met' => 14.5, 'category' => 'Running'],
        13 => ['name' => 'Running (12 mph)',                               'met' => 16.0, 'category' => 'Running'],
        14 => ['name' => 'Running, trail / cross-country',                 'met' => 9.0,  'category' => 'Running'],
        15 => ['name' => 'Running, uphill (8% grade, 6 mph)',              'met' => 13.3, 'category' => 'Running'],
        16 => ['name' => 'Sprint intervals (all-out, short bursts)',       'met' => 17.0, 'category' => 'Running'],
        17 => ['name' => 'Marathon pacing (7–8 min/mile)',                 'met' => 12.0, 'category' => 'Running'],

        // ── CYCLING ──────────────────────────────────────────────────────────────
        18 => ['name' => 'Cycling, stationary (moderate, 100W)',           'met' => 6.8,  'category' => 'Cycling'],
        19 => ['name' => 'Cycling, stationary (vigorous, 150W)',           'met' => 8.8,  'category' => 'Cycling'],
        20 => ['name' => 'Cycling, stationary (very vigorous, 200W+)',     'met' => 11.0, 'category' => 'Cycling'],
        21 => ['name' => 'Cycling, outdoors (leisure, <10 mph)',           'met' => 4.0,  'category' => 'Cycling'],
        22 => ['name' => 'Cycling, outdoors (moderate, 12–14 mph)',        'met' => 8.0,  'category' => 'Cycling'],
        23 => ['name' => 'Cycling, outdoors (racing, 16–19 mph)',          'met' => 12.0, 'category' => 'Cycling'],
        24 => ['name' => 'Cycling, outdoors (>20 mph, very fast)',         'met' => 15.8, 'category' => 'Cycling'],
        25 => ['name' => 'Mountain biking (moderate terrain)',             'met' => 8.5,  'category' => 'Cycling'],
        26 => ['name' => 'Mountain biking (technical, hilly)',             'met' => 11.0, 'category' => 'Cycling'],
        27 => ['name' => 'Spin class (high-intensity)',                    'met' => 10.0, 'category' => 'Cycling'],
        28 => ['name' => 'BMX / trick cycling',                           'met' => 6.0,  'category' => 'Cycling'],

        // ── SWIMMING ─────────────────────────────────────────────────────────────
        29 => ['name' => 'Swimming, leisurely (light effort)',             'met' => 5.8,  'category' => 'Swimming'],
        30 => ['name' => 'Swimming, freestyle (moderate effort)',          'met' => 7.0,  'category' => 'Swimming'],
        31 => ['name' => 'Swimming, freestyle (vigorous effort)',          'met' => 9.8,  'category' => 'Swimming'],
        32 => ['name' => 'Swimming, breaststroke',                        'met' => 8.3,  'category' => 'Swimming'],
        33 => ['name' => 'Swimming, backstroke',                          'met' => 6.8,  'category' => 'Swimming'],
        34 => ['name' => 'Swimming, butterfly stroke',                    'met' => 13.8, 'category' => 'Swimming'],
        35 => ['name' => 'Swimming, sidestroke',                          'met' => 7.0,  'category' => 'Swimming'],
        36 => ['name' => 'Water polo',                                    'met' => 10.0, 'category' => 'Swimming'],
        37 => ['name' => 'Synchronized swimming',                         'met' => 8.0,  'category' => 'Swimming'],
        38 => ['name' => 'Open water swimming (moderate)',                 'met' => 8.3,  'category' => 'Swimming'],
        39 => ['name' => 'Aqua aerobics / water aerobics',                'met' => 5.5,  'category' => 'Swimming'],

        // ── GYM MACHINES ─────────────────────────────────────────────────────────
        40 => ['name' => 'Elliptical trainer (moderate)',                  'met' => 5.0,  'category' => 'Gym Machines'],
        41 => ['name' => 'Elliptical trainer (vigorous)',                  'met' => 7.0,  'category' => 'Gym Machines'],
        42 => ['name' => 'Rowing machine (moderate, 100W)',                'met' => 6.0,  'category' => 'Gym Machines'],
        43 => ['name' => 'Rowing machine (vigorous, 150W)',                'met' => 8.5,  'category' => 'Gym Machines'],
        44 => ['name' => 'Stair climber / step machine',                  'met' => 6.0,  'category' => 'Gym Machines'],
        45 => ['name' => 'Stair climber (vigorous)',                      'met' => 9.0,  'category' => 'Gym Machines'],
        46 => ['name' => 'SkiErg (moderate)',                             'met' => 7.5,  'category' => 'Gym Machines'],
        47 => ['name' => 'SkiErg (vigorous)',                             'met' => 10.5, 'category' => 'Gym Machines'],
        48 => ['name' => 'Assault / air bike (moderate)',                  'met' => 8.0,  'category' => 'Gym Machines'],
        49 => ['name' => 'Assault / air bike (all-out intervals)',         'met' => 14.0, 'category' => 'Gym Machines'],
        50 => ['name' => 'VersaClimber (moderate)',                       'met' => 8.5,  'category' => 'Gym Machines'],
        51 => ['name' => 'Hand cycle ergometer (vigorous)',               'met' => 6.5,  'category' => 'Gym Machines'],

        // ── STRENGTH & RESISTANCE ─────────────────────────────────────────────────
        52 => ['name' => 'Weight lifting (light to moderate)',             'met' => 3.5,  'category' => 'Strength & Resistance'],
        53 => ['name' => 'Weight lifting (vigorous, power lifting)',       'met' => 6.0,  'category' => 'Strength & Resistance'],
        54 => ['name' => 'Olympic weightlifting (snatch/clean & jerk)',    'met' => 7.5,  'category' => 'Strength & Resistance'],
        55 => ['name' => 'Kettlebell training (upright, swings)',          'met' => 8.0,  'category' => 'Strength & Resistance'],
        56 => ['name' => 'Kettlebell, ballistic (snatches, cleans)',       'met' => 11.0, 'category' => 'Strength & Resistance'],
        57 => ['name' => 'CrossFit / HIIT',                               'met' => 9.0,  'category' => 'Strength & Resistance'],
        58 => ['name' => 'Circuit training (moderate)',                    'met' => 6.5,  'category' => 'Strength & Resistance'],
        59 => ['name' => 'Circuit training (vigorous)',                    'met' => 8.0,  'category' => 'Strength & Resistance'],
        60 => ['name' => 'Bodyweight training (push-ups, pull-ups, etc.)', 'met' => 5.0,  'category' => 'Strength & Resistance'],
        61 => ['name' => 'Resistance band training (moderate)',            'met' => 3.5,  'category' => 'Strength & Resistance'],
        62 => ['name' => 'TRX / suspension training',                     'met' => 5.5,  'category' => 'Strength & Resistance'],
        63 => ['name' => 'Battle ropes (moderate)',                       'met' => 9.0,  'category' => 'Strength & Resistance'],
        64 => ['name' => 'Battle ropes (all-out intervals)',               'met' => 12.0, 'category' => 'Strength & Resistance'],
        65 => ['name' => 'Sled push / pull (moderate)',                   'met' => 9.5,  'category' => 'Strength & Resistance'],
        66 => ['name' => 'Tire flipping (moderate effort)',               'met' => 8.5,  'category' => 'Strength & Resistance'],
        67 => ['name' => 'Sandbag training',                              'met' => 7.5,  'category' => 'Strength & Resistance'],

        // ── JUMP ROPE & PLYOMETRICS ───────────────────────────────────────────────
        68 => ['name' => 'Jumping rope (slow, ~100 skips/min)',            'met' => 8.8,  'category' => 'Jump Rope & Plyometrics'],
        69 => ['name' => 'Jumping rope (fast, 120–160 skips/min)',         'met' => 11.8, 'category' => 'Jump Rope & Plyometrics'],
        70 => ['name' => 'Jumping rope (double unders)',                   'met' => 13.5, 'category' => 'Jump Rope & Plyometrics'],
        71 => ['name' => 'Box jumps (moderate)',                          'met' => 8.0,  'category' => 'Jump Rope & Plyometrics'],
        72 => ['name' => 'Burpees',                                       'met' => 10.0, 'category' => 'Jump Rope & Plyometrics'],
        73 => ['name' => 'Plyometric lunges / squat jumps',               'met' => 9.5,  'category' => 'Jump Rope & Plyometrics'],

        // ── YOGA, PILATES & FLEXIBILITY ──────────────────────────────────────────
        74 => ['name' => 'Stretching (light)',                            'met' => 2.3,  'category' => 'Yoga, Pilates & Flexibility'],
        75 => ['name' => 'Yoga, Hatha (gentle)',                          'met' => 2.5,  'category' => 'Yoga, Pilates & Flexibility'],
        76 => ['name' => 'Yoga, Restorative / Yin',                      'met' => 2.0,  'category' => 'Yoga, Pilates & Flexibility'],
        77 => ['name' => 'Yoga, Power / Vinyasa',                         'met' => 4.0,  'category' => 'Yoga, Pilates & Flexibility'],
        78 => ['name' => 'Yoga, Bikram / hot yoga',                       'met' => 5.0,  'category' => 'Yoga, Pilates & Flexibility'],
        79 => ['name' => 'Ashtanga yoga',                                 'met' => 5.5,  'category' => 'Yoga, Pilates & Flexibility'],
        80 => ['name' => 'Pilates (general)',                             'met' => 3.0,  'category' => 'Yoga, Pilates & Flexibility'],
        81 => ['name' => 'Pilates (reformer, vigorous)',                  'met' => 4.5,  'category' => 'Yoga, Pilates & Flexibility'],
        82 => ['name' => 'Tai chi (gentle)',                              'met' => 3.0,  'category' => 'Yoga, Pilates & Flexibility'],
        83 => ['name' => 'Qigong',                                       'met' => 2.5,  'category' => 'Yoga, Pilates & Flexibility'],

        // ── AEROBICS & DANCE ─────────────────────────────────────────────────────
        84 => ['name' => 'Aerobics, low impact',                          'met' => 5.0,  'category' => 'Aerobics & Dance'],
        85 => ['name' => 'Aerobics, high impact',                         'met' => 7.3,  'category' => 'Aerobics & Dance'],
        86 => ['name' => 'Step aerobics (6–8 in. step)',                  'met' => 8.5,  'category' => 'Aerobics & Dance'],
        87 => ['name' => 'Step aerobics (10–12 in. step)',                'met' => 10.0, 'category' => 'Aerobics & Dance'],
        88 => ['name' => 'Zumba / dance fitness',                         'met' => 6.5,  'category' => 'Aerobics & Dance'],
        89 => ['name' => 'Ballroom dancing (slow)',                       'met' => 3.0,  'category' => 'Aerobics & Dance'],
        90 => ['name' => 'Ballroom dancing (fast, e.g. swing)',           'met' => 5.5,  'category' => 'Aerobics & Dance'],
        91 => ['name' => 'Ballet / barre class',                         'met' => 4.8,  'category' => 'Aerobics & Dance'],
        92 => ['name' => 'Hip-hop dancing',                               'met' => 7.0,  'category' => 'Aerobics & Dance'],
        93 => ['name' => 'Pole dancing / fitness',                        'met' => 5.0,  'category' => 'Aerobics & Dance'],
        94 => ['name' => 'Cardio kickboxing (aerobic)',                   'met' => 7.5,  'category' => 'Aerobics & Dance'],
        95 => ['name' => 'Irish dancing (competitive)',                    'met' => 8.0,  'category' => 'Aerobics & Dance'],
        96 => ['name' => 'Bhangra / high-energy folk dance',              'met' => 7.5,  'category' => 'Aerobics & Dance'],

        // ── OUTDOOR & ADVENTURE ──────────────────────────────────────────────────
        97  => ['name' => 'Hiking (cross-country, light load)',            'met' => 5.3,  'category' => 'Outdoor & Adventure'],
        98  => ['name' => 'Hiking (uphill, moderate load)',                'met' => 7.5,  'category' => 'Outdoor & Adventure'],
        99  => ['name' => 'Hiking (steep terrain, heavy pack >40 lb)',     'met' => 9.0,  'category' => 'Outdoor & Adventure'],
        100 => ['name' => 'Backpacking (overnight, mixed terrain)',        'met' => 7.0,  'category' => 'Outdoor & Adventure'],
        101 => ['name' => 'Kayaking (moderate effort)',                   'met' => 5.0,  'category' => 'Outdoor & Adventure'],
        102 => ['name' => 'Kayaking (vigorous / whitewater)',             'met' => 7.5,  'category' => 'Outdoor & Adventure'],
        103 => ['name' => 'Canoeing (moderate effort)',                   'met' => 4.0,  'category' => 'Outdoor & Adventure'],
        104 => ['name' => 'Stand-up paddleboarding (SUP, leisurely)',     'met' => 4.0,  'category' => 'Outdoor & Adventure'],
        105 => ['name' => 'Stand-up paddleboarding (SUP, vigorous)',      'met' => 6.5,  'category' => 'Outdoor & Adventure'],
        106 => ['name' => 'Surfing (paddle-out & riding)',                'met' => 5.0,  'category' => 'Outdoor & Adventure'],
        107 => ['name' => 'Kitesurfing / windsurfing',                    'met' => 7.0,  'category' => 'Outdoor & Adventure'],
        108 => ['name' => 'Rowing, on water (recreational)',              'met' => 7.0,  'category' => 'Outdoor & Adventure'],
        109 => ['name' => 'Rowing, on water (competitive)',               'met' => 12.0, 'category' => 'Outdoor & Adventure'],
        110 => ['name' => 'Rock climbing (rappelling)',                   'met' => 5.8,  'category' => 'Outdoor & Adventure'],
        111 => ['name' => 'Rock climbing (ascending, moderate)',          'met' => 8.0,  'category' => 'Outdoor & Adventure'],
        112 => ['name' => 'Rock climbing (competition, overhanging)',      'met' => 11.0, 'category' => 'Outdoor & Adventure'],
        113 => ['name' => 'Bouldering (moderate)',                        'met' => 7.5,  'category' => 'Outdoor & Adventure'],

        // ── WINTER SPORTS ────────────────────────────────────────────────────────
        114 => ['name' => 'Skiing, downhill (light effort)',              'met' => 5.3,  'category' => 'Winter Sports'],
        115 => ['name' => 'Skiing, downhill (moderate effort)',           'met' => 6.0,  'category' => 'Winter Sports'],
        116 => ['name' => 'Skiing, downhill (vigorous / moguls)',         'met' => 8.0,  'category' => 'Winter Sports'],
        117 => ['name' => 'Skiing, cross-country (slow)',                 'met' => 7.0,  'category' => 'Winter Sports'],
        118 => ['name' => 'Skiing, cross-country (moderate)',             'met' => 8.5,  'category' => 'Winter Sports'],
        119 => ['name' => 'Skiing, cross-country (racing)',               'met' => 13.3, 'category' => 'Winter Sports'],
        120 => ['name' => 'Snowboarding',                                 'met' => 5.3,  'category' => 'Winter Sports'],
        121 => ['name' => 'Snowshoeing (flat terrain)',                   'met' => 6.0,  'category' => 'Winter Sports'],
        122 => ['name' => 'Snowshoeing (hilly / deep snow)',              'met' => 8.5,  'category' => 'Winter Sports'],
        123 => ['name' => 'Ice skating (general)',                        'met' => 5.5,  'category' => 'Winter Sports'],
        124 => ['name' => 'Ice skating (vigorous / speed skating)',       'met' => 11.0, 'category' => 'Winter Sports'],
        125 => ['name' => 'Ice hockey (game)',                            'met' => 8.0,  'category' => 'Winter Sports'],
        126 => ['name' => 'Curling',                                      'met' => 4.0,  'category' => 'Winter Sports'],

        // ── WHEELED SPORTS ───────────────────────────────────────────────────────
        127 => ['name' => 'Rollerblading (moderate)',                     'met' => 6.5,  'category' => 'Wheeled Sports'],
        128 => ['name' => 'Rollerblading (vigorous / racing)',            'met' => 12.5, 'category' => 'Wheeled Sports'],
        129 => ['name' => 'Skateboarding',                                'met' => 5.0,  'category' => 'Wheeled Sports'],
        130 => ['name' => 'Scootering (kick scooter, moderate)',          'met' => 4.5,  'category' => 'Wheeled Sports'],

        // ── TEAM & COURT SPORTS ──────────────────────────────────────────────────
        131 => ['name' => 'Basketball (game)',                            'met' => 8.0,  'category' => 'Team & Court Sports'],
        132 => ['name' => 'Basketball (shooting baskets)',                'met' => 4.5,  'category' => 'Team & Court Sports'],
        133 => ['name' => 'Soccer (casual)',                              'met' => 7.0,  'category' => 'Team & Court Sports'],
        134 => ['name' => 'Soccer (competitive)',                         'met' => 10.0, 'category' => 'Team & Court Sports'],
        135 => ['name' => 'American football (game)',                     'met' => 8.0,  'category' => 'Team & Court Sports'],
        136 => ['name' => 'Flag football',                               'met' => 7.0,  'category' => 'Team & Court Sports'],
        137 => ['name' => 'Rugby (game)',                                 'met' => 10.0, 'category' => 'Team & Court Sports'],
        138 => ['name' => 'Rugby (touch / tag)',                          'met' => 6.5,  'category' => 'Team & Court Sports'],
        139 => ['name' => 'Field hockey',                                'met' => 8.0,  'category' => 'Team & Court Sports'],
        140 => ['name' => 'Lacrosse (game)',                             'met' => 8.0,  'category' => 'Team & Court Sports'],
        141 => ['name' => 'Baseball / softball (game)',                   'met' => 5.0,  'category' => 'Team & Court Sports'],
        142 => ['name' => 'Cricket (batting / bowling)',                  'met' => 5.0,  'category' => 'Team & Court Sports'],
        143 => ['name' => 'Volleyball (beach, competitive)',              'met' => 8.0,  'category' => 'Team & Court Sports'],
        144 => ['name' => 'Volleyball (indoor, casual)',                  'met' => 4.0,  'category' => 'Team & Court Sports'],
        145 => ['name' => 'Volleyball (indoor, competitive)',             'met' => 6.5,  'category' => 'Team & Court Sports'],

        // ── RACKET SPORTS ────────────────────────────────────────────────────────
        146 => ['name' => 'Tennis (singles)',                             'met' => 7.5,  'category' => 'Racket Sports'],
        147 => ['name' => 'Tennis (doubles)',                             'met' => 5.0,  'category' => 'Racket Sports'],
        148 => ['name' => 'Badminton (casual)',                           'met' => 4.5,  'category' => 'Racket Sports'],
        149 => ['name' => 'Badminton (competitive)',                      'met' => 7.0,  'category' => 'Racket Sports'],
        150 => ['name' => 'Squash (casual)',                             'met' => 7.5,  'category' => 'Racket Sports'],
        151 => ['name' => 'Squash (competitive)',                         'met' => 12.0, 'category' => 'Racket Sports'],
        152 => ['name' => 'Racquetball (casual)',                         'met' => 7.0,  'category' => 'Racket Sports'],
        153 => ['name' => 'Racquetball (competitive)',                    'met' => 10.0, 'category' => 'Racket Sports'],
        154 => ['name' => 'Pickleball (casual)',                         'met' => 4.5,  'category' => 'Racket Sports'],
        155 => ['name' => 'Pickleball (competitive)',                     'met' => 7.0,  'category' => 'Racket Sports'],
        156 => ['name' => 'Table tennis / ping pong',                    'met' => 4.0,  'category' => 'Racket Sports'],
        157 => ['name' => 'Padel (competitive)',                         'met' => 7.5,  'category' => 'Racket Sports'],

        // ── COMBAT SPORTS ────────────────────────────────────────────────────────
        158 => ['name' => 'Martial arts (karate, judo)',                  'met' => 8.0,  'category' => 'Combat Sports'],
        159 => ['name' => 'Boxing (sparring)',                            'met' => 9.0,  'category' => 'Combat Sports'],
        160 => ['name' => 'Boxing (punching bag)',                        'met' => 5.5,  'category' => 'Combat Sports'],
        161 => ['name' => 'Boxing (competitive match)',                   'met' => 12.0, 'category' => 'Combat Sports'],
        162 => ['name' => 'Muay Thai (pad work)',                        'met' => 10.0, 'category' => 'Combat Sports'],
        163 => ['name' => 'MMA training (drilling)',                     'met' => 9.5,  'category' => 'Combat Sports'],
        164 => ['name' => 'MMA (sparring)',                              'met' => 12.5, 'category' => 'Combat Sports'],
        165 => ['name' => 'Wrestling (practice)',                        'met' => 7.0,  'category' => 'Combat Sports'],
        166 => ['name' => 'Wrestling (competitive)',                      'met' => 10.0, 'category' => 'Combat Sports'],
        167 => ['name' => 'Brazilian jiu-jitsu (rolling)',               'met' => 9.5,  'category' => 'Combat Sports'],
        168 => ['name' => 'Fencing',                                    'met' => 6.0,  'category' => 'Combat Sports'],
        169 => ['name' => 'Cardio kickboxing (aerobic)',                 'met' => 7.5,  'category' => 'Combat Sports'],

        // ── GOLF & LOW-INTENSITY SPORTS ──────────────────────────────────────────
        170 => ['name' => 'Golf (walking, carrying clubs)',               'met' => 4.3,  'category' => 'Golf & Low-Intensity Sports'],
        171 => ['name' => 'Golf (using cart)',                           'met' => 3.0,  'category' => 'Golf & Low-Intensity Sports'],
        172 => ['name' => 'Golf (driving range)',                        'met' => 3.5,  'category' => 'Golf & Low-Intensity Sports'],
        173 => ['name' => 'Disc golf (walking course)',                  'met' => 3.5,  'category' => 'Golf & Low-Intensity Sports'],
        174 => ['name' => 'Bocce / lawn bowling',                       'met' => 2.5,  'category' => 'Golf & Low-Intensity Sports'],
        175 => ['name' => 'Bowling (10-pin)',                            'met' => 3.0,  'category' => 'Golf & Low-Intensity Sports'],
        176 => ['name' => 'Frisbee (casual toss)',                       'met' => 3.0,  'category' => 'Golf & Low-Intensity Sports'],
        177 => ['name' => 'Frisbee (ultimate)',                          'met' => 8.0,  'category' => 'Golf & Low-Intensity Sports'],
        178 => ['name' => 'Horseback riding (trotting)',                  'met' => 5.8,  'category' => 'Golf & Low-Intensity Sports'],
        179 => ['name' => 'Horseback riding (cantering / galloping)',     'met' => 7.0,  'category' => 'Golf & Low-Intensity Sports'],
        180 => ['name' => 'Archery (standing)',                          'met' => 3.5,  'category' => 'Golf & Low-Intensity Sports'],

        // ── MANUAL LABOR & DAILY LIFE ─────────────────────────────────────────────
        181 => ['name' => 'Gardening (light, weeding)',                  'met' => 3.5,  'category' => 'Manual Labor & Daily Life'],
        182 => ['name' => 'Gardening (heavy, digging)',                  'met' => 5.0,  'category' => 'Manual Labor & Daily Life'],
        183 => ['name' => 'Mowing lawn (walking push mower)',            'met' => 5.5,  'category' => 'Manual Labor & Daily Life'],
        184 => ['name' => 'Raking leaves',                              'met' => 4.0,  'category' => 'Manual Labor & Daily Life'],
        185 => ['name' => 'Shoveling snow (by hand)',                   'met' => 6.0,  'category' => 'Manual Labor & Daily Life'],
        186 => ['name' => 'Chopping / splitting wood',                  'met' => 6.0,  'category' => 'Manual Labor & Daily Life'],
        187 => ['name' => 'Carrying / moving boxes (moderate)',          'met' => 5.0,  'category' => 'Manual Labor & Daily Life'],
        188 => ['name' => 'Stair climbing (carrying load)',              'met' => 8.0,  'category' => 'Manual Labor & Daily Life'],
        189 => ['name' => 'Housework (mopping, vacuuming)',              'met' => 3.3,  'category' => 'Manual Labor & Daily Life'],
        190 => ['name' => 'Window washing (vigorous)',                   'met' => 3.5,  'category' => 'Manual Labor & Daily Life'],
        191 => ['name' => 'Painting (walls, moderate effort)',           'met' => 3.5,  'category' => 'Manual Labor & Daily Life'],
        192 => ['name' => 'Roofing / construction work (general)',       'met' => 5.5,  'category' => 'Manual Labor & Daily Life'],
        193 => ['name' => 'Farming (baling hay, shoveling grain)',       'met' => 7.5,  'category' => 'Manual Labor & Daily Life'],

        // ── GYMNASTICS & PERFORMANCE ──────────────────────────────────────────────
        194 => ['name' => 'Gymnastics (general)',                        'met' => 4.0,  'category' => 'Gymnastics & Performance'],
        195 => ['name' => 'Gymnastics (floor / tumbling)',               'met' => 6.5,  'category' => 'Gymnastics & Performance'],
        196 => ['name' => 'Trampoline (recreational)',                   'met' => 4.5,  'category' => 'Gymnastics & Performance'],
        197 => ['name' => 'Trampoline (competitive / vigorous)',         'met' => 7.5,  'category' => 'Gymnastics & Performance'],
        198 => ['name' => 'Cheerleading / acrobatics',                  'met' => 5.5,  'category' => 'Gymnastics & Performance'],
        199 => ['name' => 'Aerial silks / circus arts',                 'met' => 6.0,  'category' => 'Gymnastics & Performance'],

        // ── MISCELLANEOUS ─────────────────────────────────────────────────────────
        200 => ['name' => 'Handball',                                   'met' => 8.0,  'category' => 'Miscellaneous'],
        201 => ['name' => 'Climbing stairs (building, fast)',            'met' => 8.8,  'category' => 'Miscellaneous'],
        202 => ['name' => 'Carrying infant / child (walking)',           'met' => 3.5,  'category' => 'Miscellaneous'],
        203 => ['name' => 'Active video games (e.g. VR fitness)',        'met' => 4.5,  'category' => 'Miscellaneous'],
        204 => ['name' => 'Hula hooping',                               'met' => 5.5,  'category' => 'Miscellaneous'],
        205 => ['name' => 'Jump training / agility ladder drills',       'met' => 8.5,  'category' => 'Miscellaneous'],

        // 🐾 PETS & ANIMAL CARE
        206 => ['name' => 'Playing with dog (fetch, tug, active)',               'met' => 4.0,  'category' => 'Pets & Animal Care'],
        207 => ['name' => 'Walking dog (brisk)',                                 'met' => 3.5,  'category' => 'Pets & Animal Care'],
        208 => ['name' => 'Running with dog',                                    'met' => 8.0,  'category' => 'Pets & Animal Care'],
        209 => ['name' => 'Dog agility training (handler running)',               'met' => 5.5,  'category' => 'Pets & Animal Care'],
        210 => ['name' => 'Grooming / bathing large dog',                        'met' => 3.0,  'category' => 'Pets & Animal Care'],
        211 => ['name' => 'Mucking stalls / shoveling manure',                   'met' => 5.5,  'category' => 'Pets & Animal Care'],
        212 => ['name' => 'Leading / lunging horse',                             'met' => 4.5,  'category' => 'Pets & Animal Care'],

        // CHILDREN'S PLAY & FAMILY ACTIVITIES
        213 => ['name' => 'Playing tag / chase with children',                   'met' => 5.5,  'category' => "Children's Play & Family"],
        214 => ['name' => 'Jumping on trampoline with kids',                     'met' => 4.5,  'category' => "Children's Play & Family"],
        215 => ['name' => 'Playing at playground (climbing, swinging)',          'met' => 3.8,  'category' => "Children's Play & Family"],
        216 => ['name' => 'Tossing / kicking ball with kids',                    'met' => 3.5,  'category' => "Children's Play & Family"],
        217 => ['name' => 'Swimming with children (recreational)',               'met' => 4.0,  'category' => "Children's Play & Family"],

        // SNOW & BEACH ACTIVITIES (non-sport)
        218 => ['name' => 'Building snowman / playing in snow',                  'met' => 4.0,  'category' => 'Snow & Beach Play'],
        219 => ['name' => 'Sledding (carrying sled uphill)',                     'met' => 6.5,  'category' => 'Snow & Beach Play'],
        220 => ['name' => 'Digging in sand / beach play (active)',               'met' => 3.5,  'category' => 'Snow & Beach Play'],
        221 => ['name' => 'Beach volleyball (casual)',                           'met' => 5.0,  'category' => 'Snow & Beach Play'],
        222 => ['name' => 'Snorkeling',                                          'met' => 5.0,  'category' => 'Snow & Beach Play'],
        223 => ['name' => 'Scuba diving',                                        'met' => 7.0,  'category' => 'Snow & Beach Play'],

        // PRECISION & SKILL SPORTS
        224 => ['name' => 'Darts (standing)',                                    'met' => 2.5,  'category' => 'Precision & Skill Sports'],
        225 => ['name' => 'Billiards / pool',                                    'met' => 2.5,  'category' => 'Precision & Skill Sports'],
        226 => ['name' => 'Shooting sports (rifle / pistol, standing)',          'met' => 2.5,  'category' => 'Precision & Skill Sports'],
        227 => ['name' => 'Fly fishing (wading)',                                'met' => 4.0,  'category' => 'Precision & Skill Sports'],
        228 => ['name' => 'Fishing (boat, sitting)',                             'met' => 2.0,  'category' => 'Precision & Skill Sports'],
        229 => ['name' => 'Fishing (shore casting, standing)',                   'met' => 2.5,  'category' => 'Precision & Skill Sports'],

        // BACKYARD & CASUAL GAMES
        230 => ['name' => 'Cornhole / bean bag toss',                            'met' => 2.5,  'category' => 'Backyard & Casual Games'],
        231 => ['name' => 'Horseshoes',                                          'met' => 2.5,  'category' => 'Backyard & Casual Games'],
        232 => ['name' => 'Ladder toss / lawn games (light)',                    'met' => 2.5,  'category' => 'Backyard & Casual Games'],
        233 => ['name' => 'Badminton (backyard, casual)',                        'met' => 3.5,  'category' => 'Backyard & Casual Games'],
        234 => ['name' => 'Croquet',                                             'met' => 2.8,  'category' => 'Backyard & Casual Games'],
        235 => ['name' => 'Capture the flag / outdoor group games',              'met' => 5.0,  'category' => 'Backyard & Casual Games'],

        // OCCUPATIONAL & TRADE PHYSICAL WORK
        236 => ['name' => 'Masonry / bricklaying',                               'met' => 5.5,  'category' => 'Occupational Physical Work'],
        237 => ['name' => 'Carpentry (framing, sawing)',                         'met' => 5.0,  'category' => 'Occupational Physical Work'],
        238 => ['name' => 'Plumbing / electrical work (crawling, kneeling)',     'met' => 4.5,  'category' => 'Occupational Physical Work'],
        239 => ['name' => 'Warehouse work (picking, packing, walking)',          'met' => 4.0,  'category' => 'Occupational Physical Work'],
        240 => ['name' => 'Mail / package delivery (walking with load)',         'met' => 5.0,  'category' => 'Occupational Physical Work'],
        241 => ['name' => 'Firefighting (active)',                               'met' => 12.0, 'category' => 'Occupational Physical Work'],
        242 => ['name' => 'Logging / forestry work',                             'met' => 8.0,  'category' => 'Occupational Physical Work'],

        // MOVEMENT PRACTICES & MINDFUL FITNESS
        243 => ['name' => 'Feldenkrais / somatic movement',                      'met' => 2.0,  'category' => 'Movement Practices'],
        244 => ['name' => 'Foam rolling / self-myofascial release',              'met' => 1.8,  'category' => 'Movement Practices'],
        245 => ['name' => 'Balance / proprioception training',                   'met' => 2.5,  'category' => 'Movement Practices'],
        246 => ['name' => 'Breathing exercises / pranayama',                     'met' => 1.5,  'category' => 'Movement Practices'],
        247 => ['name' => 'Active recovery walk (very slow)',                    'met' => 2.0,  'category' => 'Movement Practices'],

        // WALKING (extended)
        248 => ['name' => 'Racewalking (competitive, 7 mph)',                 'met' => 9.0,  'category' => 'Walking'],
        249 => ['name' => 'Walking, on soft sand',                            'met' => 4.0,  'category' => 'Walking'],
        250 => ['name' => 'Walking, pushing wheelchair',                      'met' => 4.0,  'category' => 'Walking'],
        251 => ['name' => 'Walking, descending stairs',                       'met' => 3.5,  'category' => 'Walking'],

        // RUNNING (extended)
        252 => ['name' => 'Running, stairs (up)',                             'met' => 15.0, 'category' => 'Running'],
        253 => ['name' => 'Running, on soft sand',                            'met' => 10.0, 'category' => 'Running'],
        254 => ['name' => 'Running, pushing stroller',                        'met' => 7.0,  'category' => 'Running'],
        255 => ['name' => 'Treadmill running (0% incline, 6 mph)',            'met' => 9.8,  'category' => 'Running'],

        // CYCLING (extended)
        256 => ['name' => 'Cycling, recumbent (moderate)',                    'met' => 5.0,  'category' => 'Cycling'],
        257 => ['name' => 'Cycling, handbike (vigorous)',                     'met' => 7.0,  'category' => 'Cycling'],
        258 => ['name' => 'Cycling, unicycle',                                'met' => 5.5,  'category' => 'Cycling'],
        259 => ['name' => 'Indoor cycling (low intensity, 50W)',              'met' => 3.5,  'category' => 'Cycling'],

        // SWIMMING (extended)
        260 => ['name' => 'Swimming, treading water (moderate)',              'met' => 4.0,  'category' => 'Swimming'],
        261 => ['name' => 'Deep water running (with buoyancy vest)',          'met' => 8.0,  'category' => 'Swimming'],
        262 => ['name' => 'Swimming, with fins (moderate)',                   'met' => 5.0,  'category' => 'Swimming'],
        263 => ['name' => 'Underwater hockey / octopush',                     'met' => 10.0, 'category' => 'Swimming'],

        //️ GYM MACHINES (extended)
        264 => ['name' => 'Arc / cross-trainer (moderate)',                   'met' => 5.5,  'category' => 'Gym Machines'],
        265 => ['name' => 'Arc / cross-trainer (vigorous)',                   'met' => 8.0,  'category' => 'Gym Machines'],
        266 => ['name' => 'Upper body ergometer (moderate)',                  'met' => 4.5,  'category' => 'Gym Machines'],
        267 => ['name' => 'Adaptive motion trainer (AMT)',                    'met' => 6.5,  'category' => 'Gym Machines'],

        // STRENGTH & RESISTANCE (extended)
        268 => ['name' => 'Calisthenics (advanced, muscle‑ups, levers)',      'met' => 7.0,  'category' => 'Strength & Resistance'],
        269 => ['name' => 'Strongman training (yoke carry, log press)',       'met' => 9.5,  'category' => 'Strength & Resistance'],
        270 => ['name' => 'Plyo push‑ups / clapping push‑ups',                'met' => 8.5,  'category' => 'Strength & Resistance'],
        271 => ['name' => 'Isometric holds (wall sit, plank)',                'met' => 3.5,  'category' => 'Strength & Resistance'],
        272 => ['name' => 'Resistance band training (vigorous)',              'met' => 5.5,  'category' => 'Strength & Resistance'],

        // JUMP ROPE & PLYOMETRICS (extended)
        273 => ['name' => 'Jumping jacks (moderate)',                         'met' => 5.0,  'category' => 'Jump Rope & Plyometrics'],
        274 => ['name' => 'Tuck jumps (max effort)',                          'met' => 10.5, 'category' => 'Jump Rope & Plyometrics'],
        275 => ['name' => 'Broad jumps / standing long jumps',                'met' => 8.5,  'category' => 'Jump Rope & Plyometrics'],
        276 => ['name' => 'Lateral bounds / skater jumps',                    'met' => 9.0,  'category' => 'Jump Rope & Plyometrics'],
        277 => ['name' => 'Depth jumps (from box)',                           'met' => 10.0, 'category' => 'Jump Rope & Plyometrics'],

        // YOGA, PILATES & FLEXIBILITY (extended)
        278 => ['name' => 'Yoga, chair / senior yoga',                        'met' => 2.0,  'category' => 'Yoga, Pilates & Flexibility'],
        279 => ['name' => 'Yoga sculpt (with light weights)',                 'met' => 4.5,  'category' => 'Yoga, Pilates & Flexibility'],
        280 => ['name' => 'Aerial yoga (hammock)',                            'met' => 3.5,  'category' => 'Yoga, Pilates & Flexibility'],
        281 => ['name' => 'Pilates mat class (beginner)',                     'met' => 2.8,  'category' => 'Yoga, Pilates & Flexibility'],
        282 => ['name' => 'Pilates with props (magic circle / ball)',         'met' => 3.5,  'category' => 'Yoga, Pilates & Flexibility'],

        // AEROBICS & DANCE (extended)
        283 => ['name' => 'Cardio barre / barre fitness',                     'met' => 5.0,  'category' => 'Aerobics & Dance'],
        284 => ['name' => 'Latin dance (salsa, merengue)',                    'met' => 5.5,  'category' => 'Aerobics & Dance'],
        285 => ['name' => 'Belly dancing',                                    'met' => 4.0,  'category' => 'Aerobics & Dance'],
        286 => ['name' => 'Break dancing / b‑boying',                         'met' => 8.0,  'category' => 'Aerobics & Dance'],
        287 => ['name' => 'Line / country dancing',                           'met' => 4.5,  'category' => 'Aerobics & Dance'],
        288 => ['name' => 'Flamenco dancing',                                 'met' => 5.0,  'category' => 'Aerobics & Dance'],
        289 => ['name' => 'Aqua Zumba / water dance',                         'met' => 4.5,  'category' => 'Aerobics & Dance'],

        //️ OUTDOOR & ADVENTURE (extended)
        290 => ['name' => 'Canyoneering / canyoning',                         'met' => 7.0,  'category' => 'Outdoor & Adventure'],
        291 => ['name' => 'Caving / spelunking',                              'met' => 6.0,  'category' => 'Outdoor & Adventure'],
        292 => ['name' => 'Orienteering',                                     'met' => 9.0,  'category' => 'Outdoor & Adventure'],
        293 => ['name' => 'Mountaineering (mixed climb, no pack)',            'met' => 9.5,  'category' => 'Outdoor & Adventure'],
        294 => ['name' => 'Via ferrata',                                      'met' => 7.5,  'category' => 'Outdoor & Adventure'],
        295 => ['name' => 'Canoe portaging (carrying canoe over land)',       'met' => 8.0,  'category' => 'Outdoor & Adventure'],
        296 => ['name' => 'Whitewater rafting (paddling hard)',               'met' => 6.5,  'category' => 'Outdoor & Adventure'],
        297 => ['name' => 'Sailing (racing, active crew)',                    'met' => 5.5,  'category' => 'Outdoor & Adventure'],

        //️ WINTER SPORTS (extended)
        298 => ['name' => 'Ice climbing',                                     'met' => 10.0, 'category' => 'Winter Sports'],
        299 => ['name' => 'Ski touring / backcountry (uphill, light pack)',   'met' => 8.5,  'category' => 'Winter Sports'],
        300 => ['name' => 'Telemark skiing',                                  'met' => 7.5,  'category' => 'Winter Sports'],
        301 => ['name' => 'Luge / skeleton (start run)',                      'met' => 12.0, 'category' => 'Winter Sports'],
        302 => ['name' => 'Bobsled / bobsleigh (push start)',                 'met' => 15.0, 'category' => 'Winter Sports'],
        303 => ['name' => 'Snowmobiling (driving aggressively)',              'met' => 4.5,  'category' => 'Winter Sports'],

        // WHEELED SPORTS (extended)
        304 => ['name' => 'Roller skiing (moderate)',                         'met' => 9.0,  'category' => 'Wheeled Sports'],
        305 => ['name' => 'Skateboarding (vert / park, aggressive)',          'met' => 8.0,  'category' => 'Wheeled Sports'],
        306 => ['name' => 'Inline speed skating (racing)',                    'met' => 13.0, 'category' => 'Wheeled Sports'],
        307 => ['name' => 'Kick scooter (vigorous, fast)',                    'met' => 6.0,  'category' => 'Wheeled Sports'],

        // TEAM & COURT SPORTS (extended)
        308 => ['name' => 'Futsal (indoor soccer)',                           'met' => 8.0,  'category' => 'Team & Court Sports'],
        309 => ['name' => 'Netball',                                          'met' => 6.5,  'category' => 'Team & Court Sports'],
        310 => ['name' => 'Floorball / innebandy',                            'met' => 7.5,  'category' => 'Team & Court Sports'],
        311 => ['name' => 'Sepak takraw / kick volleyball',                   'met' => 8.0,  'category' => 'Team & Court Sports'],
        312 => ['name' => 'Cricket (fielding)',                               'met' => 5.5,  'category' => 'Team & Court Sports'],

        // RACKET SPORTS (extended)
        313 => ['name' => 'Platform tennis (outdoor)',                        'met' => 7.0,  'category' => 'Racket Sports'],
        314 => ['name' => 'Table tennis (competitive)',                       'met' => 6.0,  'category' => 'Racket Sports'],
        315 => ['name' => 'Paddle tennis',                                    'met' => 6.5,  'category' => 'Racket Sports'],
        316 => ['name' => 'Speed‑minton (fast badminton)',                    'met' => 7.5,  'category' => 'Racket Sports'],

        // COMBAT SPORTS (extended)
        317 => ['name' => 'Krav Maga (drills)',                               'met' => 9.0,  'category' => 'Combat Sports'],
        318 => ['name' => 'Capoeira (moderate)',                              'met' => 7.0,  'category' => 'Combat Sports'],
        319 => ['name' => 'Kendo (bamboo sword practice)',                    'met' => 8.0,  'category' => 'Combat Sports'],
        320 => ['name' => 'Kickboxing (heavy bag)',                           'met' => 8.0,  'category' => 'Combat Sports'],

        // GOLF & LOW‑INTENSITY SPORTS (extended)
        321 => ['name' => 'Miniature golf',                                   'met' => 2.5,  'category' => 'Golf & Low-Intensity Sports'],
        322 => ['name' => 'Disc golf (casual, minimal walking)',              'met' => 3.0,  'category' => 'Golf & Low-Intensity Sports'],
        323 => ['name' => 'Horseshoes (competitive, constant throwing)',      'met' => 3.0,  'category' => 'Golf & Low-Intensity Sports'],

        // MANUAL LABOR & DAILY LIFE (extended)
        324 => ['name' => 'Washing car by hand (vigorous)',                   'met' => 4.5,  'category' => 'Manual Labor & Daily Life'],
        325 => ['name' => 'Scrubbing floors on hands and knees',              'met' => 4.8,  'category' => 'Manual Labor & Daily Life'],
        326 => ['name' => 'Cleaning gutters / eavestroughs',                  'met' => 4.0,  'category' => 'Manual Labor & Daily Life'],
        327 => ['name' => 'Moving furniture / boxes (heavy, up stairs)',      'met' => 6.5,  'category' => 'Manual Labor & Daily Life'],
        328 => ['name' => 'Welding (standing)',                               'met' => 3.0,  'category' => 'Manual Labor & Daily Life'],
        329 => ['name' => 'Automotive repair (standing, moderate effort)',    'met' => 3.5,  'category' => 'Manual Labor & Daily Life'],
        330 => ['name' => 'Mining (general)',                                 'met' => 6.5,  'category' => 'Manual Labor & Daily Life'],
        331 => ['name' => 'Loading / unloading truck (moderate)',             'met' => 5.5,  'category' => 'Manual Labor & Daily Life'],

        // GYMNASTICS & PERFORMANCE (extended)
        332 => ['name' => 'Parkour / free running',                           'met' => 9.0,  'category' => 'Gymnastics & Performance'],
        333 => ['name' => 'Acrobatics (partner balancing)',                   'met' => 5.5,  'category' => 'Gymnastics & Performance'],
        334 => ['name' => 'Hand balancing / handstands',                      'met' => 6.0,  'category' => 'Gymnastics & Performance'],
        335 => ['name' => 'Pole vault training',                              'met' => 10.0, 'category' => 'Gymnastics & Performance'],

        // PETS & ANIMAL CARE (extended)
        336 => ['name' => 'Dog mushing / skijoring (moderate)',               'met' => 8.5,  'category' => 'Pets & Animal Care'],
        337 => ['name' => 'Herding / working stock dogs',                     'met' => 5.5,  'category' => 'Pets & Animal Care'],
        338 => ['name' => 'Cleaning horse stalls (heavy)',                    'met' => 6.0,  'category' => 'Pets & Animal Care'],

        // CHILDREN’S PLAY & FAMILY (extended)
        339 => ['name' => 'Wrestling / roughhousing with kids',               'met' => 5.0,  'category' => "Children's Play & Family"],
        340 => ['name' => 'Giving piggyback rides',                           'met' => 5.5,  'category' => "Children's Play & Family"],
        341 => ['name' => 'Playing hide and seek (running)',                  'met' => 5.0,  'category' => "Children's Play & Family"],

        //️ SNOW & BEACH PLAY (extended)
        342 => ['name' => 'Sand castle building (intense digging)',           'met' => 4.5,  'category' => 'Snow & Beach Play'],
        343 => ['name' => 'Body surfing / bodysurfing',                       'met' => 5.0,  'category' => 'Snow & Beach Play'],
        344 => ['name' => 'Spearfishing (active)',                            'met' => 6.5,  'category' => 'Snow & Beach Play'],

        // PRECISION & SKILL SPORTS (extended)
        345 => ['name' => 'Archery (3D / field course, walking)',             'met' => 4.0,  'category' => 'Precision & Skill Sports'],
        346 => ['name' => 'Axe throwing (recreational)',                      'met' => 3.5,  'category' => 'Precision & Skill Sports'],

        // BACKYARD & CASUAL GAMES (extended)
        347 => ['name' => 'KanJam / disc toss games',                         'met' => 3.0,  'category' => 'Backyard & Casual Games'],
        348 => ['name' => 'Spikeball (roundnet)',                             'met' => 6.0,  'category' => 'Backyard & Casual Games'],
        349 => ['name' => 'Bocce (walking only)',                             'met' => 2.5,  'category' => 'Backyard & Casual Games'],

        // OCCUPATIONAL PHYSICAL WORK (extended)
        350 => ['name' => 'Police / tactical training (obstacle course)',     'met' => 10.0, 'category' => 'Occupational Physical Work'],
        351 => ['name' => 'Military boot camp (general)',                     'met' => 9.0,  'category' => 'Occupational Physical Work'],
        352 => ['name' => 'Steel mill work (heavy)',                          'met' => 8.0,  'category' => 'Occupational Physical Work'],
        353 => ['name' => 'Nursing / patient handling (lifting)',             'met' => 4.5,  'category' => 'Occupational Physical Work'],

        // WATER SPORTS (NEW CATEGORY)
        354 => ['name' => 'Waterskiing / wakeboarding',                       'met' => 6.0,  'category' => 'Water Sports'],
        355 => ['name' => 'Barefoot waterskiing',                             'met' => 8.0,  'category' => 'Water Sports'],
        356 => ['name' => 'Jet skiing (aggressive)',                          'met' => 5.0,  'category' => 'Water Sports'],
        357 => ['name' => 'Synchronized swimming (competition)',              'met' => 8.5,  'category' => 'Water Sports'],
        358 => ['name' => 'Free diving (active descent/ascent)',              'met' => 9.0,  'category' => 'Water Sports'],

        //️ AIR SPORTS (NEW CATEGORY)
        359 => ['name' => 'Paragliding (carrying gear uphill)',               'met' => 6.0,  'category' => 'Air Sports'],
        360 => ['name' => 'Skydiving (packing chute, active)',                'met' => 4.0,  'category' => 'Air Sports'],
        361 => ['name' => 'Hang gliding (carrying and launching)',            'met' => 5.0,  'category' => 'Air Sports'],

        // HOME IMPROVEMENT (NEW CATEGORY)
        362 => ['name' => 'Demolition / sledgehammer work',                   'met' => 7.0,  'category' => 'Home Improvement'],
        363 => ['name' => 'Tiling / grouting floors',                         'met' => 4.0,  'category' => 'Home Improvement'],
        364 => ['name' => 'Wallpapering / stripping wallpaper',               'met' => 3.5,  'category' => 'Home Improvement'],
        365 => ['name' => 'Assembling furniture (moderate effort)',           'met' => 3.5,  'category' => 'Home Improvement'],

        // MOVEMENT PRACTICES (extended)
        366 => ['name' => 'Alexander technique',                              'met' => 1.8,  'category' => 'Movement Practices'],
        367 => ['name' => '5 Rhythms / ecstatic dance (moderate)',            'met' => 4.5,  'category' => 'Movement Practices'],
        368 => ['name' => 'NIA technique',                                    'met' => 4.0,  'category' => 'Movement Practices'],

        // PERSONAL ACTIVITIES (NEW CATEGORY – for completeness)
        369 => ['name' => 'Intimate activity (light effort)',                 'met' => 1.5,  'category' => 'Personal Activities'],
        370 => ['name' => 'Intimate activity (moderate effort)',              'met' => 2.5,  'category' => 'Personal Activities'],
        371 => ['name' => 'Intimate activity (vigorous effort)',              'met' => 3.5,  'category' => 'Personal Activities'],
    ];

    public function mainAction() {
        echo "Exercise Data Importer" . PHP_EOL;
        echo "------------------" . PHP_EOL;
        echo "Usage: php bin/console exercise_data activities" . PHP_EOL;
    }

        /**
         * Action to import foods from a gzipped JSON file.
         * CLI args map to the $params array automatically.
         */
        public function activitiesAction() {
            echo "Exercise import initiated..." . PHP_EOL;

            $added = 0;
            $updated = 0;

            foreach ($this->exercises as $id => $exercise) {
                // 1. Look for an existing record by the array key (ID)
                $activity = \App\Models\Activities::findFirst($id);

                if ($activity) {
                    // EXISTS: Update the existing record
                    $activity->assign([
                        'name'      => $exercise['name'],
                        'category'  => $exercise['category'],
                        'met_value' => $exercise['met'],
                    ]);

                    // Explicitly call update()
                    if ($activity->update()) {
                        $updated++;
                    }
                } else {
                    // DOES NOT EXIST: Create a new record and force the specific ID
                    $activity = new \App\Models\Activities();
                    $activity->assign([
                        'id'        => $id,
                        'name'      => $exercise['name'],
                        'category'  => $exercise['category'],
                        'met_value' => $exercise['met'],
                    ]);

                    // Explicitly call create()
                    if ($activity->create()) {
                        $added++;
                    }
                }
            }

            echo "Done! Added: {$added}, Updated: {$updated}." . PHP_EOL;
        }
    }