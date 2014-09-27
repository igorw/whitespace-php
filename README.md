![logo](doc/decision.png)

# whitespace-php

Don't you hate programming in PHP? Me too! Well luckily, now you don't have
to!

Introducing whitespace! A dependently spaced language! Running inside of your
PHP interpreter!

Remember all of those discussions about tabs vs spaces? Never again!
Whitespace gives you both tabs **and** spaces! And newlines, too!

And guess what?! A lot of existing PHP programs are **already** valid
whitespace programs!

Now, I know what you're thinking. Does whitespace support indentation? The
answer is **yes**! Any character apart from newlines, tabs and spaces can be
used to indent your whitespace program!

Ok, but does it support comments? Well sure as hell it does! Any character
apart from newlines, tabs and spaces can be used to comment your program!

## What is whitespace?

[Whitespace](http://compsoc.dur.ac.uk/whitespace/) is a dependently spaced
language created by Edwin Brady and Chris Morris in 2003.

## Example run

Here is a basic example of a hello world program, running inside a whitespace
interpreter (that is written in whitespace), running again in that same
whitespace interpreter (that is still written in whitespace), running in this
whitespace interpreter (that is written in PHP).

    $ (cat examples/wsinterws.ws; cat examples/hworld.ws; echo -ne "\n\n\nquit\n\n\n") | hhvm bin/interpreter examples/wsinterws.ws
    whitespace interpreter written in whitespace
    made by oliver burghard smarty21@gmx.net
    in his free time for your and his joy
    good time and join me to get whitespace ready for business
    for any other information dial 1-900-whitespace
    or get soon info at www.whitespace-wants-to-be-taken-serious.org
    please enter the program and terminate via 3xenter,'quit',3xenter
    -- ws interpreter ws -------------------------------------------
    whitespace interpreter written in whitespace
    made by oliver burghard smarty21@gmx.net
    in his free time for your and his joy
    good time and join me to get whitespace ready for business
    for any other information dial 1-900-whitespace
    or get soon info at www.whitespace-wants-to-be-taken-serious.org
    please enter the program and terminate via 3xenter,'quit',3xenter
    -- ws interpreter ws -------------------------------------------
    Hello, world of spaces!

Please note that running this particular example can take a while.

## TODO

* Use BigInteger to properly support large labels

## See also

* [Whitespace](http://compsoc.dur.ac.uk/whitespace/index.php)
* [Whitespace interpreters](https://github.com/hostilefork/whitespacers)
