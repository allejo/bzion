<?php

interface EloInterface
{
    public function getElo();

    public function adjustElo($adjust, Match $match);
}
