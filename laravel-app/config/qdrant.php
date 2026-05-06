<?php

return [
    'url'         => env('QDRANT_URL', 'http://localhost:6333'),
    'collection'  => env('QDRANT_COLLECTION', 'health_documents'),
    'vector_size' => (int) env('QDRANT_VECTOR_SIZE', 768),
];
