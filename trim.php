<?php
$file = 'app/Http/Controllers/Admin/BlastRecipientController.php';
$content = file_get_contents($file);
$lines = explode(PHP_EOL, $content);
if(count($lines) < 2) { $lines = explode("\n", $content); }
$newLines = array_slice($lines, 0, 1674);
file_put_contents($file, implode(PHP_EOL, $newLines) . PHP_EOL . '}');
echo "Done\n";
