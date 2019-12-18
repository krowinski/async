<?php
/** @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

namespace bin;

include __DIR__ . '/../vendor/autoload.php';

use Async\AsyncCall;
use Exception;
use RuntimeException;

$s = microtime(true);

AsyncCall::run(
    static function () {
        sleep(2);
        // there is no callback so echo will not be showed and parent process will not w8
        echo 'sleep 2s' . PHP_EOL;
    }
);

AsyncCall::run(
    static function () {
        sleep(4);
        // echo will be catched in child process and returned as second parameter $stdOut
        echo 'sleep 4s' . PHP_EOL;
    },
    static function ($results, $stdOut) {
        echo $stdOut;
    }
);

AsyncCall::run(
    static function () {
        throw new RuntimeException('bar');
    }
);

AsyncCall::run(
    static function () {
        throw new RuntimeException('foo');
    },
    static function () {
    },
    static function (Exception $error) {
        // we will get error
        assert($error->getMessage() === 'foo');
    }
);

AsyncCall::run(
    static function () {
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
    static function ($results) {
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
        static function () {
            sleep(1);
        },
        static function () {
        }
    );
}
// reset limit
AsyncCall::setProcessLimit(0);


echo PHP_EOL;
echo 'Script ended: ';
echo microtime(true) - $s;
echo PHP_EOL;