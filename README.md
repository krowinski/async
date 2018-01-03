
### How this work?
Well its basically "exec" with serialised closure. "Dressed" in nice libs like symfony process and console.
I serialise callable function and sent to child process by exec. To get callback I register shutdown function and wait for process to finish.

### Why not pcntl ?
- Pcntl extension fork, so you can forget using it in web applications like apache2/php-fpm etc its only for CLI
- forks retains the parent state (for example open files) so its problematic

### Problems ?
- Calling exec is slower then fork
- Some resource/function/data must be passed directly to closure

### Some research
* https://www.phproundtable.com/episode/asynchronous-php - good start to "know how" make php async
* https://amphp.org/ - non-blocking framework for PHP

### Example ?
Sure take a look - https://github.com/krowinski/async/blob/master/example/example.php

### Supports M$ Windows?
NO.

### TODO 
- process limit
- timeouts 
- tests
