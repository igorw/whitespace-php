<?php

namespace igorw\whitespace;

// whitespace is a dependently spaced language
// created by edwin brady and chris morris in 2003
//
// http://compsoc.dur.ac.uk/whitespace/

// in order to allow consuming single chars,
// make sure to run with:
//   system("stty -icanon");
//
// source: http://stackoverflow.com/a/3684565/289985

function parse(array $input) {
    $instructions = [
        // stack
        ["  ", 'push', 'signed'],
        [" \n ", 'dup', null],
        [" \t ", 'ref', 'signed'],
        [" \n\t", 'swap', null],
        [" \n\n", 'discard', null],
        [" \t\n", 'slide', 'signed'],

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
        ["\n  ", 'label', 'unsigned'],
        ["\n \t", 'call', 'unsigned'],
        ["\n \n", 'jump', 'unsigned'],
        ["\n\t ", 'jumpz', 'unsigned'],
        ["\n\t\t", 'jumplz', 'unsigned'],
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
            throw new \InvalidArgumentException("Could not find corresponding instruction for input ".json_encode($input).".");
        }

        list($prefix, $inst, $arg) = $matched;
        $input = array_slice($input, strlen($prefix));

        $parsed_arg = null;
        if ($arg === 'signed') {
            // space = positive, tab = negative
            $sign = array_shift($input) === ' ' ? 1 : -1;
            $digits = '';
            while ("\n" !== $char = array_shift($input)) {
                // space = 0, tab = 1, \n separates
                $digits .= ($char === ' ') ? '0' : '1';
            }
            $parsed_arg = $sign * base_convert($digits, 2, 10);
        } else if ($arg === 'unsigned') {
            $digits = '';
            while ("\n" !== $char = array_shift($input)) {
                // space = 0, tab = 1, \n separates
                $digits .= ($char === ' ') ? '0' : '1';
            }
            $parsed_arg = base_convert($digits, 2, 10);
        }

        $code[] = [$inst, $parsed_arg];
    }
    return $code;
}

function evaluate(array $code, $options = []) {
    $labels = [];
    foreach ($code as $i => list($inst, $arg)) {
        // disallow duplicate labels -- pick first match
        if ($inst === 'label' && !isset($labels[$arg])) {
            $labels[$arg] = $i;
        }
    }

    $ip = 0;
    $stack = new \SplStack();
    $calls = new \SplStack();
    $heap = [];

    $running = true;
    while ($running) {
        list($inst, $arg) = $code[$ip++];

        if (isset($options['debug']) && $options['debug']) {
            echo 'inst: '.json_encode([$inst, $arg])."\n";
            echo 'stack: '.json_encode(array_reverse(array_values(iterator_to_array($stack))))."\n";
            echo 'heap: '.json_encode($heap)."\n";
            echo 'calls: '.json_encode(array_reverse(array_values(iterator_to_array($calls))))."\n";
            echo "---\n";
        }

        switch ($inst) {
            case 'push':
                $stack->push($arg);
                break;
            case 'dup':
                $stack->push($stack->top());
                break;
            case 'ref':
                $stack->push($stack[count($stack) - $arg]);
                break;
            case 'swap':
                $b = $stack->pop();
                $a = $stack->pop();
                $stack->push($b);
                $stack->push($a);
                break;
            case 'discard':
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
                $stack->push(intval($a + $b));
                break;
            case 'sub':
                $b = $stack->pop();
                $a = $stack->pop();
                $stack->push(intval($a - $b));
                break;
            case 'mul':
                $b = $stack->pop();
                $a = $stack->pop();
                $stack->push(intval($a * $b));
                break;
            case 'div':
                $b = $stack->pop();
                $a = $stack->pop();
                $stack->push(intval($a / $b));
                break;
            case 'mod':
                $b = $stack->pop();
                $a = $stack->pop();
                $stack->push(intval($a % $b));
                break;
            case 'store':
                $value = $stack->pop();
                $addr = $stack->pop();
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
                $calls->push($ip);
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
                $ip = $calls->pop();
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
                $addr = $stack->pop();
                $char = fread(STDIN, 1);
                $heap[$addr] = ord($char);
                break;
            case 'read_num':
                $addr = $stack->pop();
                $num = (int) trim(fgets(STDIN));
                $heap[$addr] = $num;
                break;
            default:
                throw new \InvalidArgumentException("Instruction $inst not implemented.");
                break;
        }
    }

    return array_reverse(array_values(iterator_to_array($stack)));
}
