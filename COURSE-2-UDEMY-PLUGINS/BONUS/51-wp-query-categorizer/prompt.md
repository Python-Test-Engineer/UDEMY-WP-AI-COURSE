
        $prompt = "You are analyzing a user query to find the most appropriate WordPress category that would likely contain the answer.\n\n";
        $prompt .= "User Query: \"$query\"\n\n";
        $prompt .= "Available WordPress Categories (with descriptions):\n";
        $prompt .= $categories_json . "\n\n";
        $prompt .= "Analyze the query and all category names and descriptions. Determine which category is most likely to contain content that would answer this query.\n\n";
        $prompt .= "Respond with ONLY a JSON object in this exact format:\n";
        $prompt .= "{\n";
        $prompt .= '  "query": "the original user query",'."\n";
        $prompt .= '  "category_name": "the most appropriate category name",'."\n";
        $prompt .= '  "category_description": "explanation of why this category is best suited to contain the answer to the query"'."\n";
        $prompt .= "}";