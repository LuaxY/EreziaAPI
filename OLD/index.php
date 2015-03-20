<?php

require 'config.php';
require 'vendor/autoload.php';

$client = new Predis\Client(Config::$redis);

$request = $_SERVER['REQUEST_URI'];
$dispatcher = explode('/', $request);

if (@$dispatcher[1] == "forge")
{
    $format = pathinfo($request, PATHINFO_EXTENSION);
    switch ($format)
    {
        case 'png':
        default:
            header('Content-Type: image/png');
            break;
    }

    $url = "http://staticns.ankama.com/";
    $request = str_replace('/forge/', '', $request);
    $url .= $request;
    $hash = md5($request);

    $data = $client->get("dofus:forge:.".$hash);

    if ($data)
    {
        print $data;
    }
    else
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        $result = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($code == 404)
        {
            header("HTTP/1.1 404 Not Found");
            header('Content-Type: plain/text');
            print $result;
        }
        else
        {
            $client->set("dofus:forge:".$hash, $result);
            print $result;
        }

        curl_close($curl);
    }
}

if (@$dispatcher[1] == "text")
{
    $textId = is_numeric(@$dispatcher[2]) ? $dispatcher[2] : null;

    if (!$textId)
        die("Invalid text id.");

    $text = $client->get("dofus:text:.".$textId);

    if ($text)
    {
        print $text;
    }
    else
    {
        $filename = "i18n_fr.json";
        $file = fopen($filename, "r");
        $json = fread($file, filesize($filename));
        fclose($file);

        $obj = json_decode($json);
        $texts = $obj->texts;

        $text = @$texts->$textId;

        if (!$text)
        {
            print "Text not found.";
        }
        else
        {
            $client->set("dofus:text:".$textId, $text);
            print $text;
        }
    }
}

?>
