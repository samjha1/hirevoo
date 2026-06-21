<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Elasticsearch / OpenSearch
    |--------------------------------------------------------------------------
    |
    | Required for employer talent pool at scale (100k+ candidates). SQL LIKE
    | fallback is kept for local dev only.
    |
    */

    'enabled' => env('ELASTICSEARCH_ENABLED', false),

    /** Allow OpenSearch / AWS OpenSearch (elasticsearch-php 8.x product check). */
    'allow_opensearch' => filter_var(env('ELASTICSEARCH_ALLOW_OPENSEARCH', true), FILTER_VALIDATE_BOOLEAN),

    'hosts' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ELASTICSEARCH_HOSTS', 'http://127.0.0.1:9200'))
    ))),

    'index' => env('ELASTICSEARCH_JOB_INDEX', 'hirevo_job_openings'),

    'search_limit' => (int) env('ELASTICSEARCH_SEARCH_LIMIT', 500),

    'talent_pool_index' => env('ELASTICSEARCH_TALENT_POOL_INDEX', 'hirevo_talent_pool'),

    /** Max offset+size for paginated talent pool search (raise index.max_result_window to match). */
    'talent_pool_max_result_window' => (int) env('ELASTICSEARCH_TALENT_POOL_MAX_RESULT_WINDOW', 50000),

    'talent_pool_shards' => (int) env('ELASTICSEARCH_TALENT_POOL_SHARDS', 2),

    'talent_pool_replicas' => (int) env('ELASTICSEARCH_TALENT_POOL_REPLICAS', 0),

    'talent_pool_bulk_size' => (int) env('ELASTICSEARCH_TALENT_POOL_BULK_SIZE', 500),

    'talent_pool_reindex_chunk' => (int) env('ELASTICSEARCH_TALENT_POOL_REINDEX_CHUNK', 500),

    /** Cap bool/ids clauses to stay under OpenSearch indices.query.bool.max_clause_count (default 1024). */
    'talent_pool_max_query_terms' => max(1, min(16, (int) env('ELASTICSEARCH_TALENT_POOL_MAX_QUERY_TERMS', 8))),

    'talent_pool_max_skill_terms' => max(1, min(16, (int) env('ELASTICSEARCH_TALENT_POOL_MAX_SKILL_TERMS', 8))),

    'talent_pool_max_related_terms' => max(1, min(12, (int) env('ELASTICSEARCH_TALENT_POOL_MAX_RELATED_TERMS', 6))),

    /** ids filter above this count falls back to SQL (each id ≈ one Lucene clause). */
    'talent_pool_max_ids_values' => max(64, min(4096, (int) env('ELASTICSEARCH_TALENT_POOL_MAX_IDS_VALUES', 512))),

];
