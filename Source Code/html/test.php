<?php

$update_files = scandir('/');
echo(json_encode($update_files));