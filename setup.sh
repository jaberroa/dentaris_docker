#!/bin/bash

echo "🚀 Configurando Dentaris Docker..."

# Copiar archivo de entorno
if [ ! -f .env ]; then
    cp env.docker .env
    echo "✅ Archivo .env creado"
else
    echo "⚠️  Archivo .env ya existe"
fi

# Construir y levantar contenedores
echo "🔨 Construyendo contenedores..."
docker-compose build

echo "🚀 Iniciando servicios..."
docker-compose up -d

# Esperar a que la base de datos esté lista
echo "⏳ Esperando a que la base de datos esté lista..."
sleep 30

# Instalar dependencias de Composer
echo "📦 Instalando dependencias de Composer..."
docker-compose exec app composer install

# Generar clave de aplicación
echo "🔑 Generando clave de aplicación..."
docker-compose exec app php artisan key:generate

# Ejecutar migraciones
echo "🗄️  Ejecutando migraciones..."
docker-compose exec app php artisan migrate

# Crear enlace simbólico para storage
echo "🔗 Creando enlace simbólico para storage..."
docker-compose exec app php artisan storage:link

# Limpiar y optimizar
echo "🧹 Limpiando y optimizando..."
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

echo "✅ ¡Configuración completada!"
echo ""
echo "🌐 Aplicación disponible en: http://localhost:8080"
echo "🗄️  phpMyAdmin disponible en: http://localhost:8081"
echo ""
echo "📋 Comandos útiles:"
echo "  - Ver logs: docker-compose logs -f"
echo "  - Parar servicios: docker-compose down"
echo "  - Reiniciar servicios: docker-compose restart"
echo "  - Acceder al contenedor: docker-compose exec app bash"
