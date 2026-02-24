<?php
    function loadClass($className) {
        require_once './classe/' . $className . '.php';
    }
    spl_autoload_register('loadClass');
?>