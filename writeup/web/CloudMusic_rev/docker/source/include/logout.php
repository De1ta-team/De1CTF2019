<?php
session_destroy();
ob_end_clean();
header('Location: /hotload.php?page=index');
die();