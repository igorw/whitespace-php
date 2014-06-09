<?php

namespace igorw\whitespace;

// whitespace is a dependently spaced lanuage
// created by edwin brady and chris morris in 2003

// in order to allow consuming single chars,
// make sure to run with:
//   system("stty -icanon");
//
// source: http://stackoverflow.com/a/3684565/289985

function parse(array $input) {
    $instructions = [
        // stack
        ["  ", 'push', 'num'],
        [" \n ", 'dup', null],
        [" \t ", 'copy', 'num'],
        [" \n\t", 'swap', null],
        [" \n\n", 'drop', null],
        [" \t\n", 'slide', 'num'],

        // arithmetic
        ["\t   ", 'add', null],
        ["\t  \t", 'sub', null],
        ["\t  \n", 'mul', null],
        ["\t \t ", 'div', null],
        ["\t \t\t", 'mod', null],

        // heap
        ["\t\t ", 'store', null],
        ["\t\t\t", 'retrieve', null],

        // flow
        ["\n  ", 'label', 'label'],
        ["\n \t", 'call', 'label'],
        ["\n \n", 'jump', 'label'],
        ["\n\t ", 'jumpz', 'label'],
        ["\n\t\t", 'jumplz', 'label'],
        ["\n\t\n", 'ret', null],
        ["\n\n\n", 'exit', null],

        // i/o
        ["\t\n  ", 'write_char', null],
        ["\t\n \t", 'write_num', null],
        ["\t\n\t ", 'read_char', null],
        ["\t\n\t\t", 'read_num', null],
    ];

    $code = [];
    while (count($input) > 0) {
        $matched = null;
        foreach ($instructions as list($prefix, $inst, $arg)) {
            if (0 === strpos(implode('', $input), $prefix)) {
                $matched = [$prefix, $inst, $arg];
                break;
            }
        }

        if (!$matched) {
            throw new \InvalidArgumentException('Could not find corresponding instruction.');
        }

        list($prefix, $inst, $arg) = $matched;
        $input = array_slice($input, strlen($prefix));

        $parsed_arg = null;
        if ($arg) {
            // space = positive, tab = negative
            $sign = array_shift($input) === ' ' ? 1 : -1;
            $digits = '';
            while ("\n" !== $char = array_shift($input)) {
                // space = 0, tab = 1, \n separates
                $digits .= ($char === ' ') ? '0' : '1';
            }
            $parsed_num = $sign * base_convert($digits, 2, 10);
            $parsed_arg = ($arg === 'label') ? chr($parsed_num) : $parsed_num;
        }

        $code[] = [$inst, $parsed_arg];
    }
    return $code;
}

function evaluate(array $code) {
    $labels = [];
    foreach ($code as $i => list($inst, $arg)) {
        if ($inst === 'label') {
            $labels[$arg] = $i;
        }
    }

    $ip = 0;
    $stack = new \SplStack();
    $heap = [];

    $running = true;
    while ($running) {
        list($inst, $arg) = $code[$ip++];
        switch ($inst) {
            case 'push':
                $stack->push($arg);
                break;
            case 'dup':
                $stack->push($stack->top());
                break;
            case 'copy':
                $stack->push($stack[count($stack) - $arg]);
                break;
            case 'swap':
                $b = $stack->pop();
                $a = $stack->pop();
                $stack->push($b);
                $stack->push($a);
                break;
            case 'drop':
                $stack->pop();
                break;
            case 'slide':
                $top = $stack->pop();
                for ($i = 0; $i < $arg; $i++) {
                    $stack->pop();
                }
                $stack->push($top);
                break;
            case 'add':
                $b = $stack->pop();
                $a = $stack->pop();
                $stack->push($a + $b);
                break;
            case 'sub':
                $b = $stack->pop();
                $a = $stack->pop();
                $stack->push($a - $b);
                break;
            case 'mul':
                $b = $stack->pop();
                $a = $stack->pop();
                $stack->push($a * $b);
                break;
            case 'div':
                $b = $stack->pop();
                $a = $stack->pop();
                $stack->push($a / $b);
                break;
            case 'mod':
                $b = $stack->pop();
                $a = $stack->pop();
                $stack->push($a % $b);
                break;
            case 'store':
                $addr = $stack->pop();
                $value = $stack->pop();
                $heap[$addr] = $value;
                break;
            case 'retrieve':
                $addr = $stack->pop();
                $stack->push($heap[$addr]);
                break;
            case 'label':
                // noop, labels are pre-processed
                break;
            case 'call':
                $stack->push($ip);
                $ip = $labels[$arg];
                break;
            case 'jump':
                $ip = $labels[$arg];
                break;
            case 'jumpz':
                if ($stack->pop() === 0) {
                    $ip = $labels[$arg];
                }
                break;
            case 'jumplz':
                if ($stack->pop() < 0) {
                    $ip = $labels[$arg];
                }
                break;
            case 'ret':
                $ip = $stack->pop();
                break;
            case 'exit':
                $running = false;
                break;
            case 'write_char':
                echo chr($stack->pop());
                break;
            case 'write_num':
                echo $stack->pop();
                break;
            case 'read_char':
                $char = fread(STDIN, 1);
                $stack->push(ord($char));
                break;
            case 'read_num':
                $num = (int) trim(fgets(STDIN));
                $stack->push($num);
                break;
            default:
                throw new \InvalidArgumentException("Instruction $inst not implemented.");
                break;
        }
    }

    return iterator_to_array($stack);
}
