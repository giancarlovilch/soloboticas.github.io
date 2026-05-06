<?php

require_once __DIR__ . '/../Repositories/PostulanteRepository.php';
require_once __DIR__ . '/../Repositories/PostulacionRepository.php';
require_once __DIR__ . '/../Helpers/Validator.php';
require_once __DIR__ . '/../../config/env.php';

class PostulanteService
{
    private PostulanteRepository $repository;
    private PostulacionRepository $postulacionRepository;

    public function __construct()
    {
        $this->repository = new PostulanteRepository();
        $this->postulacionRepository = new PostulacionRepository();
    }

    public function getAll(): array
    {
        return $this->repository->findAll();
    }

    public function getById(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): array
    {
        $validation = $this->validatePostulanteData($data);
        if ($validation !== null) {
            return $validation;
        }

        $documentValidation = $this->validateUniqueDocument($data['num_documento']);
        if ($documentValidation !== null) {
            return $documentValidation;
        }

        $emailValidation = $this->validateUniqueEmail($data['email'] ?? null);
        if ($emailValidation !== null) {
            return $emailValidation;
        }

        $created = $this->repository->create($data);

        return [
            'success' => true,
            'message' => 'Postulante creado correctamente',
            'data' => $created,
            'status' => 201
        ];
    }

    public function getApplicationView(string $numDocumento): array
    {
        $validator = new Validator([
            'num_documento' => $numDocumento
        ]);

        $validator->required(['num_documento'])
            ->numeric('num_documento')
            ->exactLength('num_documento', 8);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'DNI inválido',
                'errors' => $validator->errors(),
                'status' => 422
            ];
        }

        $application = $this->postulacionRepository->findApplicationViewByDocument($numDocumento);

        if (!$application) {
            $postulante = $this->repository->findByDocument($numDocumento);

            return [
                'success' => true,
                'message' => 'Nueva postulación',
                'data' => [
                    'mode' => 'editable',
                    'postulante' => $postulante ?: null,
                    'postulacion' => null
                ],
                'status' => 200
            ];
        }

        $experiencias = $this->postulacionRepository
            ->findExperienciasByPostulante((int) $application['id_postulante']);

        $skills = $this->postulacionRepository
            ->findSkillsByPostulante((int) $application['id_postulante']);

        $etapaId = (int) ($application['etapa_id'] ?? 0);

        if (in_array($etapaId, [4, 5], true)) {
            return [
                'success' => false,
                'message' => 'Tu información ya no está disponible en esta sección. Debes ingresar por intranet.',
                'errors' => [],
                'status' => 403
            ];
        }

        return [
            'success' => true,
            'message' => 'Solicitud encontrada',
            'data' => [
                'mode' => 'readonly',
                'postulante' => [
                    'id_postulante' => $application['id_postulante'],
                    'nombres' => $application['nombres'],
                    'apellidos' => $application['apellidos'],
                    'genero_id' => $application['genero_id'],
                    'fecha_nacimiento' => $application['fecha_nacimiento'],
                    'email' => $application['email'],
                    'telefono' => $application['telefono'],
                    'situacion_vivienda_id' => $application['situacion_vivienda_id'],
                    'num_documento' => $application['num_documento'],
                    'cv_url' => $application['cv_url'],
                    'institucion_id' => $application['institucion_id'] ?? null,
                    'tipo_estudio_id' => $application['tipo_estudio_id'] ?? null,
                    'estado_id' => $application['estado_id'] ?? null,
                    'fecha_inicio' => $application['fecha_inicio'] ?? null,
                    'fecha_fin' => $application['fecha_fin'] ?? null,
                    'turno_id' => $application['turno_id'] ?? null,
                ],
                'postulacion' => [
                    'id_postulacion' => $application['id_postulacion'],
                    'fecha_postulacion' => $application['fecha_postulacion'],
                    'puesto_id' => $application['puesto_id'],
                    'etapa_id' => $application['etapa_id'],
                    'etapa_descripcion' => $application['etapa_descripcion']
                ],
                'experiencias' => $experiencias,
                'skills' => $skills
            ],
            'status' => 200
        ];
    }

    public function update(int $id, array $data): array
    {
        $postulante = $this->repository->findById($id);

        if (!$postulante) {
            return [
                'success' => false,
                'message' => 'Postulante no encontrado',
                'errors' => [],
                'status' => 404
            ];
        }

        $existingApplication = $this->postulacionRepository
            ->findExistingApplicationByDocument($postulante['num_documento']);

        if ($existingApplication) {
            return [
                'success' => false,
                'message' => 'La solicitud ya fue enviada y no puede modificarse',
                'errors' => [
                    'num_documento' => [
                        'Este postulante ya tiene una postulación enviada.'
                    ]
                ],
                'status' => 403
            ];
        }

        $validation = $this->validatePostulanteData($data);
        if ($validation !== null) {
            return $validation;
        }

        $documentValidation = $this->validateUniqueDocument($data['num_documento'], $id);
        if ($documentValidation !== null) {
            return $documentValidation;
        }

        $emailValidation = $this->validateUniqueEmail($data['email'] ?? null, $id);
        if ($emailValidation !== null) {
            return $emailValidation;
        }

        $updated = $this->repository->update($id, $data);

        return [
            'success' => true,
            'message' => 'Postulante actualizado correctamente',
            'data' => $updated,
            'status' => 200
        ];
    }

    public function delete(int $id): array
    {
        $postulante = $this->repository->findById($id);

        if (!$postulante) {
            return [
                'success' => false,
                'message' => 'Postulante no encontrado',
                'errors' => [],
                'status' => 404
            ];
        }

        $existingApplication = $this->postulacionRepository
            ->findExistingApplicationByDocument($postulante['num_documento']);

        if ($existingApplication) {
            return [
                'success' => false,
                'message' => 'La solicitud ya fue enviada y no puede eliminarse',
                'errors' => [
                    'num_documento' => [
                        'Este postulante ya tiene una postulación enviada.'
                    ]
                ],
                'status' => 403
            ];
        }

        $deleted = $this->repository->delete($id);

        if (!$deleted) {
            return [
                'success' => false,
                'message' => 'No se pudo eliminar el postulante',
                'errors' => [],
                'status' => 500
            ];
        }

        return [
            'success' => true,
            'message' => 'Postulante eliminado correctamente',
            'data' => null,
            'status' => 200
        ];
    }

    private function validatePostulanteData(array $data): ?array
    {
        $validator = new Validator($data);

        $validator->required(['nombres', 'num_documento'])
            ->max('nombres', 100)
            ->max('apellidos', 100)
            ->numeric('num_documento')
            ->exactLength('num_documento', 8)
            ->email('email')
            ->max('telefono', 15)
            ->date('fecha_nacimiento');

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
                'status' => 422
            ];
        }

        return null;
    }

    private function isValidAccessKey(?string $accessKey): bool
    {
        $expectedKey = env('APP_ACCESS_KEY', '');

        if ($expectedKey === '') {
            return false;
        }

        return trim((string) $accessKey) === trim($expectedKey);
    }

    public function checkDni(array $data): array
    {
        $validator = new Validator($data);

        $validator->required(['num_documento', 'access_key'])
            ->numeric('num_documento')
            ->exactLength('num_documento', 8);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
                'status' => 422
            ];
        }

        if (!$this->isValidAccessKey($data['access_key'] ?? null)) {
            return [
                'success' => false,
                'message' => 'Clave de acceso inválida',
                'errors' => [
                    'access_key' => ['La clave ingresada no es válida.']
                ],
                'status' => 401
            ];
        }

        $existingApplication = $this->postulacionRepository
            ->findExistingApplicationByDocument($data['num_documento']);

        if (!$existingApplication) {
            return [
                'success' => true,
                'message' => 'Puede iniciar una nueva postulación',
                'data' => [
                    'requires_birth_validation' => false,
                    'has_submitted_application' => false,
                    'next_step' => 'new_application'
                ],
                'status' => 200
            ];
        }

        $etapaId = (int) ($existingApplication['etapa_id'] ?? 0);

        if (in_array($etapaId, [4, 5], true)) {
            return [
                'success' => false,
                'message' => 'No tienes acceso a esta sección. Debes ingresar por intranet.',
                'errors' => [
                    'access' => ['Acceso restringido para esta etapa del proceso.']
                ],
                'status' => 403
            ];
        }

        return [
            'success' => true,
            'message' => 'Ya existe una solicitud enviada, valide su identidad',
            'data' => [
                'requires_birth_validation' => true,
                'has_submitted_application' => true,
                'next_step' => 'birth_validation'
            ],
            'status' => 200
        ];
    }

    public function validateAccess(array $data): array
    {
        $validator = new Validator($data);

        $validator->required(['num_documento', 'access_key', 'fecha_nacimiento'])
            ->numeric('num_documento')
            ->exactLength('num_documento', 8)
            ->date('fecha_nacimiento');

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
                'status' => 422
            ];
        }

        if (!$this->isValidAccessKey($data['access_key'] ?? null)) {
            return [
                'success' => false,
                'message' => 'Clave de acceso inválida',
                'errors' => [
                    'access_key' => ['La clave ingresada no es válida.']
                ],
                'status' => 401
            ];
        }

        $existingApplication = $this->postulacionRepository
            ->findExistingApplicationByDocument($data['num_documento']);

        if (!$existingApplication) {
            return [
                'success' => false,
                'message' => 'No existe una solicitud enviada para este DNI',
                'errors' => [
                    'num_documento' => [
                        'Primero debe verificar su DNI para iniciar una nueva postulación.'
                    ]
                ],
                'status' => 404
            ];
        }

        $etapaId = (int) ($existingApplication['etapa_id'] ?? 0);

        if (in_array($etapaId, [4, 5], true)) {
            return [
                'success' => false,
                'message' => 'No tienes acceso a esta sección. Debes ingresar por intranet.',
                'errors' => [
                    'access' => ['Acceso restringido para esta etapa del proceso.']
                ],
                'status' => 403
            ];
        }

        if ($existingApplication['fecha_nacimiento'] !== $data['fecha_nacimiento']) {
            return [
                'success' => false,
                'message' => 'La fecha de nacimiento no coincide con el registro',
                'errors' => [
                    'fecha_nacimiento' => [
                        'La fecha de nacimiento no coincide con el DNI registrado.'
                    ]
                ],
                'status' => 401
            ];
        }

        return [
            'success' => true,
            'message' => 'Identidad validada correctamente',
            'data' => [
                'validated' => true,
                'mode' => 'readonly',
                'postulante_id' => $existingApplication['id_postulante'],
                'id_postulacion' => $existingApplication['id_postulacion'],
                'etapa_id' => $existingApplication['etapa_id'],
                'next_step' => 'view_submitted_application'
            ],
            'status' => 200
        ];
    }

    public function apply(array $data): array
    {
        // =========================
        // 1. VALIDACIÓN
        // =========================
        $validator = new Validator($data);

        $validator->required([
            'nombres',
            'num_documento',
            'fecha_nacimiento',
            'puesto_id',
            'institucion_id',
            'tipo_estudio_id',
            'estado_id',
            'turno_id',
            'fecha_inicio'
        ])
            ->numeric('num_documento')
            ->exactLength('num_documento', 8)
            ->date('fecha_nacimiento')
            ->date('fecha_inicio');

        // validar fecha_fin solo si viene
        if (!empty($data['fecha_fin'])) {
            $validator->date('fecha_fin');
        }

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
                'status' => 422
            ];
        }

        // =========================
        // 2. REGLAS DE NEGOCIO
        // =========================

        // verificar si ya existe postulación
        $existingApplication = $this->postulacionRepository
            ->findExistingApplicationByDocument($data['num_documento']);

        if ($existingApplication) {
            return [
                'success' => false,
                'message' => 'Ya existe una solicitud enviada para este DNI',
                'errors' => [
                    'num_documento' => [
                        'Este postulante ya envió una solicitud.'
                    ]
                ],
                'status' => 409
            ];
        }

        $experiencias = $data['experiencias'] ?? [];

        $skills = $data['skills'] ?? [];

        if (!is_array($skills)) {
            return [
                'success' => false,
                'message' => 'El campo skills debe ser un arreglo',
                'status' => 422
            ];
        }

        if (!is_array($experiencias)) {
            return [
                'success' => false,
                'message' => 'El campo experiencias debe ser un arreglo',
                'errors' => [
                    'experiencias' => ['El formato de experiencias no es válido.']
                ],
                'status' => 422
            ];
        }

        $experienceValidation = $this->validateExperiencias($experiencias);
        if ($experienceValidation !== null) {
            return $experienceValidation;
        }

        $postulanteExistente = $this->repository->findByDocument($data['num_documento']);

        if (!$postulanteExistente) {
            $documentValidation = $this->validateUniqueDocument($data['num_documento']);
            if ($documentValidation !== null) {
                return $documentValidation;
            }

            $emailValidation = $this->validateUniqueEmail($data['email'] ?? null);
            if ($emailValidation !== null) {
                return $emailValidation;
            }
        } else {
            $emailValidation = $this->validateUniqueEmail(
                $data['email'] ?? null,
                (int)$postulanteExistente['id_postulante']
            );

            if ($emailValidation !== null) {
                return $emailValidation;
            }
        }

        if (!empty($data['fecha_fin']) && $data['fecha_fin'] < $data['fecha_inicio']) {
            return [
                'success' => false,
                'message' => 'La fecha fin de estudios no puede ser menor a la fecha inicio',
                'errors' => [
                    'fecha_fin' => [
                        'La fecha fin no puede ser menor que la fecha inicio.'
                    ]
                ],
                'status' => 422
            ];
        }


        // =========================
        // 3. TRANSACCIÓN
        // =========================
        $db = Database::getConnection();

        try {
            $db->beginTransaction();

            // =========================
            // 4. CREAR O BUSCAR POSTULANTE
            // =========================
            $postulante = $postulanteExistente;

            if (!$postulante) {
                $postulante = $this->repository->create($data);
            } else {
                $postulante = $this->repository->update($postulante['id_postulante'], $data);
            }

            // =========================
            // 5. CREAR POSTULACIÓN
            // =========================
            $postulacion = $this->postulacionRepository->create([
                'postulante_id' => $postulante['id_postulante'],
                'puesto_id' => $data['puesto_id'],
                'etapa_id' => 1 // Pendiente
            ]);

            // =========================
            // 6. CREAR ESTUDIOS
            // =========================
            $this->postulacionRepository->deleteEstudioByPostulante($postulante['id_postulante']);

            $this->postulacionRepository->createEstudio([
                'postulante_id' => $postulante['id_postulante'],
                'tipo_id' => $data['tipo_estudio_id'],
                'institucion_id' => $data['institucion_id'],
                'estado_id' => $data['estado_id'],
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => !empty($data['fecha_fin']) ? $data['fecha_fin'] : null,
            ]);

            $this->postulacionRepository->deletePreferenciaByPostulante($postulante['id_postulante']);

            $this->postulacionRepository->createPreferencia([
                'turno_id' => $data['turno_id'],
                'postulante_id' => $postulante['id_postulante'],
            ]);

            $this->postulacionRepository->deleteExperienciasByPostulante($postulante['id_postulante']);
            foreach ($experiencias as $experiencia) {
                $this->postulacionRepository->createExperiencia([
                    'postulante_id' => $postulante['id_postulante'],
                    'empresa' => $experiencia['empresa'],
                    'cargo' => $experiencia['cargo'] ?? null,
                    'fecha_inicio' => $experiencia['fecha_inicio'],
                    'fecha_fin' => !empty($experiencia['fecha_fin']) ? $experiencia['fecha_fin'] : null,
                ]);
            }

            // =========================
            // 7. CREAR SKILLS
            // =========================
            $this->postulacionRepository->deleteSkillsByPostulante($postulante['id_postulante']);

            foreach ($skills as $skill) {
                $this->postulacionRepository->createSkill([
                    'postulante_id' => $postulante['id_postulante'],
                    'skill_id' => $skill['skill_id'],
                    'nivel_id' => $skill['nivel_id'] ?? null
                ]);
            }


            // =========================
            // 8. CONFIRMAR
            // =========================
            $db->commit();

            return [
                'success' => true,
                'message' => 'Postulación enviada correctamente',
                'data' => [
                    'postulante' => $postulante,
                    'postulacion' => $postulacion
                ],
                'status' => 201
            ];
        } catch (Throwable $e) {

            if ($db->inTransaction()) {
                $db->rollBack();
            }

            return [
                'success' => false,
                'message' => 'Error al guardar la postulación',
                'errors' => [
                    'exception' => [$e->getMessage()]
                ],
                'status' => 500
            ];
        }
    }


    private function validateUniqueDocument(string $numDocumento, ?int $ignoreId = null): ?array
    {
        $existing = $this->repository->findByDocument($numDocumento);

        if ($existing && ($ignoreId === null || (int)$existing['id_postulante'] !== $ignoreId)) {
            return [
                'success' => false,
                'message' => 'Ya existe un postulante con ese número de documento',
                'errors' => [
                    'num_documento' => ['El número de documento ya está registrado.']
                ],
                'status' => 409
            ];
        }

        return null;
    }

    private function validateUniqueEmail(?string $email, ?int $ignoreId = null): ?array
    {
        if (empty($email)) {
            return null;
        }

        $existing = $this->repository->findByEmail($email);

        if ($existing && ($ignoreId === null || (int)$existing['id_postulante'] !== $ignoreId)) {
            return [
                'success' => false,
                'message' => 'Ya existe un postulante con ese correo',
                'errors' => [
                    'email' => ['El correo ya está registrado.']
                ],
                'status' => 409
            ];
        }

        return null;
    }

    private function validateExperiencias(array $experiencias): ?array
    {
        foreach ($experiencias as $index => $experiencia) {
            $validator = new Validator($experiencia);

            $validator->required([
                'empresa',
                'fecha_inicio'
            ])
                ->max('empresa', 150)
                ->max('cargo', 100)
                ->date('fecha_inicio');

            if (!empty($experiencia['fecha_fin'])) {
                $validator->date('fecha_fin');
            }

            if ($validator->fails()) {
                return [
                    'success' => false,
                    'message' => 'Datos inválidos en experiencia laboral',
                    'errors' => [
                        "experiencias.{$index}" => $validator->errors()
                    ],
                    'status' => 422
                ];
            }

            if (
                !empty($experiencia['fecha_fin']) &&
                $experiencia['fecha_fin'] < $experiencia['fecha_inicio']
            ) {
                return [
                    'success' => false,
                    'message' => 'La fecha fin de una experiencia no puede ser menor a la fecha inicio',
                    'errors' => [
                        "experiencias.{$index}.fecha_fin" => [
                            'La fecha fin no puede ser menor que la fecha inicio.'
                        ]
                    ],
                    'status' => 422
                ];
            }
        }

        return null;
    }
}
