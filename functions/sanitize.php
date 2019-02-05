<?php

function escapa($string){
    return htmlentities($string, ENT_QUOTES, "UTF-8");
}

?>
