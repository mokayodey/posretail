<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Mail;

class HealthCheckController extends Controller
{
    public function check()
    {
        $status = [
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'services' => []
        ];

        // Check database connection
        try {
            DB::connection()->getPdo();
            $status['services']['database'] = 'healthy';
        } catch (\Exception $e) {
            $status['services']['database'] = 'unhealthy';
            $status['status'] = 'unhealthy';
        }

        // Check cache
        try {
            Cache::put('health-check', 'ok', 1);
            $status['services']['cache'] = 'healthy';
        } catch (\Exception $e) {
            $status['services']['cache'] = 'unhealthy';
            $status['status'] = 'unhealthy';
        }

        // Check Redis
        try {
            Redis::ping();
            $status['services']['redis'] = 'healthy';
        } catch (\Exception $e) {
            $status['services']['redis'] = 'unhealthy';
            $status['status'] = 'unhealthy';
        }

        // Check mail
        try {
            Mail::raw('Health check', function ($message) {
                $message->to('health@posretail.pipeops.app')
                    ->subject('Health Check');
            });
            $status['services']['mail'] = 'healthy';
        } catch (\Exception $e) {
            $status['services']['mail'] = 'unhealthy';
            $status['status'] = 'unhealthy';
        }

        return response()->json($status, $status['status'] === 'healthy' ? 200 : 503);
    }

    public function metrics()
    {
        $metrics = [
            'timestamp' => now()->toIso8601String(),
            'metrics' => [
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
                'uptime' => exec('uptime'),
                'load_average' => sys_getloadavg(),
            ]
        ];

        return response()->json($metrics);
    }

    public function sendNotification()
    {
        $health = $this->checkHealth();
        
        Mail::send('emails.health', ['health' => $health], function($message) {
            $message->to('health@posretail-api.pipeops.app')
                    ->subject('Health Check Notification');
        });
        
        return response()->json(['message' => 'Notification sent']);
    }
} 