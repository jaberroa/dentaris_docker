<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SecurityAuditLog;
use App\Models\User;
use App\Services\SecurityAuditService;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class GenerateSecurityReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:report {--days=30 : Number of days to analyze} {--output= : Output file path} {--format=html : Report format (html, json, txt)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a comprehensive security report';

    protected $securityAuditService;

    public function __construct(SecurityAuditService $securityAuditService)
    {
        parent::__construct();
        $this->securityAuditService = $securityAuditService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔒 Generando reporte de seguridad...');

        $days = $this->option('days');
        $report = $this->generateReport($days);
        $format = $this->option('format');
        $output = $this->option('output');

        if ($output) {
            $this->saveReport($report, $output, $format);
            $this->info("📄 Reporte guardado en: {$output}");
        } else {
            $this->displayReport($report, $format);
        }
    }

    protected function generateReport(int $days): array
    {
        $startDate = now()->subDays($days);
        
        return [
            'generated_at' => now()->toISOString(),
            'period' => [
                'start_date' => $startDate->toISOString(),
                'end_date' => now()->toISOString(),
                'days' => $days,
            ],
            'summary' => $this->getSecuritySummary($startDate),
            'threats' => $this->getThreatAnalysis($startDate),
            'users' => $this->getUserSecurityStats($startDate),
            'recommendations' => $this->getSecurityRecommendations($startDate),
            'compliance' => $this->getComplianceStatus(),
        ];
    }

    protected function getSecuritySummary(Carbon $startDate): array
    {
        $stats = SecurityAuditLog::getSecurityStats($startDate->diffInDays(now()));
        
        return [
            'total_events' => $stats['total_events'],
            'suspicious_events' => $stats['suspicious_events'],
            'high_risk_events' => $stats['high_risk_events'],
            'failed_logins' => $stats['failed_logins'],
            'unique_ips' => $stats['unique_ips'],
            'events_by_type' => $stats['events_by_type'],
            'security_score' => $this->calculateSecurityScore($stats),
        ];
    }

    protected function getThreatAnalysis(Carbon $startDate): array
    {
        $threats = [
            'failed_login_attempts' => SecurityAuditLog::where('event_time', '>=', $startDate)
                ->where('event_type', 'failed_login')
                ->count(),
            'suspicious_activities' => SecurityAuditLog::where('event_time', '>=', $startDate)
                ->where('is_suspicious', true)
                ->count(),
            'high_risk_events' => SecurityAuditLog::where('event_time', '>=', $startDate)
                ->whereIn('risk_level', ['high', 'critical'])
                ->count(),
            'unique_suspicious_ips' => SecurityAuditLog::where('event_time', '>=', $startDate)
                ->where('is_suspicious', true)
                ->distinct('ip_address')
                ->count('ip_address'),
        ];

        // Get top suspicious IPs
        $topSuspiciousIps = SecurityAuditLog::where('event_time', '>=', $startDate)
            ->where('is_suspicious', true)
            ->selectRaw('ip_address, COUNT(*) as count')
            ->groupBy('ip_address')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        $threats['top_suspicious_ips'] = $topSuspiciousIps;

        return $threats;
    }

    protected function getUserSecurityStats(Carbon $startDate): array
    {
        $users = User::withCount([
            'securityAuditLogs as total_events' => function ($query) use ($startDate) {
                $query->where('event_time', '>=', $startDate);
            },
            'securityAuditLogs as suspicious_events' => function ($query) use ($startDate) {
                $query->where('event_time', '>=', $startDate)->where('is_suspicious', true);
            },
            'securityAuditLogs as failed_logins' => function ($query) use ($startDate) {
                $query->where('event_time', '>=', $startDate)->where('event_type', 'failed_login');
            },
        ])->get();

        return [
            'total_users' => $users->count(),
            'users_with_2fa' => $users->where('google2fa_enabled', true)->count(),
            'locked_users' => $users->where('is_locked', true)->count(),
            'users_with_suspicious_activity' => $users->where('suspicious_events', '>', 0)->count(),
            'top_risk_users' => $users->sortByDesc('suspicious_events')->take(5)->values(),
        ];
    }

    protected function getSecurityRecommendations(Carbon $startDate): array
    {
        $recommendations = [];

        // Check for high failed login rate
        $failedLogins = SecurityAuditLog::where('event_time', '>=', $startDate)
            ->where('event_type', 'failed_login')
            ->count();

        if ($failedLogins > 10) {
            $recommendations[] = [
                'priority' => 'high',
                'category' => 'authentication',
                'title' => 'Alto número de intentos de login fallidos',
                'description' => "Se detectaron {$failedLogins} intentos de login fallidos. Considera implementar bloqueo de IPs o CAPTCHA.",
            ];
        }

        // Check for users without 2FA
        $usersWithout2FA = User::where('google2fa_enabled', false)->count();
        $totalUsers = User::count();

        if ($usersWithout2FA > 0 && $totalUsers > 0) {
            $percentage = round(($usersWithout2FA / $totalUsers) * 100, 2);
            $recommendations[] = [
                'priority' => 'medium',
                'category' => '2fa',
                'title' => 'Usuarios sin autenticación de dos factores',
                'description' => "{$usersWithout2FA} usuarios ({$percentage}%) no tienen 2FA habilitado. Considera hacer obligatorio el 2FA.",
            ];
        }

        // Check for suspicious activities
        $suspiciousActivities = SecurityAuditLog::where('event_time', '>=', $startDate)
            ->where('is_suspicious', true)
            ->count();

        if ($suspiciousActivities > 5) {
            $recommendations[] = [
                'priority' => 'high',
                'category' => 'monitoring',
                'title' => 'Actividades sospechosas detectadas',
                'description' => "Se detectaron {$suspiciousActivities} actividades sospechosas. Revisa los logs y considera medidas adicionales.",
            ];
        }

        return $recommendations;
    }

    protected function getComplianceStatus(): array
    {
        return [
            'gdpr_compliance' => [
                'data_encryption' => config('security.encryption.enabled', true),
                'audit_logging' => config('security.audit_logging.enabled', true),
                'data_retention' => config('security.compliance.data_retention_days', 2555),
                'right_to_be_forgotten' => config('security.compliance.right_to_be_forgotten', true),
            ],
            'hipaa_compliance' => [
                'data_encryption' => config('security.encryption.enabled', true),
                'access_controls' => true, // Based on implemented RBAC
                'audit_trails' => config('security.audit_logging.enabled', true),
                'data_backup' => config('security.backup_security.encrypt_backups', true),
            ],
            'security_headers' => [
                'x_frame_options' => config('security.headers.x_frame_options', 'DENY'),
                'x_content_type_options' => config('security.headers.x_content_type_options', 'nosniff'),
                'x_xss_protection' => config('security.headers.x_xss_protection', '1; mode=block'),
                'content_security_policy' => config('security.headers.content_security_policy', true),
            ],
        ];
    }

    protected function calculateSecurityScore(array $stats): int
    {
        $score = 100;

        // Deduct points for security issues
        if ($stats['suspicious_events'] > 0) {
            $score -= min($stats['suspicious_events'] * 5, 30);
        }

        if ($stats['failed_logins'] > 10) {
            $score -= min(($stats['failed_logins'] - 10) * 2, 20);
        }

        if ($stats['high_risk_events'] > 0) {
            $score -= min($stats['high_risk_events'] * 10, 40);
        }

        return max($score, 0);
    }

    protected function saveReport(array $report, string $output, string $format): void
    {
        $content = match ($format) {
            'json' => json_encode($report, JSON_PRETTY_PRINT),
            'html' => $this->generateHtmlReport($report),
            'txt' => $this->generateTextReport($report),
            default => json_encode($report, JSON_PRETTY_PRINT),
        };

        File::put($output, $content);
    }

    protected function displayReport(array $report, string $format): void
    {
        match ($format) {
            'json' => $this->line(json_encode($report, JSON_PRETTY_PRINT)),
            'html' => $this->line($this->generateHtmlReport($report)),
            'txt' => $this->line($this->generateTextReport($report)),
            default => $this->line(json_encode($report, JSON_PRETTY_PRINT)),
        };
    }

    protected function generateHtmlReport(array $report): string
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>Security Report - ' . $report['generated_at'] . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin-bottom: 30px; }
        .metric { margin: 10px 0; }
        .recommendation { padding: 10px; margin: 5px 0; border-left: 4px solid #007bff; background: #f8f9fa; }
        .high { border-left-color: #dc3545; }
        .medium { border-left-color: #ffc107; }
        .low { border-left-color: #28a745; }
        .security-score { font-size: 24px; font-weight: bold; }
        .good { color: #28a745; }
        .warning { color: #ffc107; }
        .danger { color: #dc3545; }
    </style>
</head>
<body>
    <h1>🔒 Security Report</h1>
    <p>Generated: ' . $report['generated_at'] . '</p>
    <p>Period: ' . $report['period']['start_date'] . ' to ' . $report['period']['end_date'] . '</p>';

        // Security Score
        $score = $report['summary']['security_score'];
        $scoreClass = $score >= 80 ? 'good' : ($score >= 60 ? 'warning' : 'danger');
        $html .= '<div class="section">
            <h2>Security Score</h2>
            <div class="security-score ' . $scoreClass . '">' . $score . '/100</div>
        </div>';

        // Summary
        $html .= '<div class="section"><h2>Summary</h2>';
        foreach ($report['summary'] as $key => $value) {
            if ($key !== 'security_score') {
                $html .= "<div class='metric'><strong>{$key}:</strong> {$value}</div>";
            }
        }
        $html .= '</div>';

        // Recommendations
        if (!empty($report['recommendations'])) {
            $html .= '<div class="section"><h2>Recommendations</h2>';
            foreach ($report['recommendations'] as $rec) {
                $html .= "<div class='recommendation {$rec['priority']}'>
                    <strong>{$rec['title']}</strong><br>
                    {$rec['description']}
                </div>";
            }
            $html .= '</div>';
        }

        $html .= '</body></html>';
        return $html;
    }

    protected function generateTextReport(array $report): string
    {
        $text = "SECURITY REPORT\n";
        $text .= "Generated: " . $report['generated_at'] . "\n";
        $text .= "Period: " . $report['period']['start_date'] . " to " . $report['period']['end_date'] . "\n\n";

        $text .= "SECURITY SCORE: " . $report['summary']['security_score'] . "/100\n\n";

        $text .= "SUMMARY\n";
        $text .= "=======\n";
        foreach ($report['summary'] as $key => $value) {
            if ($key !== 'security_score') {
                $text .= "{$key}: {$value}\n";
            }
        }

        $text .= "\nRECOMMENDATIONS\n";
        $text .= "==============\n";
        foreach ($report['recommendations'] as $rec) {
            $text .= "[{$rec['priority']}] {$rec['title']}: {$rec['description']}\n";
        }

        return $text;
    }
}