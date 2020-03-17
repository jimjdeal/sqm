<?php

require '../src/SqmParser.php';
require '../src/Sqm.php';
require '../src/SqmFragment.php';

$rustart = getrusage();

$parser = SqmParser::getFromFile('./mission.sqm');
$sqm = $parser->parse();

echo '<pre>';
print_r($sqm->getAuthor());
echo '<br><br><br>';
print_r($sqm->getSquads('West'));
echo '<br><br><br>';
print_r($sqm);

function rutime($ru, $rus, $index) {
    return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
        -  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
}

$ru = getrusage();
echo "This process used " . rutime($ru, $rustart, "utime") .
    " ms for its computations\n";
echo "It spent " . rutime($ru, $rustart, "stime") .
    " ms in system calls\n";