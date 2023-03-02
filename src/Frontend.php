<?php

declare(strict_types=1);

namespace Packyderm\Frontend;

final class Frontend
{
    const SERVER_COMMAND = './server';
    const SERVER_STARTED_STRING = 'Server listening at';

    private static mixed $process;

    private static array $pipes = [];

    private static array $descriptors = [
        0 => ['file', '/dev/null', 'r'], //stdin
        1 => ['file', '/tmp/packyderm-server-out', 'a'], //stdout
        2 => ['file', '/tmp/packyderm-server-err', 'a'], //stderr
    ];

    /**
     * @param callable($buildFunc) : void $buildFunc
     */
    public static function run(callable $buildFunc) : void
    {
        try {
            self::startServer();
            $builder = new ImageBuilder();
            $buildFunc($builder);
        } catch (\Throwable $t) {
            self::displayException($t);
        }
        self::stopServer();
    }

    private static function startServer() : void
    {
        self::$process = proc_open(self::SERVER_COMMAND, self::$descriptors, self::$pipes);
        self::waitForServerToListen();
    }

    private static function stopServer() : void
    {
        if (self::$process) {
            proc_terminate(self::$process);
            proc_close(self::$process);
        }
    }

    private static function waitForServerToListen(): void
    {
        while (true) {
            $status = proc_get_status(self::$process);
            if (!$status['running']) {
                throw new \Exception('Server process stopped');
            }
            if ($stdout = file_get_contents(self::$descriptors[2][1])) {
                if (str_contains($stdout, self::SERVER_STARTED_STRING)) {
                    break;
                }
            }
            usleep(100);
        }
    }

    private static function displayException(\Throwable|\Exception $t): void
    {
        echo "Error: " . get_class($t) . ":" . $t->getMessage() . "\n";
        echo file_get_contents(self::$descriptors[1][1]);
        echo file_get_contents(self::$descriptors[2][1]);
        var_dump(proc_get_status(self::$process));
    }

}
