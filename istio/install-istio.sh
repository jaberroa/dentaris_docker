#!/bin/bash

# Istio Installation Script for Dentaris
# This script installs and configures Istio Service Mesh

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
ISTIO_VERSION="1.19.0"
NAMESPACE="istio-system"
DENTARIS_NAMESPACE="dentaris"
LOG_FILE="/var/log/istio-install.log"

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
    
    # Check if kubectl can connect to cluster
    if ! kubectl cluster-info &> /dev/null; then
        error "Cannot connect to Kubernetes cluster"
    fi
    
    # Check if cluster supports Istio
    kubectl get nodes --no-headers | while read node status roles age version; do
        if [[ $version == *"v1.24"* ]] || [[ $version == *"v1.25"* ]] || [[ $version == *"v1.26"* ]] || [[ $version == *"v1.27"* ]] || [[ $version == *"v1.28"* ]]; then
            success "Kubernetes version $version is supported"
        else
            warning "Kubernetes version $version may not be fully supported"
        fi
    done
    
    success "Prerequisites check passed"
}

# Download Istio
download_istio() {
    log "Downloading Istio $ISTIO_VERSION..."
    
    # Create temporary directory
    TEMP_DIR=$(mktemp -d)
    cd $TEMP_DIR
    
    # Download Istio
    curl -L https://istio.io/downloadIstio | ISTIO_VERSION=$ISTIO_VERSION sh -
    
    # Add to PATH
    export PATH=$PWD/istio-$ISTIO_VERSION/bin:$PATH
    
    # Verify installation
    istioctl version --remote=false
    
    success "Istio downloaded and verified"
}

# Install Istio
install_istio() {
    log "Installing Istio..."
    
    # Create istio-system namespace
    kubectl create namespace $NAMESPACE --dry-run=client -o yaml | kubectl apply -f -
    
    # Install Istio with demo profile
    istioctl install --set values.defaultRevision=default -y
    
    # Verify installation
    kubectl get pods -n $NAMESPACE
    
    success "Istio installed successfully"
}

# Install Istio addons
install_addons() {
    log "Installing Istio addons..."
    
    # Install Prometheus
    kubectl apply -f https://raw.githubusercontent.com/istio/istio/release-1.19/samples/addons/prometheus.yaml
    
    # Install Grafana
    kubectl apply -f https://raw.githubusercontent.com/istio/istio/release-1.19/samples/addons/grafana.yaml
    
    # Install Jaeger
    kubectl apply -f https://raw.githubusercontent.com/istio/istio/release-1.19/samples/addons/jaeger.yaml
    
    # Install Kiali
    kubectl apply -f https://raw.githubusercontent.com/istio/istio/release-1.19/samples/addons/kiali.yaml
    
    # Install Zipkin
    kubectl apply -f https://raw.githubusercontent.com/istio/istio/release-1.19/samples/addons/extras/zipkin.yaml
    
    # Wait for addons to be ready
    kubectl wait --for=condition=available --timeout=300s deployment/prometheus -n $NAMESPACE
    kubectl wait --for=condition=available --timeout=300s deployment/grafana -n $NAMESPACE
    kubectl wait --for=condition=available --timeout=300s deployment/jaeger -n $NAMESPACE
    kubectl wait --for=condition=available --timeout=300s deployment/kiali -n $NAMESPACE
    
    success "Istio addons installed successfully"
}

# Enable Istio for Dentaris namespace
enable_istio() {
    log "Enabling Istio for Dentaris namespace..."
    
    # Label namespace for Istio injection
    kubectl label namespace $DENTARIS_NAMESPACE istio-injection=enabled --overwrite
    
    # Verify namespace is labeled
    kubectl get namespace $DENTARIS_NAMESPACE --show-labels
    
    success "Istio enabled for Dentaris namespace"
}

# Configure Istio for Dentaris
configure_istio() {
    log "Configuring Istio for Dentaris..."
    
    # Apply Istio configurations
    kubectl apply -f istio/gateway.yaml
    kubectl apply -f istio/virtual-services.yaml
    kubectl apply -f istio/destination-rules.yaml
    kubectl apply -f istio/security-policies.yaml
    kubectl apply -f istio/observability.yaml
    
    success "Istio configured for Dentaris"
}

# Verify installation
verify_installation() {
    log "Verifying Istio installation..."
    
    # Check Istio control plane
    kubectl get pods -n $NAMESPACE
    
    # Check Istio configuration
    istioctl analyze
    
    # Check Dentaris namespace
    kubectl get pods -n $DENTARIS_NAMESPACE
    
    # Check Istio sidecars
    kubectl get pods -n $DENTARIS_NAMESPACE -o jsonpath='{range .items[*]}{.metadata.name}{"\t"}{.spec.containers[*].name}{"\n"}{end}' | grep istio-proxy
    
    success "Istio installation verified"
}

# Show access information
show_access_info() {
    log "Showing access information..."
    
    echo "=== Istio Control Plane ==="
    kubectl get pods -n $NAMESPACE
    
    echo "=== Istio Services ==="
    kubectl get services -n $NAMESPACE
    
    echo "=== Istio Gateway ==="
    kubectl get gateway -n $DENTARIS_NAMESPACE
    
    echo "=== Virtual Services ==="
    kubectl get virtualservice -n $DENTARIS_NAMESPACE
    
    echo "=== Destination Rules ==="
    kubectl get destinationrule -n $DENTARIS_NAMESPACE
    
    echo "=== Security Policies ==="
    kubectl get peerauthentication -n $DENTARIS_NAMESPACE
    kubectl get authorizationpolicy -n $DENTARIS_NAMESPACE
    
    echo "=== Access URLs ==="
    echo "Kiali: kubectl port-forward -n $NAMESPACE svc/kiali 20001:20001"
    echo "Grafana: kubectl port-forward -n $NAMESPACE svc/grafana 3000:3000"
    echo "Jaeger: kubectl port-forward -n $NAMESPACE svc/tracing 16686:80"
    echo "Prometheus: kubectl port-forward -n $NAMESPACE svc/prometheus 9090:9090"
}

# Cleanup
cleanup() {
    log "Cleaning up temporary files..."
    
    # Remove temporary directory
    rm -rf $TEMP_DIR
    
    success "Cleanup completed"
}

# Main installation function
install() {
    log "Starting Istio installation for Dentaris..."
    
    check_prerequisites
    download_istio
    install_istio
    install_addons
    enable_istio
    configure_istio
    verify_installation
    show_access_info
    cleanup
    
    success "Istio installation completed successfully"
}

# Uninstall Istio
uninstall() {
    log "Uninstalling Istio..."
    
    # Remove Istio configurations
    kubectl delete -f istio/observability.yaml --ignore-not-found=true
    kubectl delete -f istio/security-policies.yaml --ignore-not-found=true
    kubectl delete -f istio/destination-rules.yaml --ignore-not-found=true
    kubectl delete -f istio/virtual-services.yaml --ignore-not-found=true
    kubectl delete -f istio/gateway.yaml --ignore-not-found=true
    
    # Remove Istio addons
    kubectl delete -f https://raw.githubusercontent.com/istio/istio/release-1.19/samples/addons/kiali.yaml --ignore-not-found=true
    kubectl delete -f https://raw.githubusercontent.com/istio/istio/release-1.19/samples/addons/jaeger.yaml --ignore-not-found=true
    kubectl delete -f https://raw.githubusercontent.com/istio/istio/release-1.19/samples/addons/grafana.yaml --ignore-not-found=true
    kubectl delete -f https://raw.githubusercontent.com/istio/istio/release-1.19/samples/addons/prometheus.yaml --ignore-not-found=true
    
    # Remove Istio
    istioctl uninstall --purge -y
    
    # Remove namespace
    kubectl delete namespace $NAMESPACE --ignore-not-found=true
    
    success "Istio uninstalled successfully"
}

# Show usage
usage() {
    echo "Usage: $0 [install|uninstall|status|verify]"
    echo ""
    echo "Commands:"
    echo "  install   - Install Istio Service Mesh"
    echo "  uninstall - Uninstall Istio Service Mesh"
    echo "  status    - Show Istio status"
    echo "  verify    - Verify Istio installation"
    echo ""
    echo "Environment variables:"
    echo "  ISTIO_VERSION - Istio version to install (default: 1.19.0)"
    echo "  NAMESPACE     - Istio namespace (default: istio-system)"
}

# Main script logic
case "${1:-install}" in
    install)
        install
        ;;
    uninstall)
        uninstall
        ;;
    status)
        show_access_info
        ;;
    verify)
        verify_installation
        ;;
    *)
        usage
        exit 1
        ;;
esac





