<?php

    function makeImageFromUserName($name) {
        $userImage = "";
        $shortName = "";

        $usernames = str_replace(" ","", str_split($name, 6));
        foreach($usernames as $uname) {
            $shortName .= $uname[0];
        }

        return "<div class='name-image bg-primary'><p class='mt-1'>". $shortName ."</p></div>";
    }