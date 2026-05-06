<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Services/CatalogoService.php';

class CatalogoController extends Controller
{
    private CatalogoService $service;

    public function __construct()
    {
        $this->service = new CatalogoService();
    }

    public function getAll(): void
    {
        $result = $this->service->getAll();

        if (!$result['success']) {
            $this->error($result['message'], $result['status'] ?? 500);
        }

        $this->success($result['message'], $result['data']);
    }
}