<?php


namespace app\helpers;


use Yii;

class TokenHelper
{
    public static function setToken($token, $data)
    {
        $nm = self::path() . $token;
        if (!file_exists($nm)) {
            $handle = fopen($nm, 'w');
            fwrite($handle, $data);
            fclose($handle);
        }
    }

    /**
     * Get contents of token file
     *
     * @param $token
     * @return bool|false|string    False means no token exists
     */
    public static function getData($token)
    {
        $nm = self::path() . $token;
        $data = false;
        if (file_exists($nm)) {
            $handle = fopen($nm, 'r');
            $data = fread($handle, filesize($nm));
            fclose($handle);
        }
        return $data;
    }

    public static function removeToken($token)
    {
        $nm = self::path() . $token;
        if (file_exists($nm)) {
            unlink($nm);

        }
    }

    private static function path()
    {
        return Yii::$app->params['tokenDir'];
    }

}