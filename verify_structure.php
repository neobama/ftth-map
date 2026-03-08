<?php
/**
 * Script untuk verify struktur file map.blade.php
 * Jalankan di server: php verify_structure.php
 */

$filePath = __DIR__ . '/resources/views/filament/pages/map.blade.php';

if (!file_exists($filePath)) {
    echo "❌ File tidak ditemukan: $filePath\n";
    exit(1);
}

$content = file_get_contents($filePath);
$lines = explode("\n", $content);
$lineCount = count($lines);

echo "File: $filePath\n";
echo "Total lines: $lineCount\n\n";

// Check first line
echo "First line: " . trim($lines[0]) . "\n";
if (!str_starts_with(trim($lines[0]), '<x-filament-panels::page>')) {
    echo "❌ File tidak dimulai dengan <x-filament-panels::page>\n";
    exit(1);
}

// Check last line
$lastLine = trim($lines[$lineCount - 1]);
echo "Last line: $lastLine\n";
if (!str_ends_with($lastLine, '</x-filament-panels::page>')) {
    echo "❌ File tidak diakhiri dengan </x-filament-panels::page>\n";
    exit(1);
}

// Extract content between tags
preg_match('/<x-filament-panels::page>(.*?)<\/x-filament-panels::page>/s', $content, $matches);
if (empty($matches[1])) {
    echo "❌ Tidak bisa extract content dari <x-filament-panels::page>\n";
    exit(1);
}

$innerContent = trim($matches[1]);
$innerLines = explode("\n", $innerContent);

// Find top-level elements
$topLevel = [];
$baseIndent = null;

foreach ($innerLines as $i => $line) {
    $stripped = ltrim($line);
    
    // Skip comments and directives
    if (empty($stripped) || str_starts_with($stripped, '<!--') || str_starts_with($stripped, '@')) {
        continue;
    }
    
    // Check for opening tags
    if (preg_match('/^<(\w+)/', $stripped, $tagMatch) && !str_starts_with($stripped, '</')) {
        $currentIndent = strlen($line) - strlen($stripped);
        
        if ($baseIndent === null) {
            $baseIndent = $currentIndent;
            echo "Base indent: $baseIndent spaces\n\n";
        }
        
        if ($currentIndent === $baseIndent) {
            $topLevel[] = [
                'line' => $i + 1,
                'tag' => $tagMatch[1],
                'content' => substr($stripped, 0, 60)
            ];
        }
    }
}

echo "Found " . count($topLevel) . " top-level elements:\n";
foreach ($topLevel as $elem) {
    echo "  Line {$elem['line']}: <{$elem['tag']}> - {$elem['content']}\n";
}

if (count($topLevel) === 1) {
    echo "\n✅ Structure is CORRECT - only 1 root element\n";
    exit(0);
} else {
    echo "\n❌ PROBLEM: Found " . count($topLevel) . " top-level elements!\n";
    echo "This will cause the multiple root elements error.\n";
    exit(1);
}
