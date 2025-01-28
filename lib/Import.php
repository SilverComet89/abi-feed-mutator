<?php

namespace SilverComet\lib;

class Import
{
    public function dumpToFile($file, $data)
    {
        $myfile = fopen($file, "w"); // w here truncates the file so we only ever have 1 copy.
        fwrite($myfile, $data);
        fclose($myfile);
    }

    public function getFeed($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $data = curl_exec($curl);
        curl_close($curl);

        return $data;
    }
}
