<?php

include_once 'headerUser/headerUs.php';
include_once 'headers/setHTTPStatus.php';

function route($method, $urlList, $requestData, $link)
{

    switch ($method) {
        case "GET":
        {
            if ($urlList[1] == "profile")
            {
                
                if (empty(getallheaders()['Authorization'])) {
                    setHTTPStatus("403","Authorization token are invalid");
                    exit();
                } 
                else {
                   
                    $token = substr(getallheaders()['Authorization'], 7);
                    echo json_encode(getProfileID($token, $link));
                    exit();
                }
                return;
            }
        }
        case "POST":
        {
            if ($urlList[1] === "register")
            {
                if (!empty($requestData->body->password) && !empty($requestData->body->name) && !empty($requestData->body->userName) && !empty($requestData->body->email) && !empty($requestData->body->birthDate)) {
                    register($link, $requestData);
                } else {
                    setHTTPStatus("403", "Strange data");
                }
            }
            if ($urlList[1] === "login")
            {
                if (!empty($requestData->body->password) && !empty($requestData->body->username)) {
                    login($link, $requestData);
                } else {
                    setHTTPStatus("403", "Strange data");
                }
            }
            
            if ($urlList[1] === "logout")
            {
                $token = substr(getallheaders()['Authorization'], 7);
                logout($token, $link);
                break;
            }
        }
        
        case "PUT":
        {
            if ($urlList[1] == "profile")
            {
                
                if (empty(getallheaders()['Authorization'])) {
                    setHTTPStatus("403","Authorization token are invalid");
                    exit();
                } 
                else {
                   
                    if (!empty($requestData->body->name) && !empty($requestData->body->nickName) && !empty($requestData->body->email) && !empty($requestData->body->birthDate)) {
                        $token = substr(getallheaders()['Authorization'], 7);
                        json_encode(putProfile($token, $link, $requestData));
                    } else {
                        setHTTPStatus("403", "Strange data");
                    }

                    
                    exit();
                }
                return;
            }
        }
        
        default:
        {
            echo('{"message" : "Uncorrected GET path"}');
            return;
        }
    }



}

function getProfileID($token, $link)
{
    if (empty($token))
    {
        setHTTPStatus("400","Strange data");
        exit;
    }

    $message = [];
    $message["users"] = [];
    
    $res = $link->query("SELECT UserId,username,email,name,birthDate,gender FROM account WHERE `token` = '$token'");
    if (!$res) //SQL
    {
        setHTTPStatus("500", "Unexpected error");
    } else {
        while ($row = $res->fetch_assoc()) {
            $message["users"][] = [
                "UserId" => $row['UserId'],
                "username" => $row['username'],
                "email" => $row['email'],
                "name" => $row['name'],
                "birthDate" => $row['birthDate'],
                "gender" => $row['gender']
            ];
        }
    }
    if (empty($message["users"][0]))
    {
        setHTTPStatus("401", "Unauthorise");
        exit();
    }
    setHTTPStatus();
    return $message["users"][0];
}

function putProfile($token, $link, $requestData)
{
    if (empty($token))
    {
        setHTTPStatus("400","Strange data");
        exit;
    }
    
    $name = $requestData->body->name;
    $email = $requestData->body->email;
    $gender = $requestData->body->gender;
    $username = $requestData->body->nickName;
    $birthDate = $requestData->body->birthDate;
    if($gender == 1)
    {
        $gender = 'male';
    }
    else {
        $gender = 'female';
    }

    $message = [];
    $message["users"] = [];
    
    $res = $link->query("UPDATE `account` SET `username`= '$username', `name` = '$name', `password` = '$pwd', `email`= '$email',`birthDate` = '$birthDate',`gender`='$gender' WHERE `token` = '$token'");
    if (!$res) //SQL
    {
        setHTTPStatus("500", "Unexpected error");
    } else 
    {
        setHTTPStatus("200","OK");
        }
    
    
}


function register($link, $requestData)
{
    $pwd = hash("sha1", $requestData->body->password);
    $token = (bin2hex(random_bytes(10)));
    $name = $requestData->body->name;
    $email = $requestData->body->email;
    $gender = $requestData->body->gender;
    $username = $requestData->body->userName;
    $birthDate = $requestData->body->birthDate;
    if($gender == 1)
    {
        $gender = 'male';
    }
    else {
        $gender = 'female';
    }
    $res = $link->query("INSERT INTO account(`username`, `name`, `password`, `email`,`birthDate`,`gender`,`token`) VALUES ('$username', '$name',' $pwd', '$email','$birthDate', '$gender', '$token')");
    
    if (!$res) {
        echo "Не удалось выполнить запрос: (" . $link->errno . ") " . $mysqli->error;
        setHTTPStatus("405", "Unexpected error");
        exit();
    }
    else{
        setHTTPStatus();
        echo json_encode(["token" => $token]);
        exit();
    }
}

function login($link, $requestData)
{
    $pwd = $requestData->body->password;
    $username = $requestData->body->username;
    $pwd = hash("sha1", $pwd);
    $res = $link->query("SELECT `token` FROM `account` WHERE username = '$username' AND `password` = '$pwd'");
    if (!$res) //SQL
    {
        setHTTPStatus("500", "Unexpected error");
        exit;
    }
    else
    {
        setHTTPStatus();
        $res = $res->fetch_assoc();
        $token = $res["token"];
        
        if($token != null)
        {
            echo json_encode(["token" => $token]);
        }
        else {
            setHTTPStatus('401', 'uncorrect username or password');
        }
        
        exit();
    }
}



function logout($token, $link)
{
    
    if (!empty($token))
    {
        $userId = checkTokenIsThereInTable($token,$link);
        if ($userId != null)
        {
            $token = (bin2hex(random_bytes(10)));
            $res = $link->query("UPDATE account SET token='$token' WHERE UserId = $userId");
            if (!$res) //SQL
            {
                setHTTPStatus("500","Unexpected error");
                exit();
            }
            else
            {
                setHTTPStatus("200","OK");
            }
        }
        else
        {
            setHTTPStatus("403","User already logged out");
            exit();
        }
    }
    else
    {
        setHTTPStatus("400","Strange data");
        exit();
    }
}


function checkTokenIsThereInTable($token, $link)
{
    $res = $link->query("SELECT `UserId` FROM `account` WHERE token = '$token'");
    if (!$res) //SQL
    {
        echo "Не удалось выполнить запрос: (" . $link->errno . ") " . $link->error;
    }
    else
    {
        if ($link->affected_rows == 0 || $link->affected_rows == -1) {
            return null;
        } else {
            return $res->fetch_assoc()['UserId'];
        }
    }
}