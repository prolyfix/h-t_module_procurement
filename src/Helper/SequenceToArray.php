<?php
namespace Prolyfix\ProcurementBundle\Helper;

class SequenceToArray
{
    public static function sequenceToArray($sequence, $array = [])
    {
        foreach($sequence->getSequenceEntries() as $sequenceEntry){
            if($sequenceEntry->getNumber() == null)
                continue;
            if( array_key_exists($sequenceEntry->getNumber()->getNumber(), $array))
                $array[$sequenceEntry->getNumber()->getNumber()] = [];
            $array[$sequenceEntry->getNumber()->getNumber()][] = $sequenceEntry;
        }
        return $array;
    }
}