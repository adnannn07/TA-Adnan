<?php
require_once '../includes/fungsi.php';
session_destroy();
header('Location: ../index.php');
exit;
