<?php

class NetumoAPI{

    // Generating a token for the login session, unless a token already exists

    function TokenLogin($username, $password){
     
        if ($username != "" && $password != ""){

           
            $TokenValidation = 1095;

            $data = array(
            
            );
            $payload = http_build_query($data);
            $url = "https://api.netumo.app/api/Account/CreateAPIToken?username=$username&password=$password&validFor=$TokenValidation";
            $client = curl_init($url);
            curl_setopt($client, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($client, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($client, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($client);
            $httpcode = curl_getinfo($client, CURLINFO_HTTP_CODE);
            
           
           
            if($httpcode != 200){
                
                return false;
            }else{
                $result = json_decode($response);
                $access_usertoken = $result->access_token;
                return $access_usertoken;
            }
        }
    }

    function TestAccessToken($access_token){
        $url1 = "https://api.netumo.app/api/Info";
        $client = curl_init($url1);
        curl_setopt($client, CURLOPT_URL, $url1);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
        $headers = array(
        "Accept: application/json",
        "Authorization: Bearer $access_token",
        );
        curl_setopt($client, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($client);
        $result = json_decode($response);
        if(!isset($result)){
            $access_token = NULL;
           return $access_token;
        }else{
            return $access_token;
        } 
        
    }

    // Creating Monitor for a chosen website

    function CreateMonitor($token_access){
        
        if(isset($_POST["submit"])){
            $name = $_POST['webname'];
            $protocol = $_POST['protocol'];
            $urlVar = $_POST['url'];
            $urlVar = "http://netumo.com";
            $email = isset($_POST['emailenabled']) ? $_POST['emailenabled'] : 'false';
            $twitter = isset($_POST['twitterenabled']) ? $_POST['twitterenabled'] :
            'false';
            $slack = isset($_POST['slackenabled']) ? $_POST['slackenabled'] : 'false';
            $microsoftteams = isset($_POST['microsoftteamsenabled'])?
            $_POST['microsoftteamsenabled'] : 'false';
            $telegram = isset($_POST['telegramenabled']) ? $_POST['telegramenabled'] :
            'false';
            if($email == 'on'){
            $email = 'true';
            }
            if($twitter == 'on'){
            $twitter = 'true';
            }
            if($slack == 'on'){
            $slack = 'true';
            }
            if($microsoftteams == 'on'){
            $microsoftteams = 'true';
            }
            if($telegram == 'on'){
            $telegram = 'true';
            }
            $postRequest = array(
            'Name' => $name,
            'Protocol' => $protocol,
            'URL' => $urlVar,
            'EmailEnabled' => $email,
            'TwitterEnabled' => $twitter,
            'SlackEnabled' => $slack,
            'MicrosoftTeamsEnabled' => $microsoftteams,
            'TelegramEnabled' => $telegram,
            'SuppressSuccessEmail' => 'false',
            );
            $url1 = "https://api.netumo.app/api/Website/AddSimpleMonitor";
            $client = curl_init($url1);
            curl_setopt($client, CURLOPT_POSTFIELDS, json_encode($postRequest));
            curl_setopt($client, CURLOPT_RETURNTRANSFER, true);

            $headers = array(
            "Accept: application/json",
            "Content-Type: application/json",
            "Authorization: Bearer $token_access",
            );
            curl_setopt($client, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($client);
            $httpcode = curl_getinfo($client, CURLINFO_HTTP_CODE);
            echo curl_error($client);
            $result = json_decode($response);
            
            if($httpcode !=200){
                return false;
            }else{
                return true;
            }

        }
    }

    // Information/Status about the created Monitor

    function MonitorStatusInfo($name,$token){
       
        //   Monitor Status
        $url1 = "https://api.netumo.app/api/Website";
        $client = curl_init($url1);
        curl_setopt($client, CURLOPT_URL, $url1);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
        $headers = array(
        "Accept: application/json",
        "Authorization: Bearer $token",
        );
        curl_setopt($client, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($client);
        echo curl_error($client);
        $result = json_decode($response);
       
        // $monitor_identification = $result[0]->Id;

        for($i = 0; $i<count($result); $i++){
              if($name == $result[$i]->Name){
                 return (array)$result;
                 break;
              }
        }

    }

    function DeleteMonitor($monitor_id, $tok){
        $url1 = "https://api.netumo.app/api/Website/$monitor_id";
        $client = curl_init($url1);
        curl_setopt($client, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer $tok",
        );
        curl_setopt($client, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($client);
        echo curl_error($client);
        $result = json_decode($response);
      
    }

    // Retrieving status for a particular monitor 

    function GetMonitor($monitor_id, $tok){
        $url1 = "https://api.netumo.app/api/Website/$monitor_id";
        $client = curl_init($url1);
        curl_setopt($client, CURLOPT_URL, $url1);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
        $headers = array(
        "Accept: application/json",
        "Authorization: Bearer $tok",
        );
        curl_setopt($client, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($client);
        $httpcode = curl_getinfo($client, CURLINFO_HTTP_CODE);
        echo curl_error($client);
        $result = json_decode($response);
        
        if($httpcode !=200){
            return true;
        }else{
            return false;
        }
               
    }
}

?>