<?php

namespace App\Tools;

use Illuminate\Support\Facades\Log;
use Prism\Prism\Facades\Tool;
use Illuminate\Support\Facades\Http;
use DOMDocument;
use DOMXPath;

class SearchTool
{
    public function definition()
    {
        return Tool:: as('search')
            ->for('Perform a web search using DuckDuckGo')
            ->withStringParameter('query', 'The search query (e.g., Coffee)', '')
            ->withStringParameter('location', 'The location for the search (e.g., us-en for United States, uk-en for United Kingdom)', 'us-en')
            ->withStringParameter('safe_search', 'Safe search setting (1 for On, -1 for Moderate, -2 for Off)', '-1')
            ->using(function (string $query, string $location = 'us-en', string $safe_search = '-1') {
                // Validate inputs
                if (empty(trim($query))) {
                    Log::debug("Invalid search query provided: query=$query");
                    return "Error: Search query must not be empty.";
                }

                // Construct DuckDuckGo URL
                $apiUrl = "https://lite.duckduckgo.com/lite/";
                $queryParams = [
                    'q' => $query,
                    'kl' => $location,
                    'kp' => $safe_search
                ];

                // Make HTTP request
                try {
                    $response = Http::get($apiUrl, $queryParams);
                    if ($response->failed()) {
                        Log::debug("DuckDuckGo request failed: status=" . $response->status() . ", body=" . $response->body());
                        return "Error: Failed to fetch search results.";
                    }

                    Log::debug("DuckDuckGo request successful for query=$query, location=$location");

                    // Parse HTML response
                    $html = $response->body();
                    Log::debug("raw: " . $html);

                    $doc = new DOMDocument();
                    @$doc->loadHTML($html); // Suppress warnings for malformed HTML
                    $xpath = new DOMXPath($doc);

                    // Extract search results (targeting DuckDuckGo's result structure)
                    $results = $xpath->query("//div[@class='result results_links_deep web-result']");

                    Log::debug("Found " . $results->length . " search results for query=$query");
                    if ($results->length === 0) {
                        return "No search results found for query: $query";
                    }

                    return $results;
                } catch (\Exception $e) {
                    Log::error("Error processing DuckDuckGo request: " . $e->getMessage());
                    return "Error: An unexpected error occurred while fetching search results.";
                }
            });
    }
}