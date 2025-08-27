<?php

/**
 * Script to fix syntax issues in browser tests after automated conversion
 */

$browserTestsDir = 'tests/Browser';
$files = glob($browserTestsDir . '/*.php');

echo "Fixing syntax issues in browser tests...\n";

foreach ($files as $file) {
    $filename = basename($file);
    
    // Skip certain files
    if (in_array($filename, ['CharacterBuilderMacros.php', 'Page.php', 'HomePage.php', 'CampaignFramesPage.php'])) {
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Fix the pattern where we have "}); });" 
    $content = preg_replace('/\s*}\);\s*}\);\s*\n/', "\n});\n\n", $content);
    
    // Fix cases where we have "    });\n});" 
    $content = preg_replace('/\s+}\);\s*}\);\s*\n/', "\n});\n\n", $content);
    
    // Fix trailing issues at end of file
    $content = preg_replace('/\s*}\);\s*$/', "\n});", $content);
    
    // Ensure proper spacing between tests
    $content = preg_replace('/}\);\s*test\(/', "});\n\ntest(", $content);
    
    // Ensure file ends properly
    if (!str_ends_with(trim($content), '});')) {
        $content = rtrim($content) . "\n";
    }
    
    file_put_contents($file, $content);
    echo "Fixed: $filename\n";
}

echo "\nSyntax fixing complete!\n";
