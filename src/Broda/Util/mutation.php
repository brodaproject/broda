<?php

// from https://gist.github.com/igorw/4316810f8875f7e18b24

$data = <<<'DATA'
$program = <<<PROGRAM
<?php
\$data = <<<'DATA'\n$data\nDATA;
$data

PROGRAM;
$n = 0;
if ($n >= 5) {
echo "OMFG!\n";
} else {
echo str_replace('$n = 0;', '$n = '.($n+1).';', $program);
}
DATA;
$program = <<<PROGRAM
<?php
\$data = <<<'DATA'\n$data\nDATA;
$data

PROGRAM;
$n = 0;
if ($n >= 5) {
echo "OMFG!\n";
} else {
echo str_replace('$n = 0;', '$n = '.($n+1).';', $program);
}