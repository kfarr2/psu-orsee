<?php

/* Pass in a url, and it will return the contents, or raises an exception if something doesn't go right */
function get_url($url, $http_auth=NULL){
    // Initialize session and set URL.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if($http_auth !== null){
        curl_setopt($ch, CURLOPT_USERPWD, $http_auth);
    }
    // Set so curl_exec returns the result instead of outputting it.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Get the response and close the channel.
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if($error){
        throw new Exception($error);
    }
    return $response;
}

/* Pass in the url you'd like to redirect to after a successful login */
function cas_authenticate($url, $conn, $ticket=NULL){

    // Case 0: No ticket. Go to CAS.
    if(!$ticket){
        header("Location: https://sso.pdx.edu/cas/login?service=".$url);
        exit();
    }
    // Case 1: Just got back from CAS. Verify and send to menu.
    $link = "https://sso.pdx.edu/cas/proxyValidate?ticket=".$ticket."&service=".$url;
    $cas_username = get_url($link, NULL);
    if(strpos($cas_username, "cas:authenticationFailure") !== false){
        exit("Your CAS ticket was not valid");
    }
    // Assign user-specific variables
    $matches = array();
    preg_match("#<cas:UID>(.*?)</cas:UID>#", $cas_username, $matches);
    $user = $matches[1];
    $email = $user."@pdx.edu";
    $username = array();
    preg_match("#<cas:DISPLAY_NAME>(.*?)</cas:DISPLAY_NAME>#", $cas_username, $username);
    $tokens = explode(" ", $username[1]);

    // Query the database
    $SQL = "SELECT * FROM or_participants WHERE Email = ?";
    $query = $conn->prepare($SQL);
    $query->bindParam(1, $email, PDO::PARAM_STR);
    $query->execute();
    $row = $query->fetch();

    // this user doesn't exist. Add to the database.
    if(!$row){
        // Assign some variables.
        $now = date('Ymd');
        $pending_update = 'y';
        $language = "en";
        $status_id = 0;
        $subscriptions = "|1|,|2|"; // students get subscribed to everything
        $new_id = participant__create_participant_id($tokens);
        // Prepare the SQL statement
        $SQL = 'INSERT INTO or_participants (
                        subpool_id,
                        subscriptions,
                        rules_signed,
                        status_id,
                        pending_profile_update_request,
                        language,
                        email,
                        fname,
                        lname,
                        last_activity,
                        confirmation_token,
                        participant_id,
                        participant_id_crypt
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)';

        // Bind the params
        $result = $conn->prepare($SQL);
        $result->bindParam(1, $_SESSION['subpool_id'], PDO::PARAM_INT);
        $result->bindParam(2, $subscriptions, PDO::PARAM_STR);
        $result->bindParam(3, $_SESSION['rules'], PDO::PARAM_STR);
        $result->bindParam(4, $status_id, PDO::PARAM_INT);
        $result->bindParam(5, $pending_update, PDO::PARAM_STR);
        $result->bindParam(6, $language, PDO::PARAM_STR);
        $result->bindParam(7, $email, PDO::PARAM_STR);
        $result->bindParam(8, $tokens[0], PDO::PARAM_STR);
        $result->bindParam(9, $tokens[1], PDO::PARAM_STR);
        $result->bindParam(10, $now, PDO::PARAM_STR);
        $result->bindParam(11, create_random_token(get_entropy($tokens)), PDO::PARAM_STR);
        $result->bindParam(12, $new_id['participant_id'], PDO::PARAM_STR);
        $result->bindParam(13, $new_id['participant_id_crypt'], PDO::PARAM_STR);
        $outcome = $result->execute();
        // DB operation failed somehow
        if(!$outcome){
            exit("An error occurred. Please try again later or contact the system administrator if the issue persists.");
        }
        // Get the freshly added row from the database and send the confirmation email.
        $query->execute();
        $row = $query->fetch();
        experimentmail__confirmation_mail($row);
    }

    // Error if inactive user
    if($row['locked'] == 1) exit("User is inactive. Please contact the system administrator.");

    // User exists in our DB, so just use that one.
    setcookie("cookieUserName", $user, time()+60*60*24*365);
    return $row;
}

?>
