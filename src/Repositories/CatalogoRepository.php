<?php

require_once __DIR__ . '/../Core/Database.php';

class CatalogoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function getAll(string $table, string $idField, string $descField): array
    {
        $sql = "SELECT {$idField} AS id, {$descField} AS descripcion FROM {$table} ORDER BY {$idField}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getGeneros(): array
    {
        return $this->getAll('genero', 'id_genero', 'descripcion');
    }

    public function getSituacionesVivienda(): array
    {
        return $this->getAll('situacion_vivienda', 'id_situacion', 'descripcion');
    }

    public function getInstituciones(): array
    {
        return $this->getAll('institucion', 'id_institucion', 'descripcion');
    }

    public function getTiposEstudio(): array
    {
        return $this->getAll('tipo_estudio', 'id_tipo', 'descripcion');
    }

    public function getEstados(): array
    {
        return $this->getAll('estado', 'id_estado', 'descripcion');
    }

    public function getTurnos(): array
    {
        return $this->getAll('turno', 'id_turno', 'descripcion');
    }

    public function getSkills(): array
    {
        return $this->getAll('skill', 'id_skill', 'descripcion');
    }

    public function getNiveles(): array
    {
        return $this->getAll('nivel', 'id_nivel', 'descripcion');
    }

    public function getPuestos(): array
    {
        return $this->getAll('puesto', 'id_puesto', 'descripcion');
    }
}