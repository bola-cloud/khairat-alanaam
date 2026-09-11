<?php
$file = 'resources/views/v2/home/index.blade.php';
$content = file_get_contents($file);

// Find the Features Section
$startTag = '<!-- Features Section -->';
$endTag = '    <!-- Category Swiper -->';

$startPos = strpos($content, $startTag);
$endPos = strpos($content, $endTag);

if ($startPos !== false && $endPos !== false) {
    $featuresSection = substr($content, $startPos, $endPos - $startPos);
    
    // Remove it from original position
    $content = str_replace($featuresSection, '', $content);
    
    // Find where to insert it: before <!-- Recipe Modal --> or right before <script> tag for FAQs
    // Better yet, after the last section. The last section is <!-- FAQ --> or whatever is before <script> document.addEventListener('DOMContentLoaded', function() { const faqHeaders
    
    $insertPoint = '<script>
        document.addEventListener(\'DOMContentLoaded\', function() {
            const faqHeaders = document.querySelectorAll(\'.faq-header\');';
            
    $content = str_replace($insertPoint, $featuresSection . "\n    " . $insertPoint, $content);
    
    file_put_contents($file, $content);
    echo "Features section moved successfully.\n";
} else {
    echo "Could not find tags.\n";
}
