<?php

$xhprof = (bool) isset($_COOKIE['xhprof']);

if ($xhprof) {
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
}

$currentDir = dirname(__DIR__);
chdir($currentDir);

include ($currentDir . '/vendor/autoload.php');

require_once "{$currentDir}/application/Bootstrap.php";
Bootstrap::run("{$currentDir}/application");

if ($xhprof) {
    $xhprof_data = xhprof_disable();
    $xhprof_runs = new XHProfRuns_Default();
    $run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_testing");
    echo '<!-- http://xhprof.sb6.ru/index.php?run='.$run_id.'&source=xhprof_testing -->';
}