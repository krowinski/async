<?php


namespace bin;


include __DIR__ . '/../vendor/autoload.php';

use Async\AsyncCall;

$s = microtime(true);


AsyncCall::run(
    function () {
        sleep(2);
        // there is no callback so echo will not be showed
        echo 'sleep 2s' . PHP_EOL;
    }
);

AsyncCall::run(
    function () {
        sleep(1);
        // echo will be catched in child process and returned as echo in parent process ( but empty callback MUST be set )
        echo 'sleep 1s' . PHP_EOL;
    },
    function () { }
);

AsyncCall::run(
    function () {
        throw new \Exception('bar');
    }
);

AsyncCall::run(
    function () {
        throw new \Exception('foo');
    },
    function () {},
    function (\Exception $error) { assert($error->getMessage() === 'foo'); }
);


AsyncCall::run(
    function () {
        // if this is in parent, child will not see this
        function getPage($url)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            curl_close($ch);

            return $output;
        }

        return getPage('example.com');
    },
    function ($results) {
        echo $results;
    }
);


echo PHP_EOL;
echo microtime(true) - $s;
echo PHP_EOL;