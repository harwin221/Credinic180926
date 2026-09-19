-- ============================================================
-- CAMBIOS A APLICAR EN PRODUCCIÓN - 18/09/2026
-- ============================================================

-- 1. Crear permiso "Editar Solicitudes" en grupo Prestamos
INSERT INTO permissions (name, guard_name, description, created_at, updated_at)
SELECT 'Editar Solicitudes', 'web', 'Prestamos', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'Editar Solicitudes');

-- 2. Eliminar registros basura en prestamo_cuota_abono
--    (detalles que apuntan a cuotas inexistentes, quedaron de una edición anterior)
DELETE FROM prestamo_cuota_abono
WHERE id IN (
    SELECT pca_id FROM (
        SELECT pca.id as pca_id
        FROM prestamo_cuota_abono pca
        LEFT JOIN prestamo_coutas pc ON pc.id = pca.prestamo_cuota_id
        WHERE pc.id IS NULL
    ) as tmp
);

-- 3. Restaurar préstamo a estado Activo
--    id=1251 es el ID interno de la tabla, el número de crédito visible (consecutivo) es 1253
UPDATE prestamos SET estado = 1 WHERE id = 1251 AND estado = 2;

-- ============================================================
-- FIN
-- ============================================================
