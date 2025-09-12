<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class JsonReferenceSearchController extends Controller
{
    protected array $referenceData = [];
    protected array $searchIndex = [];

    public function __construct()
    {
        $this->loadReferenceData();
    }

    public function search(Request $request): JsonResponse
    {
        $query = trim($request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = $this->performSearch($query);

        return response()->json($results);
    }

    protected function loadReferenceData(): void
    {
        $cacheKey = 'reference-data-v3';
        
        $this->referenceData = Cache::remember($cacheKey, 3600, function () {
            $jsonFile = resource_path('json/reference-content.json');
            
            if (!File::exists($jsonFile)) {
                return [];
            }

            return json_decode(File::get($jsonFile), true);
        });

        $this->searchIndex = $this->referenceData['search_index'] ?? [];
    }

    protected function performSearch(string $query): array
    {
        $normalizedQuery = strtolower(trim($query));
        $queryWords = array_filter(explode(' ', $normalizedQuery), fn($word) => strlen($word) >= 2);
        
        $results = [];
        $scores = [];

        // Search through the pre-built index for exact matches
        foreach ($queryWords as $word) {
            if (isset($this->searchIndex[$word])) {
                foreach ($this->searchIndex[$word] as $match) {
                    $key = $match['page_key'] . '::' . $match['section'];
                    
                    if (!isset($scores[$key])) {
                        $scores[$key] = 0;
                        $results[$key] = $match;
                    }
                    
                    $scores[$key] += 100; // Base score for exact word match
                }
            }
        }

        // Search through content for phrase matches and partial matches
        foreach ($this->referenceData['content'] ?? [] as $pageKey => $page) {
            foreach ($page['sections'] as $sectionTitle => $section) {
                $content = strtolower($section['content']);
                $title = strtolower($page['title']);
                $sectionTitleLower = strtolower($sectionTitle);
                
                $score = 0;
                
                // Exact phrase match in content (highest priority)
                if (str_contains($content, $normalizedQuery)) {
                    $score += 500;
                    
                    // Word boundary bonus
                    if (preg_match('/\b' . preg_quote($normalizedQuery, '/') . '\b/', $content)) {
                        $score += 200;
                    }
                }
                
                // Exact phrase match in title (very high priority)
                if (str_contains($title, $normalizedQuery)) {
                    $score += 1000;
                }
                
                // Exact phrase match in section title
                if (str_contains($sectionTitleLower, $normalizedQuery)) {
                    $score += 750;
                }
                
                // Individual word matches
                foreach ($queryWords as $word) {
                    // Title matches
                    if (str_contains($title, $word)) {
                        $score += 300;
                        
                        if (preg_match('/\b' . preg_quote($word, '/') . '\b/', $title)) {
                            $score += 100;
                        }
                    }
                    
                    // Section title matches
                    if (str_contains($sectionTitleLower, $word)) {
                        $score += 200;
                        
                        if (preg_match('/\b' . preg_quote($word, '/') . '\b/', $sectionTitleLower)) {
                            $score += 75;
                        }
                    }
                    
                    // Content matches
                    if (str_contains($content, $word)) {
                        $score += 50;
                        
                        if (preg_match('/\b' . preg_quote($word, '/') . '\b/', $content)) {
                            $score += 25;
                        }
                    }
                }
                
                if ($score > 0) {
                    $key = $pageKey . '::' . $sectionTitle;
                    
                    if (!isset($scores[$key]) || $scores[$key] < $score) {
                        $scores[$key] = $score;
                        $results[$key] = [
                            'page_key' => $pageKey,
                            'section' => $sectionTitle,
                            'title' => $page['title'],
                            'category' => $page['category'],
                            'subcategory' => $page['subcategory'] ?? null,
                            'tier' => $page['tier'] ?? null,
                            'content' => $section['content'],
                            'word_count' => $section['word_count'],
                            'anchor' => $section['anchor']
                        ];
                    }
                }
            }
        }

        // Convert to final format and sort by score
        $finalResults = [];
        foreach ($results as $key => $result) {
            $score = $scores[$key];
            $snippet = $this->generateSnippet($result['content'] ?? '', $query);
            
            $finalResults[] = [
                'key' => $result['page_key'] ?? '',
                'title' => ($result['section'] ?? '') === '_intro' ? ($result['title'] ?? '') : ($result['section'] ?? ''),
                'parent_page' => ($result['section'] ?? '') !== '_intro' ? ($result['title'] ?? '') : null,
                'type' => $this->determineResultType($result),
                'category' => $result['category'] ?? 'reference',
                'subcategory' => $result['subcategory'] ?? null,
                'tier' => $result['tier'] ?? null,
                'score' => $score,
                'snippet' => $snippet,
                'section_anchor' => $result['anchor'] ?? '',
                'has_highlight' => str_contains($snippet, '<mark>') || 
                                 str_contains(strtolower($result['title'] ?? ''), strtolower($query))
            ];
        }

        // Sort by score (highest first) and limit results
        usort($finalResults, fn($a, $b) => $b['score'] <=> $a['score']);
        
        return array_slice($finalResults, 0, 15);
    }

    protected function generateSnippet(string $content, string $query, int $maxLength = 200): string
    {
        $normalizedQuery = strtolower($query);
        $normalizedContent = strtolower($content);
        
        // Find the best position for the snippet
        $position = strpos($normalizedContent, $normalizedQuery);
        
        if ($position === false) {
            // If exact phrase not found, look for individual words
            $queryWords = explode(' ', $normalizedQuery);
            $bestPosition = 0;
            $bestScore = 0;
            
            foreach ($queryWords as $word) {
                if (strlen($word) >= 2) {
                    $wordPos = strpos($normalizedContent, $word);
                    if ($wordPos !== false) {
                        $score = 1000 - $wordPos; // Earlier positions get higher scores
                        if ($score > $bestScore) {
                            $bestScore = $score;
                            $bestPosition = $wordPos;
                        }
                    }
                }
            }
            
            $position = $bestPosition;
        }
        
        // Calculate snippet boundaries
        $start = max(0, $position - 50);
        $end = min(strlen($content), $start + $maxLength);
        
        // Try to start and end at word boundaries
        if ($start > 0) {
            $spacePos = strpos($content, ' ', $start);
            if ($spacePos !== false && $spacePos < $start + 20) {
                $start = $spacePos + 1;
            }
        }
        
        if ($end < strlen($content)) {
            $spacePos = strrpos($content, ' ', $end - strlen($content));
            if ($spacePos !== false && $spacePos > $end - 20) {
                $end = $spacePos;
            }
        }
        
        $snippet = substr($content, $start, $end - $start);
        
        // Add ellipsis if needed
        if ($start > 0) {
            $snippet = '...' . $snippet;
        }
        if ($end < strlen($content)) {
            $snippet = $snippet . '...';
        }
        
        // Highlight search terms
        $snippet = $this->highlightSearchTerms($snippet, $query);
        
        return trim($snippet);
    }

    protected function highlightSearchTerms(string $text, string $query): string
    {
        $queryWords = array_filter(explode(' ', $query), fn($word) => strlen($word) >= 2);
        
        foreach ($queryWords as $word) {
            // Use word boundaries for better matching
            $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
            $text = preg_replace($pattern, '<mark>$0</mark>', $text);
        }
        
        return $text;
    }

    protected function determineResultType(array $result): string
    {
        if (isset($result['subcategory'])) {
            return match ($result['subcategory']) {
                'environments' => 'environment',
                'campaign-frames' => 'frame',
                default => 'content'
            };
        }
        
        return $result['section'] === '_intro' ? 'page' : 'section';
    }
}
