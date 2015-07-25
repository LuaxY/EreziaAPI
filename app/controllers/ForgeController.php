<?php

class ForgeController extends BaseController {

    public function forge($request)
    {
        $redis = Redis::connection();

        $format = pathinfo($request, PATHINFO_EXTENSION);
        switch ($format)
        {
            case 'png':
            default:
                header('Content-Type: image/png');
                break;
        }

        $url = "http://staticns.ankama.com/";
        $url .= $request;
        $hash = md5($request);

        $data = $redis->get("dofus:forge:$hash");

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
                $redis->set("dofus:forge:$hash", $result);
                print $result;
            }

            curl_close($curl);
        }
    }

    public function text($id)
    {
        $redis = Redis::connection();

        $text = $redis->get("dofus:text:$id");

        if ($text)
        {
            print $text;
        }
        else
        {
            $obj = json_decode(file_get_contents("i18n_fr.json"));
            $texts = $obj->texts;

            $text = @$texts->$id;

            if (!$text)
            {
                print "Text not found.";
            }
            else
            {
                $redis->set("dofus:text:$id", $text);
                print $text;
            }
        }
    }

}
