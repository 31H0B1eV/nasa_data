<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

$pic = getImageArray();

$im = new Imagick();
$im->setFormat('GIF');

$frameCount = 0;
$i = 0;
for(; $i < sizeof($pic); $i++) {
    $frame = new Imagick($pic[$i]);
    $frame->thumbnailImage(600, 600);
    $im->addImage($frame);
    $im->setImageDelay((($frameCount % 11) * 5));
    $im->nextImage();

    $frameCount++;
}

$im->writeImages('img.gif', true);

function getImageArray()
{
    $images = array();
    $timestamp = '2014-12-30%2000:00:00.0';
    $num = '10';
    $url = 'http://iswa.gsfc.nasa.gov/IswaSystemWebApp/CygnetLastNInstancesServlet?cygnetId=251&endTimestamp='. $timestamp . '&lastN=' . $num;

    $result = json_decode(file_get_contents($url), TRUE);

    for($i=0; $i < sizeof($result['instances']); $i++) {
        array_push($images, 'http://iswa.gsfc.nasa.gov' . $result['instances'][$i]['urls'][0]['url']);
    }
    return $images;
}
