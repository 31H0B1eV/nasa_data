<?php
ini_set('max_execution_time', 300);
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

getImageArray();

$files =  glob('img/*.jpeg');
var_dump($files);

/**
 * @param $file_name array of files
 * @param $date array with index 0 => as startDate and 1 => as endDate
 * @throws ErrorException
 */
function createGif($file_name, $date)
{
    if(!is_array($date) || !is_array($file_name)) {
        throw new ErrorException('createGif function parameters must be arrays');
    } else {

        $im = new Imagick();
        $im->setFormat('GIF');

        $frameCount = 0;

        foreach($file_name as $pic) {
            if($pic == "." || $pic == "..") continue;
            $frame = new Imagick($pic);
            $frame->thumbnailImage(600, 600);
            $im->addImage($frame);
            $im->setImageDelay((($frameCount % 11) * 5));
            $im->nextImage();

            $frameCount++;
        }

        $im->writeImages('img.gif', true);
    }
}

function getImageArray()
{
    $images = array();
    $timestamp = '2014-12-31%2000:00:00.0';
    $num = '20';
    $url = 'http://iswa.gsfc.nasa.gov/IswaSystemWebApp/CygnetLastNInstancesServlet?cygnetId=251&endTimestamp='. $timestamp . '&lastN=' . $num;

    $result = json_decode(file_get_contents($url), TRUE);

    for($i=0; $i < sizeof($result['instances']); $i++) {
        $images[$result['instances'][$i]['timestamp']] = 'http://iswa.gsfc.nasa.gov' . $result['instances'][$i]['urls'][0]['url'];
    }
    saveImage($images);
}

function saveImage($img)
{
    foreach($img as $k => $v) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL , $v);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response= curl_exec ($ch);
        curl_close($ch);
        $file_name = "./img/".substr($k, 0, -3).".jpeg";
        if(!file_exists($file_name)) {
            $file = fopen($file_name , 'w') or die("X_x");
            fwrite($file, $response);
            fclose($file);
        } else {
            file_put_contents('log', 'File: ' . $file_name . ' exists' . "\n", FILE_APPEND);
        }
    }
}
