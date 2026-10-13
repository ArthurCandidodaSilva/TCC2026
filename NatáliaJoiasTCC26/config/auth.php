<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function verificarLogin(): void
{
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /Arthur351/NatáliaJoiasTCC26/login.php');
        exit;
    }
}

function verificarAdmin(): void
{
    if (
        !isset($_SESSION['usuario_id']) ||
        !isset($_SESSION['tipo_usuario']) ||
        $_SESSION['tipo_usuario'] !== 'admin'
    ) {
        header('Location: /Arthur351/NatáliaJoiasTCC26/login.php');
        exit;
    }
}