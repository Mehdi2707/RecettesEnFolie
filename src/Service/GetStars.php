<?php

namespace App\Service;

class GetStars
{
    public function getStars($notes): array
    {
        $totalNote = 0;
        $nbNote = 0;

        foreach($notes as $note)
        {
            $totalNote = $totalNote + $note->getValue();
            $nbNote++;
        }

        if(count($notes) == 0)
            $moyenne = 0;
        else
            $moyenne = round($totalNote / $nbNote, 1);

        $noteRounded = floor($moyenne);
        $hasHalfStar = false;
        $decimal = '' . ($moyenne - $noteRounded);

        if($decimal == 0.3 || $decimal == 0.4 || $decimal == 0.5 || $decimal == 0.6 || $decimal == 0.7)
            $hasHalfStar = true;

        if($decimal == 0.8 || $decimal == 0.9)
            $noteRounded++;

        return [$noteRounded, $hasHalfStar];
    }
}