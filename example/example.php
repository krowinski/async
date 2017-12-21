<?php


namespace bin;


include __DIR__ . '/../vendor/autoload.php';

use Async\AsyncCall;

$s = microtime(true);


AsyncCall::run(
    function () {
        sleep(2);
        echo 'sleep 2s' . PHP_EOL;
    }
);

AsyncCall::run(
    function () {
        sleep(1);
        echo 'sleep 1s' . PHP_EOL;
    }
);

AsyncCall::run(
    function () {
        throw new \Exception('test');
    }
);

AsyncCall::run(
    function () {
        /**
         * @param $url
         * @return mixed
         */
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