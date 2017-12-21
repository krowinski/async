
### How this work?
Well its basically "exec" with serialised closure. "Dressed" in nice libs like symfony process and console.

### Why not pcntl ?
- Pcntl extension fork, so you can forget using it in web applications like apache2/php-fpm etc its only for CLI
- forks retains the parent state (for example open files) so its problematic

### Problems ?
- Calling exec is slower then fork
- Some resource/function/data must be passed directly to closure

