<?php

if ($pageVars["route"]["action"]=="data") {
?>

Invoke SSH Data Result: <?php echo $pageVars["shlResult"] ; ?>

<?php

} else if ($pageVars["route"]["action"]=="cli") {
    ?>
Shell Result: <?php echo $pageVars["shlResult"]  ; ?>

Invoke Shell Cli
<?php
} else if ($pageVars["route"]["action"]=="script") {
    ?>
Shell Result: <?php echo $pageVars["shlResult"]  ; ?>

Invoke Script
<?php
} ?>

------------------------------
Installer Finished