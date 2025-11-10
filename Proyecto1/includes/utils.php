<?php

function hash_password($password) {
   
    return password_hash($password, PASSWORD_DEFAULT);
}


function check_password($password, $hash) {
  
    return password_verify($password, $hash);
}
?>