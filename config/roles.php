<?php

/**
 * Papéis do sistema e suas permissões.
 * Cada papel tem um array de permissões que pode executar.
 */

return [

    'roles' => [
        'super_admin'  => 'Super Administrador',
        'diretor'      => 'Diretor',
        'coordenador'  => 'Coordenador',
        'secretaria'   => 'Secretaria',
        'professor'    => 'Professor',
        'pai'          => 'Pai / Responsável',
    ],

    // Hierarquia (quanto menor o número, mais poder)
    'hierarchy' => [
        'super_admin' => 0,
        'diretor'     => 1,
        'coordenador' => 2,
        'secretaria'  => 3,
        'professor'   => 4,
        'pai'         => 5,
    ],

    'permissions' => [

        // ── Escola ─────────────────────────────────────────
        'escola.gerenciar'       => ['super_admin'],
        'escola.configurar'      => ['super_admin', 'diretor'],
        'escola.visual'          => ['super_admin', 'diretor'],

        // ── Usuários ───────────────────────────────────────
        'usuarios.criar'         => ['super_admin', 'diretor', 'coordenador', 'secretaria'],
        'usuarios.editar'        => ['super_admin', 'diretor', 'coordenador'],
        'usuarios.deletar'       => ['super_admin', 'diretor'],
        'usuarios.ver'           => ['super_admin', 'diretor', 'coordenador', 'secretaria'],

        // ── Alunos ─────────────────────────────────────────
        'alunos.criar'           => ['super_admin', 'diretor', 'coordenador', 'secretaria'],
        'alunos.editar'          => ['super_admin', 'diretor', 'coordenador', 'secretaria'],
        'alunos.deletar'         => ['super_admin', 'diretor'],
        'alunos.ver'             => ['super_admin', 'diretor', 'coordenador', 'secretaria', 'professor'],

        // ── Turmas e Disciplinas ────────────────────────────
        'turmas.gerenciar'       => ['super_admin', 'diretor', 'coordenador', 'secretaria'],
        'turmas.ver'             => ['super_admin', 'diretor', 'coordenador', 'secretaria', 'professor'],
        'disciplinas.gerenciar'  => ['super_admin', 'diretor', 'coordenador'],

        // ── Notas ──────────────────────────────────────────
        'notas.lancar'           => ['professor'],
        'notas.editar'           => ['professor', 'coordenador', 'diretor'],
        'notas.bloquear'         => ['coordenador', 'diretor'],
        'notas.ver.todas'        => ['super_admin', 'diretor', 'coordenador', 'secretaria'],
        'notas.ver.proprias'     => ['professor'],

        // ── Frequência ─────────────────────────────────────
        'frequencia.lancar'      => ['professor'],
        'frequencia.editar'      => ['professor', 'coordenador', 'diretor'],
        'frequencia.ver.todas'   => ['super_admin', 'diretor', 'coordenador', 'secretaria'],

        // ── Recuperação ────────────────────────────────────
        'recuperacao.abrir'      => ['coordenador', 'diretor', 'super_admin'],
        'recuperacao.fechar'     => ['coordenador', 'diretor', 'super_admin'],
        'recuperacao.lancar'     => ['professor'],

        // ── Diário ─────────────────────────────────────────
        'diario.escrever'        => ['super_admin', 'diretor', 'coordenador', 'professor'],
        'diario.ver.todos'       => ['super_admin', 'diretor', 'coordenador'],
        'diario.ver.professores' => ['professor'],

        // ── Comunicados e Agenda ───────────────────────────
        'comunicados.criar'      => ['super_admin', 'diretor', 'coordenador', 'secretaria'],
        'eventos.criar'          => ['super_admin', 'diretor', 'coordenador', 'secretaria'],

        // ── Relatórios ─────────────────────────────────────
        'relatorios.boletim'     => ['super_admin', 'diretor', 'coordenador', 'secretaria', 'professor'],
        'relatorios.declaracoes' => ['super_admin', 'diretor', 'coordenador', 'secretaria'],
        'relatorios.turma'       => ['super_admin', 'diretor', 'coordenador', 'professor'],

        // ── Auditoria ──────────────────────────────────────
        'auditoria.ver'          => ['super_admin', 'diretor', 'coordenador'],

        // ── Super Admin ────────────────────────────────────
        'superadmin.painel'      => ['super_admin'],
    ],
];
