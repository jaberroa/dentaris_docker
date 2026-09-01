#!/bin/bash

echo "🧪 Ejecutando pruebas del módulo Patients..."

# Crear directorio de cobertura si no existe
mkdir -p coverage/patients

# Pruebas unitarias
echo "📋 Ejecutando pruebas unitarias..."
php artisan test tests/Unit/PatientTest.php --coverage --coverage-html=coverage/patients/unit

# Pruebas de integración
echo "🔗 Ejecutando pruebas de integración..."
php artisan test tests/Feature/PatientTest.php --coverage --coverage-html=coverage/patients/feature

# Pruebas de API
echo "🌐 Ejecutando pruebas de API..."
php artisan test tests/Feature/PatientApiTest.php --coverage --coverage-html=coverage/patients/api

# Ejecutar todas las pruebas del módulo con cobertura completa
echo "📊 Ejecutando todas las pruebas del módulo con cobertura..."
php artisan test --filter=Patient --coverage --coverage-html=coverage/patients/complete

# Generar reporte de cobertura consolidado
echo "📈 Generando reporte de cobertura consolidado..."
php artisan test --filter=Patient --coverage --coverage-text --coverage-clover=coverage/patients/coverage.xml

echo "✅ Pruebas completadas."
echo "📊 Reportes de cobertura disponibles en:"
echo "   - HTML: coverage/patients/complete/index.html"
echo "   - XML: coverage/patients/coverage.xml"
echo "   - Texto: coverage.txt"

# Mostrar resumen de cobertura
if [ -f "coverage.txt" ]; then
    echo ""
    echo "📈 Resumen de cobertura:"
    cat coverage.txt | grep -A 20 "Code Coverage Report"
fi
