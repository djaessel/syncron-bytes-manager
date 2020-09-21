<?php

namespace App\Helper;

use Throwable;

/**
 * Class ProcessManager
 * @package App\Helper
 */
class ProcessManager
{
    /**
     * @param string cmd
     * @param string args
     * @param string $logFile
     * @return int
     */
    public function runCommand($cmd, $args, $logFile)
    {
      $pid = shell_exec(sprintf("%s %s > %s 2>&2 & echo $!", $cmd, $args, $logFile));
      return $pid;
    }

    /**
     * @param int $pid
     * @return bool
     */
    public function isRunning($pid)
    {
      $running = false;

      try
      {
          $result = shell_exec(sprintf("ps %d", $pid));
          $processCount = count(preg_split("/\n/", $result));

          if ($processCount > 2)
          {
              $running = true;
          }
      } catch(Throwable $e) {
        // FIXME: add error message to log file
      }

      return $running;
    }
}
