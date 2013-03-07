<?php

class Security {

    const HASHSALT = "ryan-hash-salt";

    /**
     * 
     * @param string $password
     * @param string $salt
     * @return string
     */
    public static function hashPasswordForDatabase($password, $salt) {
        $plainText = $salt . $password . $salt;
        $hash1 = sha1($plainText);
        $hash2 = sha1($hash1);
        return $hash2;
    }

    /**
     * 
     * @param string $password
     * @param int $size
     * @return string
     */
    public static function hashPassword($password, $size = 8) {
        $plainText = Security::HASHSALT . $password . Security::HASHSALT;
        $hash = sha1($plainText);
        return substr($hash, 0, $size);
    }

    /**
     * 
     * @param string $text
     * @return string
     */
    public static function encryptDES($text) {
        $key = 'qFS8LRE6XGZmNx9idHFK6AYC';
        $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_3DES, $key, $text, MCRYPT_MODE_ECB));
        return $encrypted;
    }

    /**
     * 
     * @param string $cipher
     * @return string
     */
    public static function decryptDES($cipher) {
        $key = 'qFS8LRE6XGZmNx9idHFK6AYC';
        $decrypted = mcrypt_decrypt(MCRYPT_3DES, $key, base64_decode($cipher), MCRYPT_MODE_ECB);
        return $decrypted;
    }

    /**
     * 
     * @param int $length
     * @param boolean $puncuation
     * @return string
     */
    public static function randString($length, $puncuation = false) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        if ($puncuation) {
            $chars .= "`~!@#$%^&*()-=+\|[]{};:";
        }
        $str = '';
        $size = strlen($chars);
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[rand(0, $size - 1)];
        }
        return $str;
    }
    function checkAccessAdminTools() {            
            //only yesup office ip allow access this page
            $allowedip = '199.21.151.11';
            $ip = $_SERVER['REMOTE_ADDR'];
            if ($ip != $allowedip){
                $root_dir_level = "../";
                header('HTTP/1.0 404 Not Found');
				exit;
            }
        }

}

//echo Security::hashPassword('test', 5) . "<br/>";
//echo Security::hashPasswordForDatabase('test') . "<br/>";
//echo Security::randString(5) . "<br/>";
//echo Security::randString(5, true) . "<br/>";
//echo Security::encryptDES('secret') . "<br/>";
//$e = Security::encryptDES('secret');
//echo Security::decryptDES($e) . "<br/>";
?>

