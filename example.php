<?php

require 'src/whitespace.php';

use igorw\whitespace as w;

// example program from the whitespace documentation
// counts to 10

$input = [
    // push 1
    " ", " ", " ", "\t", "\n",
    // set a label
    "\n", " ", " ", " ", "\t", " ", " ", " ", " ", "\t", "\t", "\n",
    // dup top element
    " ", "\n", " ",
    // output the current value
    "\t", "\n", " ", "\t",
    // push 10 (newline)
    " ", " ", " ", "\t", " ", "\t", " ", "\n",
    // output the newline
    "\t", "\n", " ", " ",
    // push 1
    " ", " ", " ", "\t", "\n",
    // add
    "\t", " ", " ", " ",
    // dup for test
    " ", "\n", " ",
    // push 11
    " ", " ", " ", "\t", " ", "\t", "\t", "\n",
    // sub
    "\t", " ", " ", "\t",
    // zero, so jump to end
    "\n", "\t", " ", " ", "\t", " ", " ", " ", "\t", " ", "\t", "\n",
    // jump to start
    "\n", " ", "\n", " ", "\t", " ", " ", " ", " ", "\t", "\t", "\n",
    // set end label
    "\n", " ", " ", " ", "\t", " ", " ", " ", "\t", " ", "\t", "\n",
    // discard accumulator
    " ", "\n", "\n",
    // exit
    "\n", "\n", "\n",
];

w\evaluate(w\parse($input));
