<?php
session_start();

session_unset();
session_destroy();

header("Location: ../../public/company/login.php");
exit;
