<?php
ini_set('max_execution_time', 300);
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

getImages('2014-12-31', '5');
createGif('2014');

/**
 * This function get date string and create giff file for this duration
 * for example: 2014-12-29 - create for all day,
 * 2014-12 - create for all month and
 * 2014-12-29 - create for all year
 *
 * You must have images available in your img folder
 *
 * For checking result in gif file you can see days in bottom left corner and see which of them contains in animation.
 *
 * @param $date
 */
function createGif($date)
{
    $files =  glob('img/*.jpeg');
    var_dump($files);
    $im = new Imagick();
    $im->setFormat('GIF');

    $frameCount = 0;

    foreach($files as $pic) {
        if($pic == "." || $pic == "..") continue;
        if (strpos($pic, $date) !== false) {
            $frame = new Imagick($pic);
            $frame->thumbnailImage(600, 600);
            $im->addImage($frame);
            $im->setImageDelay((($frameCount % 11) * 5));
            $im->nextImage();

            $frameCount++;
        }
    }

    try {
        $im->writeImages('img.gif', true);
    } catch(Exception $e) {
        echo "You use wrong date string or img file is missing <br />";
        echo "Error details: " . $e->getMessage();
    }
}

/**
 * Call this function for image downloading and save.
 * you need specify tomorrow day in $last_day just fo including all current day images
 * but you can use it with any date.
 *
 * @param $last_day string value of today + 1( example: '2014-12-31' ), its endTimestamp for get images request.
 * @param $num string number images for downloading, counting from $last_day
 */
function getImages($last_day, $num)
{
    $images = array();
    $timestamp = $last_day . '%2000:00:00.0';
    $url = 'http://iswa.gsfc.nasa.gov/IswaSystemWebApp/CygnetLastNInstancesServlet?cygnetId=251&endTimestamp='. $timestamp . '&lastN=' . $num;

    $result = json_decode(file_get_contents($url), TRUE);

    for($i=0; $i < sizeof($result['instances']); $i++) {
        $images[$result['instances'][$i]['timestamp']] = 'http://iswa.gsfc.nasa.gov' . $result['instances'][$i]['urls'][0]['url'];
    }
    saveImage($images);
}

/**
 * Do not call this function.
 *
 * This is just utility function for download image operation
 * it get images by http get request and if same file not exists in img folder save it.
 *
 * @param $img
 */
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
