<?php

require_once __DIR__ . '/../Repositories/CatalogoRepository.php';

class CatalogoService
{
    private CatalogoRepository $repository;

    public function __construct()
    {
        $this->repository = new CatalogoRepository();
    }

    public function getAll(): array
    {
        return [
            'success' => true,
            'message' => 'Catálogos obtenidos correctamente',
            'data' => [
                'generos' => $this->repository->getGeneros(),
                'situaciones_vivienda' => $this->repository->getSituacionesVivienda(),
                'instituciones' => $this->repository->getInstituciones(),
                'tipos_estudio' => $this->repository->getTiposEstudio(),
                'estados' => $this->repository->getEstados(),
                'turnos' => $this->repository->getTurnos(),
                'skills' => $this->repository->getSkills(),
                'niveles' => $this->repository->getNiveles(),
                'puestos' => $this->repository->getPuestos(),
            ],
            'status' => 200
        ];
    }
}