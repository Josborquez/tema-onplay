<?php
/**
 * M-OP-filtros — Panel facetado de filtros para One Piece TCG.
 *
 * Spec:  docs/specs/M-OP-filtros/spec.md
 * Plan:  docs/specs/M-OP-filtros/plan.md
 * Tasks: docs/specs/M-OP-filtros/tasks.md
 *
 * Aproximación A (aprobada 2026-05-03): se extiende el pipeline existente de
 * `inc/filters-ajax.php` mediante filter hooks documentados ahí mismo. NO se
 * crea un endpoint AJAX paralelo ni se duplica la lógica de colapso/paginación.
 *
 * Para deshabilitar el módulo entero: comentar el require_once de este archivo
 * en `functions.php`. No deja tablas, no escribe meta, no crea taxonomías.
 *
 * @package Onplay
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/op-filters/query.php';
require_once __DIR__ . '/op-filters/helpers.php';
require_once __DIR__ . '/op-filters/panel.php';
require_once __DIR__ . '/op-filters/enqueue.php';
