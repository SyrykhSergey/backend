<?php

include_once "headerUser/headerUs.php";
include_once "headers/setHTTPStatus.php";


function route($method, $urlList, $requestData, $link)
{
    $nameTopic = $requestData->parameters["take"];
    $parentId = $requestData->parameters["skip"];
    switch ($method) {
        case "GET":
        {

            if (is_numeric($urlList[1]))
            {
                echo json_encode(getMovies($urlList[1], $link));
                return;
            }
            else if(is_numeric($urlList[2])){
                echo json_encode(getByIdMovWithRev($urlList[2],$link));
                exit();
            }
            else {
                
                echo('{"message" : "Uncorrected GET path"}');
                return;
            }
           
        }

        case "POST":
        {
            switch (count($urlList)) {
                
                case 4:
                { 
                    if ($urlList[2] == "review" && is_numeric($urlList[1]) && $urlList[3] = 'add' )
                    {
                        if (empty(getallheaders()['Authorization'])) {
                            setHTTPStatus("403","Authorization token are invalid");
                            exit();
                        } else {
                            setHTTPStatus();
                            $token = substr(getallheaders()['Authorization'], 7);
                            echo json_encode(postReview((int)$urlList[1], $link, $requestData, $token));
                            return;
                        } 
                    }
                   
                }


                default:
                {
                    echo('{"message" : "Uncorrected GET path"}');
                    return;
                }
            }



        }

        case "PUT":
            {
                if ($urlList[2] == "review" && is_numeric($urlList[1]) && $urlList[4] = 'edit' )
                {
                    if (empty(getallheaders()['Authorization'])) {
                        setHTTPStatus("403","Authorization token are invalid");
                        exit();
                    } else {
                        setHTTPStatus();
                        $token = substr(getallheaders()['Authorization'], 7);
                        echo json_encode(editReview((int)$urlList[1], $urlList[3],  $link, $requestData, $token));
                        return;
                    } 
                }  
            }

        case "DELETE":
        {
            if ($urlList[2] == "review" && is_numeric($urlList[1]) && $urlList[4] = 'delete' )
            {
                if (empty(getallheaders()['Authorization'])) {
                    setHTTPStatus("403","Authorization token are invalid");
                    exit();
                } else {
                    setHTTPStatus();
                    $token = substr(getallheaders()['Authorization'], 7);
                    echo json_encode(deleteReview((int)$urlList[1], $urlList[3], $link, $requestData, $token));
                    return;
                } 
            }
        }
        default:
        {
            echo('{"message" : "Uncorrected GET path"}');
            return;
        }
    }
}


function deleteRev($idMov,$revId, $link)
{
    $res = $link->query("DELETE FROM reviews WHERE movId = $idMov AND revId = $revId");
    if (!$res) //SQL
    {
        echo $link->error;
        exit();
        setHTTPStatus("500","Unexpected error");
        exit;
    } else {
        echo("OK");
        exit();
    }
}

function postReview($id, $link, $requestData, $token)
{

    $reviewText = $requestData->body->reviewText;
    $rating = $requestData->body->rating;
    $isAnonymous = $requestData->body->isAnonymous;

    $res = $link->query("SELECT UserId,username,email,name,birthDate,gender FROM account WHERE `token` = '$token'");
    if (!$res) //SQL
    {
        setHTTPStatus("500", "Unexpected error");
    } else {
        $row = $res->fetch_assoc();
        $UserId = $row['UserId'];
    }
    
    $res = $link->query("INSERT INTO reviews(`ReviewText`, `Rating`, `isAnonymous`,`MovieId`,`UserId`,`Date`) VALUES ('$reviewText', '$rating',' $isAnonymous',$id, $UserId, '123')");
    if (!$res) {
        setHTTPStatus("405", "Unexpected error");
        exit();
    }
    else{
        setHTTPStatus("200","OK");
        exit();
    }
}
function editReview($MovieId, $id, $link, $requestData, $token)
{

    $reviewText = $requestData->body->reviewText;
    $rating = $requestData->body->rating;
    $isAnonymous = $requestData->body->isAnonymous;

    $res = $link->query("SELECT UserId,username,email,name,birthDate,gender FROM account WHERE `token` = '$token'");
    if (!$res) //SQL
    {
        setHTTPStatus("500", "Unexpected error");
    } else {
        $row = $res->fetch_assoc();
        $UserId = $row['UserId'];
    }
    
    $res = $link->query("UPDATE `reviews` SET `ReviewText` = '$reviewText', `Rating`= '$rating', `isAnonymous`='$isAnonymous', `Date`=''  WHERE `ReviewId` = '$id' and MovieId = $MovieId ");
    if (!$res) {
        setHTTPStatus("405", "Unexpected error");
        exit();
    }
    else{
        setHTTPStatus("200","OK");
        exit();
    }
}
function deleteReview($MovieId, $id, $link, $requestData, $token)
{
    $res = $link->query("SELECT UserId,username,email,name,birthDate,gender FROM account WHERE `token` = '$token'");
    if (!$res) //SQL
    {
        setHTTPStatus("500", "Unexpected error");
    } else {
        $row = $res->fetch_assoc();
        $UserId = $row['UserId'];
    }

    if($UserId == null)
{
    setHTTPStatus("401", "Unauthorise");

}

    $res = $link->query("DELETE FROM reviews WHERE ReviewId = $id");
    if (!$res) {
        setHTTPStatus("405", "Unexpected error");
        exit();
    }
    else{
        setHTTPStatus("200","OK");
        exit();
    }
}





function getByIdMovWithRev($id, $link)
{
    $message = [];
    $message["movBD"] = [];
    $message["movBD"]["movies"] = [];
    $moves = getAllMoviesId($id, $link);
    $genres = getAllGenresId($id, $link);
    $reviews = getAllReviewsId($id, $link);



    foreach ($moves as $value) {
        if ($value["MovieId"] == $id) {
            $Answgenres = [];
            $Answgenres["genres"] = [];
            foreach ($genres as $k) {
                
                    $Answgenres["genres"][] = [
                        "id" => $k['GenreId'],
                        "name" => $k['Name'],
                    ];
                
            }
            $Answreviews = [];
            $Answreviews["reviews"] = [];
            foreach ($reviews as $k) {
                

                    $chU = $k['UserId'];

                    $res = $link->query("SELECT UserId,name FROM account WHERE UserId = $chU");
                    while ($row = $res->fetch_assoc()) {
                        $mas["UserId"] = $row["UserId"];
                        $mas["name"] = $row["name"];
                    }

                    $Answreviews["reviews"][] = [
                        "id" => $k['ReviewId'],
                        "rating" => $k['Rating'],
                        "reviewText" => $k['ReviewText'],
                        "isAnonymous" => $k['isAnonymous'],
                        "createDateTime" => $k['Date'],
                        "author" => $mas,
                    ];
                
            }
            $message["movBD"]["movies"][] = [
                "id" => $value['MovieId'],
                "name" => $value['Name'],
                "poster" => $value['Poster'],
                "year" => $value['Year'],
                "country" => $value['Country'],
                "genres" => $Answgenres['genres'],
                "reviews" => $Answreviews['reviews'],
                "time" => $value['Time'],
                "tagline" => $value['Tagline'],
                "description" => $value['Description'],
                "director" => $value['Director'],
                "budget" => $value['Budget'],
                "fees" => $value['Feel'],
                "ageLimit" => $value['AgeLimit'],

            ];
        }

       
    }
    setHTTPStatus();
    return $message["movBD"]["movies"][0];
}







function getMovies($page, $link)
{
    $message = [];
    $message["movBD"] = [];
    $message["movBD"]["movies"] = [];
    $moves = getAllMoviespage($page, $link);
    $genres = getAllGenres($link);
    $reviews = getAllReviews($link);
    foreach ($moves as $value) {
        $Answgenres = [];
        $Answgenres["genres"] = [];
        foreach ($genres as $k) {
            if ($value['MovieId'] == $k['MovieId']) {
                $Answgenres["genres"][] = [
                    "id" => $k['GenreId'],
                    "name" => $k['Name'],
                ];
            }
        }
        $Answreviews = [];
        $Answreviews["reviews"] = [];
        foreach ($reviews as $k) {
            if ($value['MovieId'] == $k['MovieId']) {
                $Answreviews["reviews"][] = [
                    "Id" => $k['ReviewId'],
                    "Rating" => $k['Rating'],
                ];
            }
        }
        $message["movBD"]["movies"][] = [
            "id" => $value['MovieId'],
            "name" => $value['Name'],
            "poster" => $value['Poster'],
            "year" => $value['Year'],
            "country" => $value['Country'],
            "genres" => $Answgenres['genres'],
            "reviews" => $Answreviews['reviews'],
            "pageInfo" => [
                "pageSize" => $value['pageSize'],
                "nextSkip" => $value['nextSkip'],
                "pageCount" => $value['pageCount'],
    
            ]
        ];
       
    }
    setHTTPStatus();
    return $message["movBD"];
}

function getAllReviews($link)
{
    $mas = [];
    $res = $link->query("SELECT * FROM reviews ORDER BY ReviewId ASC");
    while ($row = $res->fetch_assoc()) {
        $mas[] = $row;
    }
    return $mas;
}
function getAllReviewsId($Id, $link)
{
    $mas = [];
    $res = $link->query("SELECT * FROM reviews WHERE MovieId = $Id");
    while ($row = $res->fetch_assoc()) {
        $mas[] = $row;
    }
    return $mas;
}

function getAllGenres($link)
{
    $mas = [];
    $res = $link->query("SELECT * FROM genres ORDER BY GenreId ASC");
    while ($row = $res->fetch_assoc()) {
        $mas[] = $row;
    }
    return $mas;
}

function getAllGenresId($Id, $link)
{
    $mas = [];
    $res = $link->query("SELECT * FROM genres WHERE MovieId = $Id");
    while ($row = $res->fetch_assoc()) {
        $mas[] = $row;
    }
    return $mas;
}



function getAllMoviespage($page, $link)
{
    $startmovie = ($page * 6) - 6;
    $endmovie = $page * 6;
    $mas = [];
    $res = $link->query("SELECT * FROM movies ORDER BY MovieId ASC limit 6 OFFSET $startmovie ");
    while ($row = $res->fetch_assoc()) {
        $mas[] = $row;
    }
    return $mas;
}

function getAllMoviesId($id, $link)
{
    $mas = [];
    $res = $link->query("SELECT * FROM movies WHERE MovieId = $id");
    while ($row = $res->fetch_assoc()) {
        $mas[] = $row;
    }
    return $mas;
}
function getAllFav($link)
{
    $mas = [];
    $res = $link->query("SELECT * FROM favorites ORDER BY IdMov ASC");
    while ($row = $res->fetch_assoc()) {
        $mas[] = $row;
    }
    return $mas;
}

function getAllFavPerson($id , $link)
{
    $mas = [];
    $res = $link->query("SELECT * FROM favorites WHERE author = $id");
    while ($row = $res->fetch_assoc()) {
        $mas[] = $row;
    }
    return $mas;
}