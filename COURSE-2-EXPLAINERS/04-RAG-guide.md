# Retrieval Augmented Generation (RAG)

## Overview

Retrieval Augmented Generation (RAG) is a technique that enhances Large Language Models (LLMs) by combining them with external knowledge retrieval. Instead of relying solely on the model's training data, RAG retrieves relevant information from external sources to provide more accurate, up-to-date, and contextually grounded responses.

## The Core Problem RAG Solves

LLMs face several limitations:
- **Knowledge cutoff**: Models only know information up to their training date
- **Hallucinations**: Models may generate plausible but incorrect information
- **Domain specificity**: Models lack specialized knowledge about proprietary or niche domains
- **Context limitations**: Models have finite context windows and can't remember everything

RAG addresses these issues by retrieving relevant information at query time and providing it as context to the LLM.

## How RAG Works

```
┌─────────────────────────────────────────────────────────────────┐
│                         RAG Pipeline                             │
└─────────────────────────────────────────────────────────────────┘

1. INDEXING PHASE (Offline)
   ┌──────────────┐      ┌──────────────┐      ┌──────────────┐
   │  Documents   │─────▶│   Chunking   │─────▶│  Embedding   │
   │   (Posts,    │      │              │      │    Model     │
   │   Pages)     │      │              │      │              │
   └──────────────┘      └──────────────┘      └──────┬───────┘
                                                       │
                                                       ▼
                                              ┌─────────────────┐
                                              │ Vector Database │
                                              │    (Storage)    │
                                              └─────────────────┘

2. RETRIEVAL PHASE (Query Time)
   ┌──────────────┐      ┌──────────────┐      ┌──────────────┐
   │ User Query   │─────▶│  Embedding   │─────▶│   Similarity │
   │              │      │    Model     │      │    Search    │
   └──────────────┘      └──────────────┘      └──────┬───────┘
                                                       │
                                                       ▼
                                              ┌─────────────────┐
                                              │ Vector Database │
                                              │   (Retrieval)   │
                                              └──────┬──────────┘
                                                     │
                                                     ▼
                                              ┌─────────────────┐
                                              │Top K Documents  │
                                              └──────┬──────────┘

3. GENERATION PHASE
                                                     │
                                                     ▼
   ┌──────────────┐      ┌──────────────────────────────────────┐
   │ User Query   │─────▶│          Prompt Template             │
   └──────────────┘      │  Context: [Retrieved Documents]      │
                         │  Question: [User Query]              │
                         └──────────────────┬───────────────────┘
                                            │
                                            ▼
                                   ┌─────────────────┐
                                   │  LLM Generation │
                                   └────────┬────────┘
                                            │
                                            ▼
                                   ┌─────────────────┐
                                   │  Final Answer   │
                                   └─────────────────┘
```

## Key Components

### 1. Document Processing & Chunking

Documents are split into smaller chunks to fit within embedding model limits and improve retrieval precision.

**Common chunking strategies:**
- **Fixed-size chunking**: Split by character/token count (e.g., 512 tokens)
- **Sentence-based**: Split at sentence boundaries
- **Semantic chunking**: Split based on topic changes
- **Recursive chunking**: Hierarchical splitting with overlap

**Example (WordPress/PHP):**
```php
<?php
/**
 * Chunk text into smaller pieces for embedding
 *
 * @param string $text The text to chunk
 * @param int $chunk_size Maximum characters per chunk
 * @param int $overlap Characters to overlap between chunks
 * @return array Array of text chunks
 */
function chunk_text($text, $chunk_size = 512, $overlap = 50) {
    $chunks = [];
    $start = 0;
    $text_length = mb_strlen($text);
    
    while ($start < $text_length) {
        $end = min($start + $chunk_size, $text_length);
        $chunks[] = mb_substr($text, $start, $chunk_size);
        $start = $end - $overlap;
    }
    
    return $chunks;
}

/**
 * Process WordPress posts for RAG indexing
 */
function process_posts_for_rag() {
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1
    );
    
    $posts = get_posts($args);
    $chunks_data = [];
    
    foreach ($posts as $post) {
        $content = $post->post_content;
        $content = wp_strip_all_tags($content);
        
        $chunks = chunk_text($content);
        
        foreach ($chunks as $index => $chunk) {
            $chunks_data[] = [
                'post_id' => $post->ID,
                'chunk_index' => $index,
                'content' => $chunk,
                'title' => $post->post_title,
                'url' => get_permalink($post->ID)
            ];
        }
    }
    
    return $chunks_data;
}
?>
```

### 2. Embedding Model

Converts text into dense vector representations that capture semantic meaning.

**Popular embedding models:**
- OpenAI `text-embedding-3-small` / `text-embedding-3-large`
- Sentence Transformers (e.g., `all-MiniLM-L6-v2`)
- Cohere embeddings
- Google's Universal Sentence Encoder

**Key properties:**
- Dimension size (e.g., 384, 768, 1536)
- Semantic similarity preserved in vector space
- Same model must be used for indexing and querying

**Example (WordPress/PHP with OpenAI):**
```php
<?php
/**
 * Generate embeddings using OpenAI API
 *
 * @param string $text Text to embed
 * @return array Vector embedding
 */
function generate_embedding($text) {
    $api_key = get_option('openai_api_key');
    
    $response = wp_remote_post('https://api.openai.com/v1/embeddings', [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json'
        ],
        'body' => json_encode([
            'input' => $text,
            'model' => 'text-embedding-3-small'
        ]),
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) {
        return false;
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    return $body['data'][0]['embedding'] ?? false;
}
?>
```

### 3. Vector Database

Stores embeddings and enables efficient similarity search.

**Popular vector databases:**
- **Pinecone**: Managed, cloud-native
- **Weaviate**: Open-source, ML-first
- **Chroma**: Lightweight, embedded
- **Qdrant**: High-performance, Rust-based
- **Milvus**: Scalable, open-source

**WordPress Implementation Options:**
- Store vectors in custom database tables
- Use external vector database APIs (Pinecone, Weaviate)
- Use PostgreSQL with pgvector extension

**Example (WordPress custom table):**
```php
<?php
/**
 * Create custom table for vector storage
 */
function create_rag_vectors_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rag_vectors';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        post_id bigint(20) NOT NULL,
        chunk_index int(11) NOT NULL,
        content text NOT NULL,
        embedding longtext NOT NULL,
        metadata longtext,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY post_id (post_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Store embedding in database
 */
function store_embedding($post_id, $chunk_index, $content, $embedding, $metadata = []) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rag_vectors';
    
    return $wpdb->insert(
        $table_name,
        [
            'post_id' => $post_id,
            'chunk_index' => $chunk_index,
            'content' => $content,
            'embedding' => json_encode($embedding),
            'metadata' => json_encode($metadata)
        ],
        ['%d', '%d', '%s', '%s', '%s']
    );
}
?>
```

### 4. Similarity Search

Finds the most relevant chunks based on vector similarity.

**Common similarity metrics:**
- **Cosine similarity**: Measures angle between vectors (most common)
- **Euclidean distance**: Measures straight-line distance
- **Dot product**: Measures alignment and magnitude

**Example (WordPress/PHP):**
```php
<?php
/**
 * Calculate cosine similarity between two vectors
 */
function cosine_similarity($vec1, $vec2) {
    $dot_product = 0;
    $magnitude1 = 0;
    $magnitude2 = 0;
    
    for ($i = 0; $i < count($vec1); $i++) {
        $dot_product += $vec1[$i] * $vec2[$i];
        $magnitude1 += $vec1[$i] * $vec1[$i];
        $magnitude2 += $vec2[$i] * $vec2[$i];
    }
    
    $magnitude1 = sqrt($magnitude1);
    $magnitude2 = sqrt($magnitude2);
    
    if ($magnitude1 == 0 || $magnitude2 == 0) {
        return 0;
    }
    
    return $dot_product / ($magnitude1 * $magnitude2);
}

/**
 * Search for similar documents
 */
function search_similar_documents($query_embedding, $top_k = 5) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rag_vectors';
    
    $results = $wpdb->get_results("SELECT * FROM $table_name");
    $similarities = [];
    
    foreach ($results as $row) {
        $stored_embedding = json_decode($row->embedding, true);
        $similarity = cosine_similarity($query_embedding, $stored_embedding);
        
        $similarities[] = [
            'id' => $row->id,
            'post_id' => $row->post_id,
            'content' => $row->content,
            'metadata' => json_decode($row->metadata, true),
            'similarity' => $similarity
        ];
    }
    
    // Sort by similarity descending
    usort($similarities, function($a, $b) {
        return $b['similarity'] <=> $a['similarity'];
    });
    
    return array_slice($similarities, 0, $top_k);
}
?>
```

### 5. LLM Generation

The final step combines retrieved context with the user query to generate a response.

**Example (WordPress/PHP with OpenAI):**
```php
<?php
/**
 * Generate RAG response using retrieved context
 */
function generate_rag_response($user_query, $top_k = 3) {
    // Step 1: Generate query embedding
    $query_embedding = generate_embedding($user_query);
    
    if (!$query_embedding) {
        return ['error' => 'Failed to generate query embedding'];
    }
    
    // Step 2: Retrieve similar documents
    $similar_docs = search_similar_documents($query_embedding, $top_k);
    
    // Step 3: Build context from retrieved documents
    $context = "";
    foreach ($similar_docs as $doc) {
        $context .= "Content: " . $doc['content'] . "\n\n";
    }
    
    // Step 4: Create prompt with context
    $prompt = "Use the following context to answer the question. If the answer cannot be found in the context, say so.\n\n";
    $prompt .= "Context:\n" . $context . "\n\n";
    $prompt .= "Question: " . $user_query . "\n\n";
    $prompt .= "Answer:";
    
    // Step 5: Call LLM
    $api_key = get_option('openai_api_key');
    
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json'
        ],
        'body' => json_encode([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant that answers questions based on provided context.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7
        ]),
        'timeout' => 60
    ]);
    
    if (is_wp_error($response)) {
        return ['error' => $response->get_error_message()];
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    return [
        'answer' => $body['choices'][0]['message']['content'] ?? 'No response generated',
        'sources' => $similar_docs
    ];
}
?>
```

## RAG Architecture Patterns

### 1. Basic RAG
```
Query → Retrieve → Generate
```
Simple, single-pass retrieval and generation.

### 2. Iterative RAG
```
Query → Retrieve → Generate → Retrieve Again → Refine
```
Multiple retrieval rounds to improve answer quality.

### 3. Hybrid RAG
```
Query → [Keyword Search + Vector Search] → Rerank → Generate
```
Combines traditional search with semantic search.

### 4. Agentic RAG
```
Query → Agent Plans → Multiple Retrievals → Synthesis → Generate
```
LLM agent decides what to retrieve and how to use it.

## Evaluation Metrics

### Retrieval Quality
- **Precision@K**: Percentage of retrieved docs that are relevant
- **Recall@K**: Percentage of relevant docs that were retrieved
- **MRR (Mean Reciprocal Rank)**: Average rank of first relevant document
- **NDCG (Normalized Discounted Cumulative Gain)**: Ranking quality metric

### Generation Quality
- **Faithfulness**: How well the answer is grounded in retrieved context
- **Answer Relevance**: How well the answer addresses the question
- **Context Relevance**: How relevant retrieved documents are to the query

## WordPress RAG Plugin Architecture

```
┌────────────────────────────────────────────────────────────┐
│                    WordPress RAG Plugin                     │
├────────────────────────────────────────────────────────────┤
│                                                             │
│  Admin Panel                                                │
│  ├─ Settings (API Keys, Model Selection)                   │
│  ├─ Indexing Dashboard                                     │
│  └─ Query Testing Interface                                │
│                                                             │
│  Indexing System                                            │
│  ├─ Post/Page Content Extraction                           │
│  ├─ Content Chunking                                       │
│  ├─ Embedding Generation                                   │
│  └─ Vector Storage                                         │
│                                                             │
│  Query System                                               │
│  ├─ Embedding Generation                                   │
│  ├─ Similarity Search                                      │
│  ├─ Context Assembly                                       │
│  └─ LLM Response Generation                                │
│                                                             │
│  Shortcodes & Widgets                                       │
│  ├─ [rag_search] Shortcode                                 │
│  └─ RAG Search Widget                                      │
│                                                             │
│  REST API Endpoints                                         │
│  ├─ /wp-json/rag/v1/search                                 │
│  └─ /wp-json/rag/v1/index                                  │
│                                                             │
└────────────────────────────────────────────────────────────┘
```

## Best Practices

### 1. Chunking Strategy
- Keep chunks between 256-512 tokens for best results
- Use overlap (50-100 tokens) to preserve context
- Include metadata (title, section, page number)

### 2. Embedding Quality
- Use domain-specific embedding models when available
- Normalize embeddings for cosine similarity
- Consider fine-tuning embeddings on your data

### 3. Retrieval Optimization
- Retrieve more documents than needed (e.g., top 10) then rerank
- Use hybrid search (keyword + semantic)
- Filter by metadata (date, category, author)

### 4. Context Management
- Don't exceed LLM context window limits
- Order retrieved chunks by relevance
- Include source citations in responses

### 5. Performance
- Cache embeddings to avoid regeneration
- Index incrementally (only new/updated content)
- Use async processing for large indexing jobs

## Common Challenges & Solutions

| Challenge | Solution |
|-----------|----------|
| Outdated information | Implement incremental indexing on content updates |
| Poor retrieval quality | Use hybrid search, reranking, or query expansion |
| Context window limits | Implement smart truncation or summarization |
| Slow response times | Cache popular queries, use smaller models for embedding |
| Hallucinations | Add source citations, use grounding techniques |
| High costs | Optimize chunk size, use efficient models, cache results |

## Resources

- **OpenAI Embeddings API**: https://platform.openai.com/docs/guides/embeddings
- **Pinecone Documentation**: https://docs.pinecone.io/
- **LangChain**: Framework for building RAG applications
- **LlamaIndex**: Data framework for LLM applications
- **WordPress REST API**: https://developer.wordpress.org/rest-api/

## Conclusion

RAG is a powerful technique for enhancing LLMs with external knowledge. By combining retrieval with generation, you can build systems that are more accurate, up-to-date, and grounded in real data. WordPress provides an excellent platform for implementing RAG systems, with its robust content management capabilities and extensible architecture.