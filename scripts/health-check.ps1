# Dentaris Health Check Script for Windows
# This script performs health checks on the Dentaris application

param(
    [string]$BaseUrl = "http://localhost",
    [int]$Timeout = 30,
    [switch]$Verbose
)

# Colors for output
$Red = "Red"
$Green = "Green"
$Yellow = "Yellow"
$Blue = "Blue"

# Functions
function Write-Log {
    param([string]$Message, [string]$Color = "White")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Write-Host "[$timestamp] $Message" -ForegroundColor $Color
}

function Write-Success {
    param([string]$Message)
    Write-Host "✓ $Message" -ForegroundColor $Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "⚠ $Message" -ForegroundColor $Yellow
}

function Write-Error {
    param([string]$Message)
    Write-Host "✗ $Message" -ForegroundColor $Red
}

# Health check functions
function Test-ApplicationHealth {
    param([string]$Url)
    
    try {
        $response = Invoke-WebRequest -Uri "$Url/health" -TimeoutSec $Timeout -UseBasicParsing
        if ($response.StatusCode -eq 200) {
            Write-Success "Application health check passed"
            return $true
        } else {
            Write-Error "Application health check failed with status: $($response.StatusCode)"
            return $false
        }
    } catch {
        Write-Error "Application health check failed: $($_.Exception.Message)"
        return $false
    }
}

function Test-ApiHealth {
    param([string]$Url)
    
    try {
        $response = Invoke-WebRequest -Uri "$Url/api/health" -TimeoutSec $Timeout -UseBasicParsing
        if ($response.StatusCode -eq 200) {
            Write-Success "API health check passed"
            return $true
        } else {
            Write-Error "API health check failed with status: $($response.StatusCode)"
            return $false
        }
    } catch {
        Write-Error "API health check failed: $($_.Exception.Message)"
        return $false
    }
}

function Test-DatabaseConnection {
    try {
        # Check if Docker containers are running
        $mysqlContainer = docker ps --filter "name=dentaris_mysql" --format "{{.Names}}"
        if ($mysqlContainer -eq "dentaris_mysql") {
            Write-Success "MySQL container is running"
            return $true
        } else {
            Write-Error "MySQL container is not running"
            return $false
        }
    } catch {
        Write-Error "Database connection check failed: $($_.Exception.Message)"
        return $false
    }
}

function Test-RedisConnection {
    try {
        # Check if Redis container is running
        $redisContainer = docker ps --filter "name=dentaris_redis" --format "{{.Names}}"
        if ($redisContainer -eq "dentaris_redis") {
            Write-Success "Redis container is running"
            return $true
        } else {
            Write-Error "Redis container is not running"
            return $false
        }
    } catch {
        Write-Error "Redis connection check failed: $($_.Exception.Message)"
        return $false
    }
}

function Test-SecurityHeaders {
    param([string]$Url)
    
    try {
        $response = Invoke-WebRequest -Uri $Url -TimeoutSec $Timeout -UseBasicParsing
        
        $requiredHeaders = @(
            "X-Frame-Options",
            "X-Content-Type-Options",
            "X-XSS-Protection",
            "Referrer-Policy"
        )
        
        $missingHeaders = @()
        foreach ($header in $requiredHeaders) {
            if (-not $response.Headers.ContainsKey($header)) {
                $missingHeaders += $header
            }
        }
        
        if ($missingHeaders.Count -eq 0) {
            Write-Success "All security headers are present"
            return $true
        } else {
            Write-Warning "Missing security headers: $($missingHeaders -join ', ')"
            return $false
        }
    } catch {
        Write-Error "Security headers check failed: $($_.Exception.Message)"
        return $false
    }
}

function Test-ResponseTime {
    param([string]$Url)
    
    try {
        $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
        $response = Invoke-WebRequest -Uri $Url -TimeoutSec $Timeout -UseBasicParsing
        $stopwatch.Stop()
        
        $responseTime = $stopwatch.ElapsedMilliseconds
        
        if ($responseTime -lt 2000) {  # Less than 2 seconds
            Write-Success "Response time is acceptable: $responseTime ms"
            return $true
        } else {
            Write-Warning "Response time is slow: $responseTime ms"
            return $false
        }
    } catch {
        Write-Error "Response time check failed: $($_.Exception.Message)"
        return $false
    }
}

function Test-SslCertificate {
    param([string]$Url)
    
    try {
        if ($Url.StartsWith("https://")) {
            $request = [System.Net.WebRequest]::Create($Url)
            $request.Timeout = $Timeout * 1000
            
            $response = $request.GetResponse()
            $response.Close()
            
            Write-Success "SSL certificate is valid"
            return $true
        } else {
            Write-Warning "SSL certificate check skipped (not HTTPS)"
            return $true
        }
    } catch {
        Write-Error "SSL certificate check failed: $($_.Exception.Message)"
        return $false
    }
}

function Test-LogFiles {
    try {
        $logPath = "storage/logs/laravel.log"
        if (Test-Path $logPath) {
            $logContent = Get-Content $logPath -Tail 100
            $errorCount = ($logContent | Select-String "ERROR").Count
            
            if ($errorCount -eq 0) {
                Write-Success "No errors found in log files"
                return $true
            } elseif ($errorCount -lt 10) {
                Write-Warning "Found $errorCount errors in log files"
                return $true
            } else {
                Write-Error "Too many errors in log files: $errorCount"
                return $false
            }
        } else {
            Write-Warning "Log file not found: $logPath"
            return $true
        }
    } catch {
        Write-Error "Log file check failed: $($_.Exception.Message)"
        return $false
    }
}

# Main health check function
function Start-HealthCheck {
    Write-Log "Starting Dentaris health check..." $Blue
    
    $results = @{
        Application = Test-ApplicationHealth -Url $BaseUrl
        Api = Test-ApiHealth -Url $BaseUrl
        Database = Test-DatabaseConnection
        Redis = Test-RedisConnection
        SecurityHeaders = Test-SecurityHeaders -Url $BaseUrl
        ResponseTime = Test-ResponseTime -Url $BaseUrl
        SslCertificate = Test-SslCertificate -Url $BaseUrl
        LogFiles = Test-LogFiles
    }
    
    $passedChecks = ($results.Values | Where-Object { $_ -eq $true }).Count
    $totalChecks = $results.Count
    
    Write-Log "Health check completed: $passedChecks/$totalChecks checks passed" $Blue
    
    if ($passedChecks -eq $totalChecks) {
        Write-Success "All health checks passed!"
        exit 0
    } else {
        Write-Error "Some health checks failed!"
        exit 1
    }
}

# Run health check
Start-HealthCheck





