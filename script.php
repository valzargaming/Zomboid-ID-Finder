<?php

// Clear the output file
file_put_contents('WorshopItems.txt', '');
file_put_contents('Mods.txt', '');
file_put_contents('Both.txt', '');

// Define the regex pattern
$regexPattern = '/(?<!\?)id=([a-zA-Z0-9]+)\r?\n/';

$mods = '';
$ids = '';
$both = '';

// Recursive function to process files in a directory and its subdirectories
function processFiles($directory, $isRootDirectory = true) {
    global $regexPattern, $mods, $ids, $both;
    $files = scandir($directory);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;

        $filePath = $directory . '/' . $file;

        if (is_dir($filePath)) {
            processFiles($filePath, false);
            continue;
        }
        $fileContent = file_get_contents($filePath);
        preg_match_all($regexPattern, $fileContent, $matches);

        if (!empty($matches[1])) {
            $mod = $matches[1];
            $result = implode(';', $mod) . ';';
            //file_put_contents('Mods.txt', $result, FILE_APPEND);
            $mods .= $matches[1][0] . ';';

            // Find the first numeric ID in the path
            preg_match('/\/(\d+)\//', $directory, $idMatch);
            if (!empty($idMatch[1])) {
                $id = $idMatch[1];
                $both .= $id . '=' . $result . PHP_EOL;
                //file_put_contents('Both.txt', $both, FILE_APPEND);
                $ids .= $id . ';';
            }
        }
    }

    if ($isRootDirectory) {
        // Write the names of all folders in the root directory to WorkshopItems.txt
        $folders = array_filter(glob($directory . '/*'), 'is_dir');
        foreach ($folders as $folder) {
            $folderName = basename($folder);
            file_put_contents('WorshopItems.txt', $folderName . ';', FILE_APPEND);
        }
    }
}

// Start processing files in the current directory
processFiles(__DIR__);

file_put_contents('Mods.txt', $mods, FILE_APPEND);
file_put_contents('WorshopItems.txt', $ids . ';', FILE_APPEND);
file_put_contents('Both.txt', $both, FILE_APPEND);

echo "Script completed. Results written to WorkshopItems.txt, and Mods.txt" . PHP_EOL;
echo "Mods=" . substr($mods, 0, -1) . PHP_EOL;
echo "WorkshopItems=" . substr($ids, 0, -1) . PHP_EOL;
exit(0);
