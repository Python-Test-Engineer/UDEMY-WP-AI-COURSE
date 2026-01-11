# Semantic Search with Vectors: Technical Summary

## Overview

Semantic search uses vector embeddings and similarity measures to find content based on meaning rather than exact keyword matches. Unlike traditional full-text search (which matches words), semantic search understands context, synonyms, and conceptual relationships between queries and documents.

Cat, kitten, feline are semantically similar.

Computer, Internet, Software are in the same area.

If we ask for an animal that is like a kitty, the embedding for kitty will be closer to the 'Cat, kitten, feline' group than the tech group.

In an office, people in same teams tend to be in same physical location so searching for a dev engineer would be more effective searching that area of the office rather than the marketing office.

## Core Concepts

### What Are Vector Embeddings?

Vector embeddings are numerical representations of text in high-dimensional space (typically 384, 768, 1536, or more dimensions). Text with similar meanings are positioned close together in this space.

**Example:**
```
"cat"     → [0.2, -0.5, 0.8, ..., 0.3]  (768 dimensions)
"kitten"  → [0.21, -0.48, 0.79, ..., 0.31]  (close to "cat")
"dog"     → [0.19, -0.52, 0.75, ..., 0.28]  (somewhat close)
"car"     → [-0.6, 0.3, -0.2, ..., -0.8]  (far from "cat")
```

### How Embeddings Capture Meaning

Machine learning models (like BERT, Sentence Transformers, OpenAI's text-embedding models) are trained on massive text corpora to learn:

- **Semantic relationships**: "happy" ≈ "joyful" ≈ "delighted"
- **Contextual meaning**: "bank" (financial) vs "bank" (river)
- **Conceptual similarity**: "Python programming" ≈ "coding in Python"
- **Multi-word phrases**: Entire sentences/paragraphs as single vectors

## Cosine Similarity Explained

### Mathematical Foundation

Cosine similarity measures the cosine of the angle between two vectors in multi-dimensional space. It ranges from -1 to 1:

- **1**: Vectors point in the same direction (identical meaning)
- **0**: Vectors are orthogonal (unrelated)
- **-1**: Vectors point in opposite directions (opposite meaning)

### Formula

For two vectors **A** and **B**:

```
cosine_similarity(A, B) = (A · B) / (||A|| × ||B||)

Where:
- A · B = dot product (sum of element-wise multiplication)
- ||A|| = magnitude/length of vector A = √(a₁² + a₂² + ... + aₙ²)
- ||B|| = magnitude/length of vector B = √(b₁² + b₂² + ... + bₙ²)
```

### Step-by-Step Calculation

**Example with simple 3D vectors:**

```
Vector A = [1, 2, 3]
Vector B = [2, 3, 4]

Step 1: Calculate dot product
A · B = (1×2) + (2×3) + (3×4) = 2 + 6 + 12 = 20

Step 2: Calculate magnitudes
||A|| = √(1² + 2² + 3²) = √(1 + 4 + 9) = √14 ≈ 3.742
||B|| = √(2² + 3² + 4²) = √(4 + 9 + 16) = √29 ≈ 5.385

Step 3: Calculate cosine similarity
cosine_similarity = 20 / (3.742 × 5.385) ≈ 20 / 20.15 ≈ 0.993

Result: 0.993 (very similar)
```

### Why Cosine Similarity Works

1. **Direction over magnitude**: Focuses on the angle between vectors, not their length
2. **Scale invariant**: "I love cats" and "I really really love cats" have similar direction
3. **Normalized comparison**: Results always between -1 and 1, making comparisons consistent
4. **Efficient computation**: Can be optimized with vector databases and indexing

### Visual Intuition

```
        Vector B
           ↗
          /
         /  θ (small angle = high similarity)
        /
       /
Vector A →

cosine(θ) ≈ 1  →  Very similar
cosine(θ) ≈ 0  →  Unrelated
cosine(θ) ≈ -1 →  Opposite
```

## Semantic Search Workflow

### Complete Pipeline

```
1. Indexing Phase:
   Documents → Embedding Model → Vector Database
   
2. Query Phase:
   User Query → Embedding Model → Query Vector
   
3. Search Phase:
   Query Vector → Similarity Search → Ranked Results
```


## Implementation Examples

### Pure PHP Implementation

```php
<?php

/**
 * Calculate cosine similarity between two vectors
 */
function cosine_similarity(array $vec_a, array $vec_b): float {
    if (count($vec_a) !== count($vec_b)) {
        throw new InvalidArgumentException('Vectors must have the same length');
    }
    
    // Calculate dot product
    $dot_product = 0;
    for ($i = 0; $i < count($vec_a); $i++) {
        $dot_product += $vec_a[$i] * $vec_b[$i];
    }
    
    // Calculate magnitudes
    $magnitude_a = sqrt(array_sum(array_map(fn($x) => $x ** 2, $vec_a)));
    $magnitude_b = sqrt(array_sum(array_map(fn($x) => $x ** 2, $vec_b)));
    
    // Avoid division by zero
    if ($magnitude_a == 0 || $magnitude_b == 0) {
        return 0.0;
    }
    
    return $dot_product / ($magnitude_a * $magnitude_b);
}

// Example usage
$vector1 = [1, 2, 3, 4];
$vector2 = [2, 3, 4, 5];
$similarity = cosine_similarity($vector1, $vector2);
echo "Similarity: " . round($similarity, 4); // 0.9915
```

### Semantic Search Class

```php
<?php

class SemanticSearch {
    private array $documents = [];
    private array $embeddings = [];
    private string $apiKey;
    private string $model = 'text-embedding-3-small'; // OpenAI model
    
    public function __construct(string $apiKey) {
        $this->apiKey = $apiKey;
    }
    
    /**
     * Add documents to the search index
     */
    public function indexDocuments(array $documents): void {
        $this->documents = $documents;
        $this->embeddings = [];
        
        foreach ($documents as $doc) {
            $this->embeddings[] = $this->getEmbedding($doc);
        }
    }
    
    /**
     * Get embedding from OpenAI API
     */
    private function getEmbedding(string $text): array {
        $ch = curl_init('https://api.openai.com/v1/embeddings');
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $this->model,
                'input' => $text
            ])
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        return $result['data'][0]['embedding'] ?? [];
    }
    
    /**
     * Search documents by query
     */
    public function search(string $query, int $topK = 5): array {
        $queryEmbedding = $this->getEmbedding($query);
        $results = [];
        
        foreach ($this->embeddings as $idx => $docEmbedding) {
            $similarity = $this->cosineSimilarity($queryEmbedding, $docEmbedding);
            $results[] = [
                'document' => $this->documents[$idx],
                'score' => $similarity,
                'index' => $idx
            ];
        }
        
        // Sort by similarity score (descending)
        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);
        
        // Return top K results
        return array_slice($results, 0, $topK);
    }
    
    /**
     * Calculate cosine similarity
     */
    private function cosineSimilarity(array $vec_a, array $vec_b): float {
        $dot_product = 0;
        $magnitude_a = 0;
        $magnitude_b = 0;
        
        for ($i = 0; $i < count($vec_a); $i++) {
            $dot_product += $vec_a[$i] * $vec_b[$i];
            $magnitude_a += $vec_a[$i] ** 2;
            $magnitude_b += $vec_b[$i] ** 2;
        }
        
        $magnitude_a = sqrt($magnitude_a);
        $magnitude_b = sqrt($magnitude_b);
        
        if ($magnitude_a == 0 || $magnitude_b == 0) {
            return 0.0;
        }
        
        return $dot_product / ($magnitude_a * $magnitude_b);
    }
}

// Usage example
$search = new SemanticSearch('your-openai-api-key');

$documents = [
    'Python is a high-level programming language',
    'Machine learning uses algorithms to learn from data',
    'The cat sat on the mat',
    'Deep learning is a subset of machine learning'
];

$search->indexDocuments($documents);

$results = $search->search('What is ML?', 3);

foreach ($results as $result) {
    echo sprintf(
        "Score: %.4f - %s\n",
        $result['score'],
        $result['document']
    );
}
```

### Local Embeddings with Sentence Transformers (Python Bridge)

For production use without API calls, you can use a Python microservice:

```php
<?php

class LocalSemanticSearch {
    private string $pythonServiceUrl;
    
    public function __construct(string $serviceUrl = 'http://localhost:5000') {
        $this->pythonServiceUrl = $serviceUrl;
    }
    
    /**
     * Get embedding from local Python service
     */
    private function getEmbedding(string $text): array {
        $ch = curl_init($this->pythonServiceUrl . '/embed');
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode(['text' => $text])
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true)['embedding'] ?? [];
    }
    
    // Rest of the implementation similar to SemanticSearch class
}
```

**Python microservice (Flask):**

```python
from flask import Flask, request, jsonify
from sentence_transformers import SentenceTransformer

app = Flask(__name__)
model = SentenceTransformer('all-MiniLM-L6-v2')

@app.route('/embed', methods=['POST'])
def embed():
    text = request.json.get('text', '')
    embedding = model.encode([text])[0].tolist()
    return jsonify({'embedding': embedding})

if __name__ == '__main__':
    app.run(port=5000)
```

### MySQL Integration with Vector Storage

```php
<?php
#region VEC
class MySQLVectorSearch {
    private PDO $pdo;
    private SemanticSearch $search;
    
    public function __construct(PDO $pdo, string $apiKey) {
        $this->pdo = $pdo;
        $this->search = new SemanticSearch($apiKey);
    }
    
    /**
     * Create table with vector storage
     */
    public function createTable(): void {
        $sql = "
            CREATE TABLE IF NOT EXISTS document_embeddings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                document TEXT NOT NULL,
                embedding JSON NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB
        ";
        $this->pdo->exec($sql);
    }
    
    /**
     * Index a document
     */
    public function indexDocument(string $document): int {
        $embedding = $this->search->getEmbedding($document);
        
        $stmt = $this->pdo->prepare("
            INSERT INTO document_embeddings (document, embedding)
            VALUES (:document, :embedding)
        ");
        
        $stmt->execute([
            'document' => $document,
            'embedding' => json_encode($embedding)
        ]);
        
        return (int)$this->pdo->lastInsertId();
    }
    
    /**
     * Search documents
     */
    public function search(string $query, int $limit = 10): array {
        // Get query embedding
        $queryEmbedding = $this->search->getEmbedding($query);
        
        // Fetch all documents (for small datasets)
        // For large datasets, use specialized vector databases
        $stmt = $this->pdo->query("
            SELECT id, document, embedding
            FROM document_embeddings
        ");
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $docEmbedding = json_decode($row['embedding'], true);
            $similarity = $this->cosineSimilarity($queryEmbedding, $docEmbedding);
            
            $results[] = [
                'id' => $row['id'],
                'document' => $row['document'],
                'score' => $similarity
            ];
        }
        
        // Sort by similarity
        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);
        
        return array_slice($results, 0, $limit);
    }
    
    private function cosineSimilarity(array $a, array $b): float {
        $dot = 0;
        $mag_a = 0;
        $mag_b = 0;
        
        for ($i = 0; $i < count($a); $i++) {
            $dot += $a[$i] * $b[$i];
            $mag_a += $a[$i] ** 2;
            $mag_b += $b[$i] ** 2;
        }
        
        return $dot / (sqrt($mag_a) * sqrt($mag_b));
    }
}

// Usage
$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'user', 'pass');
$vectorSearch = new MySQLVectorSearch($pdo, 'your-api-key');

// Create table
$vectorSearch->createTable();

// Index documents
$vectorSearch->indexDocument('Python is a programming language');
$vectorSearch->indexDocument('Machine learning is a subset of AI');

// Search
$results = $vectorSearch->search('What is ML?', 5);
print_r($results);
```

### WordPress Integration

```php
<?php

/**
 * Add semantic search to WordPress
 * Add to functions.php
 */

class WP_Semantic_Search {
    private string $apiKey;
    private string $tableName;
    
    public function __construct() {
        global $wpdb;
        $this->tableName = $wpdb->prefix . 'post_embeddings';
        $this->apiKey = get_option('openai_api_key', '');
        
        // Create table on activation
        register_activation_hook(__FILE__, [$this, 'createTable']);
        
        // Hook into post save
        add_action('save_post', [$this, 'indexPost'], 10, 2);
        
        // Add custom search endpoint
        add_action('rest_api_init', [$this, 'registerEndpoints']);
    }
    
    public function createTable(): void {
        global $wpdb;
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->tableName} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            post_id BIGINT UNSIGNED NOT NULL,
            embedding LONGTEXT NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY post_id (post_id)
        ) ENGINE=InnoDB";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function indexPost(int $postId, WP_Post $post): void {
        // Only index published posts
        if ($post->post_status !== 'publish') {
            return;
        }
        
        global $wpdb;
        
        // Combine title and content
        $text = $post->post_title . ' ' . strip_tags($post->post_content);
        
        // Get embedding
        $embedding = $this->getEmbedding($text);
        
        if (empty($embedding)) {
            return;
        }
        
        // Store in database
        $wpdb->replace(
            $this->tableName,
            [
                'post_id' => $postId,
                'embedding' => json_encode($embedding)
            ],
            ['%d', '%s']
        );
    }
    
    private function getEmbedding(string $text): array {
        $response = wp_remote_post('https://api.openai.com/v1/embeddings', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey
            ],
            'body' => json_encode([
                'model' => 'text-embedding-3-small',
                'input' => $text
            ]),
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            return [];
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body['data'][0]['embedding'] ?? [];
    }
    
    public function semanticSearch(string $query, int $limit = 10): array {
        global $wpdb;
        
        $queryEmbedding = $this->getEmbedding($query);
        
        if (empty($queryEmbedding)) {
            return [];
        }
        
        // Fetch all embeddings
        $embeddings = $wpdb->get_results("
            SELECT post_id, embedding
            FROM {$this->tableName}
        ", ARRAY_A);
        
        $results = [];
        
        foreach ($embeddings as $row) {
            $postEmbedding = json_decode($row['embedding'], true);
            $similarity = $this->cosineSimilarity($queryEmbedding, $postEmbedding);
            
            $results[] = [
                'post_id' => (int)$row['post_id'],
                'score' => $similarity
            ];
        }
        
        // Sort by score
        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);
        
        // Get post objects
        $topResults = array_slice($results, 0, $limit);
        $posts = [];
        
        foreach ($topResults as $result) {
            $post = get_post($result['post_id']);
            if ($post) {
                $posts[] = [
                    'post' => $post,
                    'score' => $result['score']
                ];
            }
        }
        
        return $posts;
    }
    
    private function cosineSimilarity(array $a, array $b): float {
        $dot = 0;
        $mag_a = 0;
        $mag_b = 0;
        
        for ($i = 0; $i < count($a); $i++) {
            $dot += $a[$i] * $b[$i];
            $mag_a += $a[$i] ** 2;
            $mag_b += $b[$i] ** 2;
        }
        
        return $dot / (sqrt($mag_a) * sqrt($mag_b));
    }
    
    public function registerEndpoints(): void {
        register_rest_route('semantic/v1', '/search', [
            'methods' => 'GET',
            'callback' => [$this, 'handleSearchRequest'],
            'permission_callback' => '__return_true'
        ]);
    }
    
    public function handleSearchRequest(WP_REST_Request $request): WP_REST_Response {
        $query = $request->get_param('q');
        $limit = $request->get_param('limit') ?? 10;
        
        $results = $this->semanticSearch($query, $limit);
        
        return new WP_REST_Response([
            'query' => $query,
            'results' => $results
        ], 200);
    }
}

// Initialize
new WP_Semantic_Search();

// Usage in templates:
// $search = new WP_Semantic_Search();
// $results = $search->semanticSearch('machine learning tutorial', 5);
```

### Optimized Batch Processing

```php
<?php

class BatchVectorProcessor {
    private int $batchSize = 100;
    
    /**
     * Process documents in batches for efficiency
     */
    public function batchIndex(array $documents): array {
        $allEmbeddings = [];
        $batches = array_chunk($documents, $this->batchSize);
        
        foreach ($batches as $batch) {
            $embeddings = $this->getBatchEmbeddings($batch);
            $allEmbeddings = array_merge($allEmbeddings, $embeddings);
            
            // Rate limiting - avoid API throttling
            usleep(100000); // 100ms delay
        }
        
        return $allEmbeddings;
    }
    
    private function getBatchEmbeddings(array $texts): array {
        // Send multiple texts in one API call
        $ch = curl_init('https://api.openai.com/v1/embeddings');
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => 'text-embedding-3-small',
                'input' => $texts // Array of texts
            ])
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        return array_map(
            fn($item) => $item['embedding'],
            $result['data'] ?? []
        );
    }
}