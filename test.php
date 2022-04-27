<?php

Class tutu extends Dbh
{
    public function tat()
    {
        $this->connect()->query("SELECT * FROM articles");
    }
}

