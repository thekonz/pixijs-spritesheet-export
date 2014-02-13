#!/usr/bin/env php
<?php
//fancy introduction
echo '#################################' . "\n";
echo '# Export JSON sprite sheet data #' . "\n";
echo '# from images to use in PixiJS. #' . "\n";
echo '#################################' . "\n\n";

//constants
define(
    'APP_VERSION',
    'v1.0.1'
);

define(
    'OUT_DEFAULT',
    'Enter -h for help.' . "\n"
);

define(
    'OUT_HELP',
    '-h        Print this help.' . "\n" .
    '-e <file> Export frames of file as JSON data.' . "\n" .
    '-w/-h     Width/Height of each frame. Default: 16 (pixels).' . "\n" .
    '-o        Filename of exported JSON file. Default: <name of image>.json.' . "\n" .
    '-s        Index of frame to start the export. Default: 0.' . "\n" .
    '-a        Amount of frames to export. Default: 0 (will export every frame).' . "\n" .
    'Example: -e image.png -w 32 -h 32 -o test.json -s 2 -a 3' . "\n"
);

//y u no arguments?
if (false === isset($argv[1])) {
    die(OUT_DEFAULT);
}

//hey, listen!
if ('-h' == $argv[1]) {
    echo OUT_HELP;
}

if ('-e' == $argv[1]) {
    try {
        $imageFile = $argv[2];
        if (false === is_readable($imageFile)) {
            throw new Exception('File ' . $imageFile . ' is not readable!');
        }

        $imageInfo = getimagesize($imageFile);
        if (false === $imageInfo) {
            throw new Exception('File ' . $imageFile . ' is not an image!');
        }

        //json filename
        $jsonFile = explode('.', $imageFile);
        array_pop($jsonFile);
        array_push($jsonFile, 'json');

        //defaults
        $options = array(
            '-w' => 16,
            '-h' => 16,
            '-o' => implode('.', $jsonFile),
            '-s' => 0,
            '-a' => 0,
        );

        //overwrite defaults
        $args = array_slice($argv, 3);
        $argsLength = count($args);
        for ($i = 0; $i < $argsLength; $i = $i + 2) {
            if (isset($options[$args[$i]])) {
                $options[$args[$i]] = $args[$i + 1];
            }
        }

        //check if the image can be separated equally
        if ((0 !== $imageInfo[0] % $options['-w'])
            || (0 !== $imageInfo[1] % $options['-h'])) {
            throw new Exception('Image ' . $imageFile . 
                                ' has width ' . $imageInfo[0] . 
                                ' and height ' . $imageInfo[1] .
                                ' and cannot be separated equally using width ' . $options['-w'] .
                                ' and height ' . $options['-h'] . '!');
        }

        $amount = $options['-a'];
        if(0 === $amount) {
            $amount = ($imageInfo[0] / $options['-w']) * ($imageInfo[1] / $options['-h']);
        }

        //the result as array
        $json = array(
            'frames' => array(),
        );

        for ($i = $options['-s']; $i < $amount; $i++) {
            $x = $i % ($imageInfo[0] / $options['-w']);
            $y = (int) ($i / ($imageInfo[1] / $options['-h']));
            $json['frames'][] = array(
                'frame' => array(
                    'x' => $x * $options['-w'],
                    'y' => $y * $options['-h'],
                    'w' => $options['-w'],
                    'h' => $options['-h'],
                ),
                'rotated' => false,
                'trimmed' => false,
                'spriteSourceSize' => array(
                    'x' => 0,
                    'y' => 0,
                    'w' => $options['-w'],
                    'h' => $options['-h'],
                ),
                'sourceSize' => array(
                    'w' => $options['-w'],
                    'h' => $options['-h'],
                ),
            );
        }

        //add metadata
        $json['meta'] = array(
            'app' => 'https://github.com/thekonz/pixijs-spritesheet-export',
            'version' => APP_VERSION,
            'image' => $imageFile,
            'format' => 'RGBA8888',
            'size' => array(
                'w' => $imageInfo[0],
                'h' => $imageInfo[1],
            ),
            'scale' => '1',
        );

        //write json file
        $writeJson = file_put_contents($options['-o'], json_encode($json));

        if (false !== $writeJson) {
            echo $writeJson . ' bytes written to ' . $options['-o'] . "!\n" .
                 'Export completed!' . "\n";
        }
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage() . "\n" .
            'Export aborted!' . "\n");
    }
}
