<?php
//DECLARE VARIABLES
$imgArray = array();
$i = 1;
if(!isset($argv[1]))
{
    echo "Please enter an argument\n";
    exit;
}
switch($argv[1])
{                           //exit le script si necessaire
    case "-r":
        if(isset($argv[2]))
        {
            if(is_dir($argv[2]))
            {
                my_scandir($argv[2]);
                my_merge_image($imgArray[0], $imgArray[1]);
                my_generate_css();
                echo "Your sprite has been generated !\n";
                exit;
            }
            else
            {
                echo "Please a valid path\n";
                exit;
            }
        }
        else
        {
            echo "Please enter a path\n";
            exit;
        }
    break;
    case "-i":
        if(isset($argv[2]))
        { 
            my_scandir("./images_to_merge");
            my_merge_image($imgArray[0], $imgArray[1]);
            my_generate_css();
            rename("./sprite.png", $argv[2] . ".png");
            echo "Your sprite name has been changed to " . $argv[2] . ".png\n";
            exit;
            
        }
        else
        {
            my_scandir("./images_to_merge");
            my_merge_image($imgArray[0], $imgArray[1]);
            my_generate_css();
            echo "Please enter a name for your sprite\n";
            exit;
        }
    break;
    case "-s":
        if(isset($argv[2]))
        {
            my_scandir("./images_to_merge");
            my_merge_image($imgArray[0], $imgArray[1]);
            my_generate_css();
            rename("./style.css", $argv[2] . ".css");
            echo "Your stylesheet name has been changed to " . $argv[2] . ".css\n";
            exit;
        }
        else
        {
            my_scandir("./images_to_merge");
            my_merge_image($imgArray[0], $imgArray[1]);
            my_generate_css();
            echo "Please enter a name for your stylesheet\n";
            exit;
        }
    break;
    default:
        echo "Please enter a valid command\n";
        exit;
}
//DECLARE FUNCTIONS
function my_merge_image($first_img_path, $second_img_path)
{
    global $imgArray;
    global $i;

    $firstImage = imagecreatefrompng(".$first_img_path");
    $firstImageSize = getimagesize(".$first_img_path");

    $secondImage = imagecreatefrompng(".$second_img_path");
    $secondImageSize = getimagesize(".$second_img_path");
            
    $blankWidth = $firstImageSize[0] + $secondImageSize[0];
    $blankHeight = ($firstImageSize[1] > $secondImageSize[1] ? $firstImageSize[1] : $secondImageSize[1]);
    $blankImage = imagecreatetruecolor($blankWidth, $blankHeight);
    
    imagecopymerge($blankImage, $firstImage, 0, 0, 0, 0, $firstImageSize[0], $firstImageSize[1], 100);
    imagecopymerge($blankImage, $secondImage, $firstImageSize[0], 0, 0, 0, $secondImageSize[0], $secondImageSize[1], 100);
    imagedestroy($firstImage);
    imagedestroy($secondImage); 

    imagepng($blankImage, "sprite.png");

    $arrayCount = count($imgArray);
    $i++;
    while($i<$arrayCount)
    {
        my_merge_image("/sprite.png", $imgArray[$i]);
    }
}

function my_generate_css()
{
    global $imgArray;
    $spriteString = null;
    $positions = null;
    $temp = 0;
    $i = 0;
    $spriteProperties = "{display: inline-block; background: url('sprite.png') no-repeat; overflow: hidden; text-align: left;}";

    $cssFile = fopen("style.css" ,"w");
    foreach($imgArray as $value)
    {
        global $spriteString;
        $fileName = basename($value);
        $imgName = preg_replace("/.png/", "", $fileName);
        $spriteString .= ".$imgName, ";
    }
    $soloImgString = explode(", ", $spriteString);
    array_splice($soloImgString, -1, 1);
    $spriteString = substr($spriteString, 0, -2);
    
    foreach($soloImgString as $value)
    {
        $size = getimagesize(".$imgArray[$i]");
        $positions .= "$value {background-position: ".$temp."px 0px; width: $size[0]px; height: $size[1]px;}".PHP_EOL;
        $temp += $size[0];
        $i++;
    }
    $cssContent = $spriteString . PHP_EOL . $spriteProperties."\n\n".$positions;
    fwrite($cssFile, $cssContent);
    fclose($cssFile);
}

function my_scandir($dir_path)
{
    global $imgArray;

    $merge_img = opendir($dir_path);
    while(false !== ($entry = readdir($merge_img)))
    {
        if($entry != "." && $entry != "..")
        {
            $path = $dir_path . "/" . $entry;
            if(is_dir($path))
            {
                my_scandir($path);
            }
            $path = substr($path, 1);
            array_push($imgArray, $path);
        }
    }
    $pngArray = preg_grep("/.png/", $imgArray);
    $imgArray = array_merge($pngArray);
    closedir($merge_img);
    return $imgArray;
}