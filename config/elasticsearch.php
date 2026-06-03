<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Job openings search (Elasticsearch)
    |--------------------------------------------------------------------------
    |
    | When enabled and reachable, the job-openings keyword bar uses Elasticsearch
    | for any-word matching and fuzzy (typo-tolerant) search. Otherwise a SQL
    | fallback matches each word with LIKE.
    |
    */

    'enabled' => env('ELASTICSEARCH_ENABLED', false),

    'hosts' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ELASTICSEARCH_HOSTS', 'http://127.0.0.1:9200'))
    ))),

    'index' => env('ELASTICSEARCH_JOB_INDEX', 'hirevo_job_openings'),

    'search_limit' => (int) env('ELASTICSEARCH_SEARCH_LIMIT', 500),

    'talent_pool_index' => env('ELASTICSEARCH_TALENT_POOL_INDEX', 'hirevo_talent_pool'),

    'talent_pool_search_limit' => (int) env('ELASTICSEARCH_TALENT_POOL_SEARCH_LIMIT', 250),

];
