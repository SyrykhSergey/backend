<?php

function checkAdmin($link)
{
    $token = substr(getallheaders()['Authorization'], 7);
    $res = $link->query("SELECT `permis` FROM `account` WHERE token = '$token'")->fetch_assoc();
    if ($link->affected_rows == 0 || $link->affected_rows == -1) {
        return false;
    } else {
        if ($res["permis"] == 2)
        {
            return true;
        }
    }
}


function checkInformationAboutMe($link,$id)
{
    $token = substr(getallheaders()['Authorization'], 7);
    $res = $link->query("SELECT userId FROM `account` WHERE token = '$token'")->fetch_assoc();
    $tokenId = $res["userId"];
    if ($id == $tokenId)
    {
        return true;
    }
    else{
        return false;
    }
}


function checkHaveUser($link)
{
    $token = substr(getallheaders()['Authorization'], 7);
    $res = $link->query("SELECT `userId` FROM `account` WHERE token = '$token'")->fetch_assoc();
    if ($link->affected_rows == 0 || $link->affected_rows == -1) {
        return false;
    } else {
        return true;
    }
}
function checkHaveUserRetId($link)
{
    $token = substr(getallheaders()['Authorization'], 7);
    $res = $link->query("SELECT `userId` FROM `account` WHERE token = '$token'")->fetch_assoc();
    if ($link->affected_rows == 0 || $link->affected_rows == -1) {
        return false;
    } else {
        return $res["userId"];
    }
}