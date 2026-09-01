#!/bin/bash

# Script para ejecutar pruebas del módulo de appointments
# Autor: Sistema de Testing Dentaris
# Fecha: $(date)

set -e  # Salir si hay algún error

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para imprimir mensajes con colores
print_message() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Función para mostrar ayuda
show_help() {
    echo "Script para ejecutar pruebas del módulo de appointments"
    echo ""
    echo "Uso: $0 [OPCIONES]"
    echo ""
    echo "Opciones:"
    echo "  -h, --help              Mostrar esta ayuda"
    echo "  -u, --unit              Ejecutar solo pruebas unitarias"
    echo "  -f, --feature           Ejecutar solo pruebas de integración"
    echo "  -a, --api               Ejecutar solo pruebas de API"
    echo "  -c, --coverage          Ejecutar con cobertura de código"
    echo "  --html                  Generar reporte HTML de cobertura"
    echo "  --xml                   Generar reporte XML de cobertura"
    echo "  --all                   Ejecutar todas las pruebas (default)"
    echo "  --fast                  Ejecutar pruebas rápidas (sin cobertura)"
    echo "  --verbose               Modo verbose"
    echo ""
    echo "Ejemplos:"
    echo "  $0 --coverage --html    Ejecutar con cobertura y reporte HTML"
    echo "  $0 --unit --fast        Solo pruebas unitarias rápidas"
    echo "  $0 --api --verbose      Pruebas de API con output detallado"
}

# Variables por defecto
RUN_UNIT=false
RUN_FEATURE=false
RUN_API=false
RUN_COVERAGE=false
GENERATE_HTML=false
GENERATE_XML=false
VERBOSE=false
FAST=false

# Parsear argumentos
while [[ $# -gt 0 ]]; do
    case $1 in
        -h|--help)
            show_help
            exit 0
            ;;
        -u|--unit)
            RUN_UNIT=true
            shift
            ;;
        -f|--feature)
            RUN_FEATURE=true
            shift
            ;;
        -a|--api)
            RUN_API=true
            shift
            ;;
        -c|--coverage)
            RUN_COVERAGE=true
            shift
            ;;
        --html)
            GENERATE_HTML=true
            shift
            ;;
        --xml)
            GENERATE_XML=true
            shift
            ;;
        --all)
            RUN_UNIT=true
            RUN_FEATURE=true
            RUN_API=true
            shift
            ;;
        --fast)
            FAST=true
            shift
            ;;
        --verbose)
            VERBOSE=true
            shift
            ;;
        *)
            print_error "Opción desconocida: $1"
            show_help
            exit 1
            ;;
    esac
done

# Si no se especificó ningún tipo de prueba, ejecutar todas
if [[ "$RUN_UNIT" == false && "$RUN_FEATURE" == false && "$RUN_API" == false ]]; then
    RUN_UNIT=true
    RUN_FEATURE=true
    RUN_API=true
fi

# Configurar directorio base
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
cd "$PROJECT_ROOT"

print_message "Iniciando pruebas del módulo de appointments..."
print_message "Directorio del proyecto: $PROJECT_ROOT"

# Verificar que estamos en el directorio correcto
if [[ ! -f "artisan" ]]; then
    print_error "No se encontró el archivo artisan. Asegúrate de ejecutar este script desde la raíz del proyecto Laravel."
    exit 1
fi

# Crear directorios de cobertura si no existen
if [[ "$RUN_COVERAGE" == true || "$GENERATE_HTML" == true || "$GENERATE_XML" == true ]]; then
    mkdir -p coverage/appointments
    print_message "Directorio de cobertura creado/verificado"
fi

# Función para ejecutar pruebas con diferentes configuraciones
run_tests() {
    local test_type=$1
    local test_path=$2
    local description=$3
    
    print_message "Ejecutando $description..."
    
    local cmd="php artisan test"
    local coverage_cmd=""
    
    # Configurar comando base
    if [[ "$test_path" != "" ]]; then
        cmd="$cmd $test_path"
    fi
    
    # Configurar cobertura
    if [[ "$RUN_COVERAGE" == true ]]; then
        if [[ "$GENERATE_HTML" == true ]]; then
            coverage_cmd="--coverage-html=coverage/appointments/html"
        fi
        if [[ "$GENERATE_XML" == true ]]; then
            coverage_cmd="$coverage_cmd --coverage-xml=coverage/appointments/coverage.xml"
        fi
        if [[ "$coverage_cmd" == "" ]]; then
            coverage_cmd="--coverage-text"
        fi
        cmd="$cmd $coverage_cmd"
    fi
    
    # Configurar verbose
    if [[ "$VERBOSE" == true ]]; then
        cmd="$cmd --verbose"
    fi
    
    # Usar configuración específica de appointments
    cmd="$cmd --configuration=phpunit-appointments.xml"
    
    print_message "Comando: $cmd"
    
    # Ejecutar comando
    if eval "$cmd"; then
        print_success "$description completadas exitosamente"
        return 0
    else
        print_error "$description fallaron"
        return 1
    fi
}

# Contador de pruebas ejecutadas y fallidas
TESTS_RUN=0
TESTS_FAILED=0

# Ejecutar pruebas unitarias
if [[ "$RUN_UNIT" == true ]]; then
    ((TESTS_RUN++))
    if ! run_tests "unit" "tests/Unit/AppointmentTest.php" "Pruebas unitarias de appointments"; then
        ((TESTS_FAILED++))
    fi
fi

# Ejecutar pruebas de integración
if [[ "$RUN_FEATURE" == true ]]; then
    ((TESTS_RUN++))
    if ! run_tests "feature" "tests/Feature/AppointmentTest.php" "Pruebas de integración de appointments"; then
        ((TESTS_FAILED++))
    fi
fi

# Ejecutar pruebas de API
if [[ "$RUN_API" == true ]]; then
    ((TESTS_RUN++))
    if ! run_tests "api" "tests/Feature/AppointmentApiTest.php" "Pruebas de API de appointments"; then
        ((TESTS_FAILED++))
    fi
fi

# Mostrar resumen
echo ""
print_message "=== RESUMEN DE PRUEBAS ==="
print_message "Pruebas ejecutadas: $TESTS_RUN"
print_message "Pruebas fallidas: $TESTS_FAILED"

if [[ "$TESTS_FAILED" -eq 0 ]]; then
    print_success "¡Todas las pruebas pasaron exitosamente!"
    
    # Mostrar información de cobertura si se generó
    if [[ "$RUN_COVERAGE" == true ]]; then
        echo ""
        print_message "=== INFORMACIÓN DE COBERTURA ==="
        
        if [[ "$GENERATE_HTML" == true ]]; then
            print_message "Reporte HTML generado en: coverage/appointments/html/index.html"
        fi
        
        if [[ "$GENERATE_XML" == true ]]; then
            print_message "Reporte XML generado en: coverage/appointments/coverage.xml"
        fi
        
        if [[ -f "coverage/appointments/coverage.txt" ]]; then
            print_message "Reporte de texto generado en: coverage/appointments/coverage.txt"
        fi
    fi
    
    echo ""
    print_success "✅ Módulo de appointments APTO para integración"
    exit 0
else
    print_error "❌ Algunas pruebas fallaron. Revisar los errores antes de continuar."
    exit 1
fi

