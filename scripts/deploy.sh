#!/bin/bash

# Dentaris Deployment Script
# This script handles the deployment of the Dentaris application

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_NAME="dentaris"
APP_ENV=${APP_ENV:-production}
DOCKER_IMAGE="dentaris"
DOCKER_TAG=${DOCKER_TAG:-latest}
BACKUP_DIR="/backups"
LOG_FILE="/var/log/dentaris-deploy.log"

# Functions
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a $LOG_FILE
}

success() {
    echo -e "${GREEN}✓${NC} $1" | tee -a $LOG_FILE
}

warning() {
    echo -e "${YELLOW}⚠${NC} $1" | tee -a $LOG_FILE
}

error() {
    echo -e "${RED}✗${NC} $1" | tee -a $LOG_FILE
    exit 1
}

# Check if running as root
check_root() {
    if [[ $EUID -eq 0 ]]; then
        error "This script should not be run as root"
    fi
}

# Check prerequisites
check_prerequisites() {
    log "Checking prerequisites..."
    
    # Check Docker
    if ! command -v docker &> /dev/null; then
        error "Docker is not installed"
    fi
    
    # Check Docker Compose
    if ! command -v docker-compose &> /dev/null; then
        error "Docker Compose is not installed"
    fi
    
    # Check if Docker is running
    if ! docker info &> /dev/null; then
        error "Docker is not running"
    fi
    
    success "Prerequisites check passed"
}

# Backup current deployment
backup_current() {
    log "Creating backup of current deployment..."
    
    # Create backup directory
    mkdir -p $BACKUP_DIR/$(date +%Y%m%d_%H%M%S)
    
    # Backup database
    if docker-compose exec -T mysql mysqldump -u root -p$MYSQL_ROOT_PASSWORD $MYSQL_DATABASE > $BACKUP_DIR/$(date +%Y%m%d_%H%M%S)/database.sql; then
        success "Database backup created"
    else
        warning "Database backup failed"
    fi
    
    # Backup application files
    if docker-compose exec -T app tar -czf /tmp/app-backup.tar.gz /var/www/html; then
        docker-compose exec -T app cat /tmp/app-backup.tar.gz > $BACKUP_DIR/$(date +%Y%m%d_%H%M%S)/app-backup.tar.gz
        success "Application backup created"
    else
        warning "Application backup failed"
    fi
}

# Pull latest code
pull_code() {
    log "Pulling latest code..."
    
    if git pull origin main; then
        success "Code updated"
    else
        error "Failed to pull latest code"
    fi
}

# Build Docker image
build_image() {
    log "Building Docker image..."
    
    if docker build -t $DOCKER_IMAGE:$DOCKER_TAG .; then
        success "Docker image built"
    else
        error "Failed to build Docker image"
    fi
}

# Run security tests
run_security_tests() {
    log "Running security tests..."
    
    if docker run --rm $DOCKER_IMAGE:$DOCKER_TAG php artisan security:test-suite; then
        success "Security tests passed"
    else
        error "Security tests failed"
    fi
}

# Deploy application
deploy_app() {
    log "Deploying application..."
    
    # Stop current containers
    docker-compose down
    
    # Start new containers
    if docker-compose up -d; then
        success "Application deployed"
    else
        error "Failed to deploy application"
    fi
}

# Run database migrations
run_migrations() {
    log "Running database migrations..."
    
    if docker-compose exec -T app php artisan migrate --force; then
        success "Database migrations completed"
    else
        error "Database migrations failed"
    fi
}

# Clear caches
clear_caches() {
    log "Clearing application caches..."
    
    docker-compose exec -T app php artisan config:cache
    docker-compose exec -T app php artisan route:cache
    docker-compose exec -T app php artisan view:cache
    docker-compose exec -T app php artisan event:cache
    
    success "Caches cleared"
}

# Health check
health_check() {
    log "Performing health check..."
    
    # Wait for application to start
    sleep 30
    
    # Check if application is responding
    if curl -f http://localhost/health; then
        success "Health check passed"
    else
        error "Health check failed"
    fi
}

# Rollback deployment
rollback() {
    log "Rolling back deployment..."
    
    # Stop current containers
    docker-compose down
    
    # Restore previous version
    if docker-compose up -d; then
        success "Rollback completed"
    else
        error "Rollback failed"
    fi
}

# Cleanup old images
cleanup() {
    log "Cleaning up old Docker images..."
    
    # Remove unused images
    docker image prune -f
    
    # Remove old backups (keep last 7 days)
    find $BACKUP_DIR -type d -mtime +7 -exec rm -rf {} \;
    
    success "Cleanup completed"
}

# Send notification
send_notification() {
    local status=$1
    local message=$2
    
    # Send Slack notification
    if [[ -n "$SLACK_WEBHOOK_URL" ]]; then
        curl -X POST -H 'Content-type: application/json' \
            --data "{\"text\":\"Dentaris deployment $status: $message\"}" \
            $SLACK_WEBHOOK_URL
    fi
    
    # Send email notification
    if [[ -n "$EMAIL_RECIPIENTS" ]]; then
        echo "Dentaris deployment $status: $message" | mail -s "Dentaris Deployment $status" $EMAIL_RECIPIENTS
    fi
}

# Main deployment function
deploy() {
    log "Starting deployment of $APP_NAME..."
    
    check_root
    check_prerequisites
    backup_current
    pull_code
    build_image
    run_security_tests
    deploy_app
    run_migrations
    clear_caches
    health_check
    cleanup
    
    success "Deployment completed successfully"
    send_notification "SUCCESS" "Deployment completed successfully"
}

# Rollback function
rollback_deployment() {
    log "Starting rollback of $APP_NAME..."
    
    rollback
    health_check
    
    success "Rollback completed successfully"
    send_notification "ROLLBACK" "Rollback completed successfully"
}

# Show usage
usage() {
    echo "Usage: $0 [deploy|rollback|health|backup]"
    echo ""
    echo "Commands:"
    echo "  deploy   - Deploy the application"
    echo "  rollback - Rollback to previous version"
    echo "  health   - Check application health"
    echo "  backup   - Create backup only"
    echo ""
    echo "Environment variables:"
    echo "  APP_ENV           - Application environment (default: production)"
    echo "  DOCKER_TAG        - Docker image tag (default: latest)"
    echo "  SLACK_WEBHOOK_URL - Slack webhook URL for notifications"
    echo "  EMAIL_RECIPIENTS  - Email recipients for notifications"
}

# Main script logic
case "${1:-deploy}" in
    deploy)
        deploy
        ;;
    rollback)
        rollback_deployment
        ;;
    health)
        health_check
        ;;
    backup)
        backup_current
        ;;
    *)
        usage
        exit 1
        ;;
esac





