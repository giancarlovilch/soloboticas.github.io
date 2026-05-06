<?php

class HomeController
{
    /**
     * Carga la página principal de Solo Boticas[cite: 5]
     */
    public function index(): void
    {
        require_once __DIR__ . '/../../views/home.php';
    }

    /**
     * Carga la vista de "Trabaja con nosotros" (Acceso por DNI)[cite: 3]
     */
    public function accesoView(): void
    {
        require_once __DIR__ . '/../../views/acceso.php';
    }

    /**
     * Carga el formulario detallado de postulación[cite: 4]
     */
    public function formularioView(): void
    {
        require_once __DIR__ . '/../../views/formulario.php';
    }

    /**
     * Carga la nueva interfaz de Login (Intranet)
     */
    public function loginView(): void
    {
        require_once __DIR__ . '/../../views/auth/login.php';
    }
}