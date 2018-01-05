<?php


namespace bin;


include __DIR__ . '/../vendor/autoload.php';

use Async\AsyncCall;

$s = microtime(true);

AsyncCall::run(
    function () {
        sleep(2);
        // there is no callback so echo will not be showed and parent process will not w8
        echo 'sleep 2s' . PHP_EOL;
    }
);

AsyncCall::run(
    function () {
        sleep(4);
        // echo will be catched in child process and returned as second parameter $stdOut
        echo 'sleep 4s' . PHP_EOL;
    },
    function ($results, $stdOut) {
        echo $stdOut;
    }
);

AsyncCall::run(
    function () {
        throw new \RuntimeException('bar');
    }
);

AsyncCall::run(
    function () {
        throw new \RuntimeException('foo');
    },
    function () {
    },
    function (\Exception $error) {
        // we will get error
        assert($error->getMessage() === 'foo');
    }
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

        // this will be returned to callback as first parameter
        return getPage('example.com');
    },
    function ($results) {
        echo $results;
    }
);

// -------- process limit ----------

// set GLOBAL limit for process
AsyncCall::setProcessLimit(2);
$i = 10;
while ($i--) {
    // this will start 2 process and then wait them to finish before starting second one
    AsyncCall::run(
        function () {
            sleep(1);
        },
        function () {
        }
    );
}
// reset limit
AsyncCall::setProcessLimit(0);


echo PHP_EOL;
echo microtime(true) - $s;
echo PHP_EOL;