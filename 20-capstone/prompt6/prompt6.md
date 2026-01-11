Here are two rest apis that get results based on Full Text Search and Vector Cosine Search. Examples of the JSON response is also included for each REST API


https://mydigitalagent.co.uk/dev/wp-json/posts-rag/v1/search?query=FOAM&limit=3

{
"success": true,
"query": "FOAM",
"method": "fulltext_search",
"results": [
{
"post_id": 169,
"post_title": "FOAM 2 Memory Pillow Set of 2",
"relevance_score": 4.747172832489014,
"categories": "Home &amp; Kitchen",
"tags": "bedding, bedroom, memory foam, pillows, sleep accessories",
"excerpt": "Premium shredded memory foam adjusts to your preferred sleeping position and height. Breathable bamboo cover remains cool throughout the night. Hypoallergenic materials resist dust mites, mold, and allergens. Machine-washable cover&hellip;"
},
{
"post_id": 179,
"post_title": "FOAM 3 Roller High Density",
"relevance_score": 3.56037974357605,
"categories": "Fitness &amp; Sports",
"tags": "foam roller, massage, muscle recovery, physical therapy, stretching",
"excerpt": "Deep tissue massage releases muscle tension and improves flexibility. High-density foam maintains shape through years of regular use. Textured surface increases blood flow to targeted areas. Hollow core design provides&hellip;"
},
{
"post_id": 398,
"post_title": "FOAM 1  Mattress Strong",
"relevance_score": 1.1867932081222534,
"categories": "Uncategorized",
"tags": "",
"excerpt": "This mattress is very comfortable yet very supportive."
}
],
"count": 3}



https://mydigitalagent.co.uk/dev/wp-json/posts-rag/v1/vector-search?query=FOAM&limit=3

{
"success": true,
"query": "FOAM",
"method": "vector_search",
"results": [
{
"post_id": 179,
"post_title": "FOAM 3 Roller High Density",
"similarity_score": 0.6884264850120317,
"categories": "Fitness &amp; Sports",
"tags": "foam roller, massage, muscle recovery, physical therapy, stretching",
"excerpt": "Deep tissue massage releases muscle tension and improves flexibility. High-density foam maintains shape through years of regular use. Textured surface increases blood flow to targeted areas. Hollow core design provides&hellip;"
},
{
"post_id": 398,
"post_title": "FOAM 1  Mattress Strong",
"similarity_score": 0.6810727310805985,
"categories": "Uncategorized",
"tags": "",
"excerpt": "This mattress is very comfortable yet very supportive."
},
{
"post_id": 169,
"post_title": "FOAM 2 Memory Pillow Set of 2",
"similarity_score": 0.6197200579344587,
"categories": "Home &amp; Kitchen",
"tags": "bedding, bedroom, memory foam, pillows, sleep accessories",
"excerpt": "Premium shredded memory foam adjusts to your preferred sleeping position and height. Breathable bamboo cover remains cool throughout the night. Hypoallergenic materials resist dust mites, mold, and allergens. Machine-washable cover&hellip;"
}
],
"count": 3
}

For the attached plugin file, create an additional admin page for this plugin that uses the saved api key already done in the plugin that provides an input for the query and an output area that displays the returned JSON object that is a result of using the FTS API and Vector API to get response.

fts_id has the post_ids for the results from the https://mydigitalagent.co.uk/dev/wp-json/posts-rag/v1/search?query=FOAM&limit=3 for a given query

vector_ids has all the post_ids from the https://mydigitalagent.co.uk/dev/wp-json/posts-rag/v1/vector-search?query=FOAM&limit=3 for a given query



{
"query": "What FOAM do ypu have?" 
"fts_ids": [3,7,9],
"vector_ids": [3,7,10,11]
"context": "title and content of all the found posts combined"
}

Also have and additional area that display only the value of the "context".