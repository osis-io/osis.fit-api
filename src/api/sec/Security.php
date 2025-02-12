<?php

use \Firebase\JWT\JWT;

function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
/**
 * get access token from header
 * */
function getBearerToken() {
    $headers = getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

class Sec {

    public static function auth($LOG = false) {

        $authHead = getBearerToken();
        echo $authHead;
        $data = $authHead;


        if (!isset($_COOKIE[Env_sec::c_name])) {
            //$LOG->addInfo("no_sec_cookie"); // APPLE WORKAROUND
            throw new ApiException(403, "token_missing_secure", "secure");
        }

        /*
        list($type, $data) = explode(" ", $authHead, 2);
        if (strcasecmp($type, "Bearer") != 0) {
            throw new ApiException(403, "token_invalid", "not_bearer");
        }
        */

        $access = Sec::decode($data, Env_sec::t_access_secret);
        $secure = Sec::decode($_COOKIE[Env_sec::c_name], Env_sec::t_secure_secret);
        
        
        $phrase = $secure->data->phrase . $access->data->phrase;
        if (!password_verify(Env_sec::phrase . $access->data->account->mail, $phrase)) {
            throw new ApiException(403, "token_invalid", "phrase_wrong");
        }
        

        $sub = $access->data->subscription;
        $premium = false;

        if ($sub->id && !$sub->deleted) {
            if ($sub->status === 'active') $premium = true;
            else if ($sub->status === 'non_renewing') $premium = true;
            else if ($sub->status === 'in_trial') $premium = true;
        }

        $sec = (object) [
            "id" => $access->data->account->id,
            "mail" => $access->data->account->mail,
            "username" => $access->data->account->username,
            "level" => $access->data->level,
            "premium" => $premium,
        ];

        if ($LOG) $LOG->setUser($access->data->account);
        return $sec;
        
    }

    public static function placeAuth($data) {

        $now = time();
        $phrase = password_hash(Env_sec::phrase . $data->account->mail, Env_sec::encryption);
        $half = (int) ((strlen($phrase)/2));

        $def = [
            "iss" => Env_sec::t_issuer,
            "iat" => $now,
            "nbf" => $now,
        ];

        $jwt_sec = JWT::encode($def + [
            "exp" => $now + Env_sec::t_secure_lifetime,
            "data" => [
                "phrase" => substr($phrase, 0, $half)
            ]
        ], Env_sec::t_secure_secret);

        $jwt_app = JWT::encode($def + [
            "exp" => $now + Env_sec::t_access_lifetime,
            "data" => [
                "phrase" => substr($phrase, $half),
                "level" => $data->level,
                "account" => [
                    "id" => $data->account->id,
                    "mail" => $data->account->mail,
                    "username" => $data->account->username
                ],
                "subscription" => [
                    "id" => $data->subscription->id,
                    "status" => $data->subscription->status,
                    "deleted" => $data->subscription->deleted,
                    "expiration" => $data->subscription->expiration_stamp,
                    "plan" => $data->subscription->plan
                ]
            ]
        ], Env_sec::t_access_secret);

        $jwt_refresh = JWT::encode($def + [
            "exp" => $now + Env_sec::t_refresh_lifetime,
            "jti" => $data->refresh->jti,
            "data" => [
                "mail" => $data->refresh->mail,
                "phrase" => $data->refresh->phrase
            ]
        ], Env_sec::t_refresh_secret);
        
        $c = [
            "data" => $jwt_sec,
            "exp" => $now + Env_sec::t_access_lifetime
        ];

        
        //setcookie("secureToken", $token, $expire, "/", $conf['domain'], $conf['secure'], true);
        $cookie = setcookie(Env_sec::c_name, $c["data"], $c["exp"], Env_sec::c_path, Env_sec::c_domain, Env_sec::c_secure, true);

        if ($cookie) return (object) [
            "access" => $jwt_app,
            "refresh" => $jwt_refresh
        ];

        throw new Exception("cookie_error", 500);

    }

    public static function removeAuth() {

        $c = [
            "name" => Env_sec::c_name,
            "data" => false,
            "expire" => time() - 3600,
            "path" => Env_sec::c_path,
            "domain" => Env_sec::c_domain,
            "secure" => Env_sec::c_secure,
            "httponly" => true
        ];

        $cookie = setcookie($c["name"], $c["data"], $c["expire"], $c["path"], $c["domain"], $c["secure"], $c["httponly"]);
        if (!$cookie) throw new Exception("cookie_remove_error", 500);

    }

    public static function permit($userLevel, $allowedLevels) {
        $found = array_search($userLevel, $allowedLevels, TRUE);
        if ($found === FALSE) return false;
        else return true;
    }

    public static function decode($token, $secret, $alg = Env_sec::t_algorithm) {
        return JWT::decode($token, $secret, $alg);
    }
    
}