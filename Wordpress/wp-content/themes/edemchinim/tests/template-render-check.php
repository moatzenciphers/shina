<?php
/** Compare isolated read-only page renders with their reviewed output contracts. */
if (PHP_SAPI !== 'cli' || defined('ABSPATH')) {
    exit;
}

$fixtures = json_decode(file_get_contents(__DIR__ . '/fixtures/rendered-pages.json'), true);
$checked = 0;
$failed = array();
foreach ($fixtures as $mode => $scenarios) {
    foreach ($scenarios as $scenario => $expected) {
        $command = array(PHP_BINARY, __DIR__ . '/theme-contract.php', '--render-fixture=' . $scenario);
        if ($mode === 'no-acf') {
            $command[] = '--without-acf';
        }
        $process = proc_open($command, array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
        if (! is_resource($process)) {
            fwrite(STDERR, "FAIL: could not start an isolated render.\n");
            exit(1);
        }
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $status = proc_close($process);
        if ($status !== 0 || $errors !== '' || json_decode($output, true) !== $expected) {
            // Print only scenario names, never rendered markup or configuration.
            $failed[] = $mode . ':' . $scenario;
        }
        $checked++;
    }
}
if ($failed) {
    fwrite(STDERR, 'FAIL: ' . implode(', ', $failed) . "\n");
    exit(1);
}
echo 'PASS: ' . $checked . " page renders match reviewed output, without data writes or PHP warnings.\n";
