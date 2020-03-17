<?php

class Sqm
{
    public function getAuthor()
    {
        return $this->ScenarioData->author;
    }

    public function getSquads(string $side = null)
    {
        $squads = [];
        foreach($this->Mission->Entities as $entitiy) {
            if(is_object($entitiy) && $entitiy->dataType == "Group") {
                if($side && $entitiy->side != $side) continue;
                $squads[] = $entitiy;
            }
        }
        return $squads;
    }
}