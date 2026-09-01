#!/bin/bash

# Dentaris Kubernetes Deployment Script
# This script handles the deployment of Dentaris to Kubernetes

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_NAME="dentaris"
NAMESPACE="dentaris"
KUBECONFIG=${KUBECONFIG:-~/.kube/config}
HELM_REPO="https://charts.bitnami.com/bitnami"
LOG_FILE="/var/log/dentaris-k8s-deploy.log"

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

# Check prerequisites
check_prerequisites() {
    log "Checking prerequisites..."
    
    # Check kubectl
    if ! command -v kubectl &> /dev/null; then
        error "kubectl is not installed"
    fi
    
    # Check helm
    if ! command -v helm &> /dev/null; then
        error "Helm is not installed"
    fi
    
    # Check if kubectl can connect to cluster
    if ! kubectl cluster-info &> /dev/null; then
        error "Cannot connect to Kubernetes cluster"
    fi
    
    success "Prerequisites check passed"
}

# Create namespace
create_namespace() {
    log "Creating namespace..."
    
    if kubectl get namespace $NAMESPACE &> /dev/null; then
        warning "Namespace $NAMESPACE already exists"
    else
        kubectl create namespace $NAMESPACE
        success "Namespace $NAMESPACE created"
    fi
}

# Apply Kubernetes manifests
apply_manifests() {
    log "Applying Kubernetes manifests..."
    
    # Apply namespace and RBAC
    kubectl apply -f k8s/namespace.yaml
    kubectl apply -f k8s/rbac.yaml
    
    # Apply ConfigMaps and Secrets
    kubectl apply -f k8s/configmap.yaml
    kubectl apply -f k8s/secrets.yaml
    
    # Apply Persistent Volumes
    kubectl apply -f k8s/persistent-volumes.yaml
    
    # Apply Deployments
    kubectl apply -f k8s/deployments.yaml
    
    # Apply Services
    kubectl apply -f k8s/services.yaml
    
    # Apply Ingress
    kubectl apply -f k8s/ingress.yaml
    
    # Apply Monitoring
    kubectl apply -f k8s/monitoring.yaml
    
    # Apply Network Policies
    kubectl apply -f k8s/network-policies.yaml
    
    success "Kubernetes manifests applied"
}

# Wait for deployments
wait_for_deployments() {
    log "Waiting for deployments to be ready..."
    
    # Wait for app deployment
    kubectl wait --for=condition=available --timeout=300s deployment/dentaris-app -n $NAMESPACE
    
    # Wait for MySQL deployment
    kubectl wait --for=condition=available --timeout=300s deployment/dentaris-mysql -n $NAMESPACE
    
    # Wait for Redis deployment
    kubectl wait --for=condition=available --timeout=300s deployment/dentaris-redis -n $NAMESPACE
    
    # Wait for monitoring deployments
    kubectl wait --for=condition=available --timeout=300s deployment/dentaris-prometheus -n $NAMESPACE
    kubectl wait --for=condition=available --timeout=300s deployment/dentaris-grafana -n $NAMESPACE
    
    success "All deployments are ready"
}

# Run database migrations
run_migrations() {
    log "Running database migrations..."
    
    # Get app pod name
    APP_POD=$(kubectl get pods -n $NAMESPACE -l component=app -o jsonpath='{.items[0].metadata.name}')
    
    if [ -z "$APP_POD" ]; then
        error "App pod not found"
    fi
    
    # Run migrations
    kubectl exec -n $NAMESPACE $APP_POD -- php artisan migrate --force
    
    success "Database migrations completed"
}

# Health check
health_check() {
    log "Performing health check..."
    
    # Get app pod name
    APP_POD=$(kubectl get pods -n $NAMESPACE -l component=app -o jsonpath='{.items[0].metadata.name}')
    
    if [ -z "$APP_POD" ]; then
        error "App pod not found"
    fi
    
    # Check if app is responding
    kubectl exec -n $NAMESPACE $APP_POD -- curl -f http://localhost/health
    
    success "Health check passed"
}

# Setup monitoring
setup_monitoring() {
    log "Setting up monitoring..."
    
    # Add Prometheus Helm repository
    helm repo add prometheus-community https://prometheus-community.github.io/helm-charts
    helm repo update
    
    # Install Prometheus
    helm upgrade --install prometheus prometheus-community/kube-prometheus-stack \
        --namespace $NAMESPACE \
        --set prometheus.prometheusSpec.serviceMonitorSelectorNilUsesHelmValues=false \
        --set prometheus.prometheusSpec.podMonitorSelectorNilUsesHelmValues=false \
        --set prometheus.prometheusSpec.ruleSelectorNilUsesHelmValues=false
    
    success "Monitoring setup completed"
}

# Show status
show_status() {
    log "Showing deployment status..."
    
    echo "=== Namespace ==="
    kubectl get namespace $NAMESPACE
    
    echo "=== Pods ==="
    kubectl get pods -n $NAMESPACE
    
    echo "=== Services ==="
    kubectl get services -n $NAMESPACE
    
    echo "=== Ingress ==="
    kubectl get ingress -n $NAMESPACE
    
    echo "=== Persistent Volumes ==="
    kubectl get pv,pvc -n $NAMESPACE
    
    echo "=== Network Policies ==="
    kubectl get networkpolicies -n $NAMESPACE
}

# Cleanup
cleanup() {
    log "Cleaning up old resources..."
    
    # Remove old deployments
    kubectl delete deployment --all -n $NAMESPACE --ignore-not-found=true
    
    # Remove old services
    kubectl delete service --all -n $NAMESPACE --ignore-not-found=true
    
    # Remove old ingress
    kubectl delete ingress --all -n $NAMESPACE --ignore-not-found=true
    
    success "Cleanup completed"
}

# Rollback
rollback() {
    log "Rolling back deployment..."
    
    # Rollback deployments
    kubectl rollout undo deployment/dentaris-app -n $NAMESPACE
    kubectl rollout undo deployment/dentaris-mysql -n $NAMESPACE
    kubectl rollout undo deployment/dentaris-redis -n $NAMESPACE
    
    success "Rollback completed"
}

# Scale deployment
scale_deployment() {
    local replicas=$1
    
    log "Scaling deployment to $replicas replicas..."
    
    kubectl scale deployment dentaris-app --replicas=$replicas -n $NAMESPACE
    
    success "Deployment scaled to $replicas replicas"
}

# Show usage
usage() {
    echo "Usage: $0 [deploy|rollback|status|scale|cleanup]"
    echo ""
    echo "Commands:"
    echo "  deploy   - Deploy the application to Kubernetes"
    echo "  rollback - Rollback to previous version"
    echo "  status   - Show deployment status"
    echo "  scale    - Scale deployment (usage: $0 scale <replicas>)"
    echo "  cleanup  - Clean up old resources"
    echo ""
    echo "Environment variables:"
    echo "  KUBECONFIG - Path to kubeconfig file (default: ~/.kube/config)"
    echo "  NAMESPACE  - Kubernetes namespace (default: dentaris)"
}

# Main script logic
case "${1:-deploy}" in
    deploy)
        check_prerequisites
        create_namespace
        apply_manifests
        wait_for_deployments
        run_migrations
        health_check
        setup_monitoring
        show_status
        success "Deployment completed successfully"
        ;;
    rollback)
        rollback
        health_check
        success "Rollback completed successfully"
        ;;
    status)
        show_status
        ;;
    scale)
        if [ -z "$2" ]; then
            error "Please specify number of replicas"
        fi
        scale_deployment $2
        ;;
    cleanup)
        cleanup
        ;;
    *)
        usage
        exit 1
        ;;
esac





