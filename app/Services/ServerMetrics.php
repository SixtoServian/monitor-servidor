<?php

namespace App\Services;

class ServerMetrics
{
    /**
     * Obtiene un "snapshot" (foto) de las métricas del servidor en el momento exacto.
     * 
     * @return array Arreglo estructurado con los datos del sistema.
     */
    public function snapshot(): array
    {
        // 1. Detectamos el Sistema Operativo de forma automática.
        $isWindows = PHP_OS_FAMILY === 'Windows';

        // 2. Datos que PHP puede leer de forma nativa en CUALQUIER SO
        $data = [
            'hostname' => gethostname(), // Nombre del equipo
            'os'       => php_uname('s') . ' ' . php_uname('r'), // SO y versión
            'php'      => PHP_VERSION, // Versión de PHP
            'disk'     => $this->getDiskMetrics(), // Disco
            'at'       => now()->toIso8601String(), // Fecha y hora actual
        ];

        // 3. Datos específicos según el SO
        if ($isWindows) {
            $specifics = $this->getWindowsMetrics();
        } else {
            $specifics = $this->getLinuxMetrics();
        }

        // 4. Unimos todo y devolvemos
        return array_merge($data, $specifics);
    }

    /**
     * Calcula el espacio en disco. Funciona tanto en Windows como en Linux.
     */
    private function getDiskMetrics(): array
    {
        // Usamos base_path() para medir el disco donde está alojado el proyecto
        $path = base_path(); 
        
        $total = disk_total_space($path);
        $free  = disk_free_space($path);
        
        $usedPct = $total > 0 ? round((($total - $free) / $total) * 100, 1) : 0;

        return [
            'total_gb' => round($total / (1024 ** 3), 1),
            'free_gb'  => round($free / (1024 ** 3), 1),
            'used_pct' => $usedPct,
        ];
    }

    /**
     * LEE MÉTRICAS EN WINDOWS (Usa PowerShell y WMI)
     */
    private function getWindowsMetrics(): array
    {
        // Script de PowerShell solicitado en el TP (ahora sí con variables escapadas correctamente)
        $psScript = <<<'PS'
            $os = Get-CimInstance Win32_OperatingSystem;
            $cpu = Get-CimInstance Win32_Processor;
            $temp = Get-CimInstance MSAcpi_ThermalZoneTemperature -ErrorAction SilentlyContinue;
            $data = @{
                total_mb = [math]::Round($os.TotalVisibleMemorySize / 1024);
                available_mb = [math]::Round($os.FreePhysicalMemory / 1024);
                cpu_pct = $cpu.LoadPercentage;
                uptime_s = [math]::Round(((Get-Date) - $os.LastBootUpTime).TotalSeconds);
                cpu_temp_c = if ($temp) { [math]::Round(($temp.CurrentTemperature / 10) - 273.15, 1) } else { $null }
            };
            $data | ConvertTo-Json -Compress
        PS;

        // Codificamos en Base64 (UTF-16LE) para evitar problemas con comillas
        $encodedCommand = base64_encode(mb_convert_encoding($psScript, 'UTF-16LE', 'UTF-8'));

        // Ejecutamos
        $output = shell_exec("powershell -NoProfile -EncodedCommand {$encodedCommand} 2>&1");
        $data = json_decode($output, true);

        return [
            'memory' => [
                'total_mb'     => $data['total_mb'] ?? null,
                'available_mb' => $data['available_mb'] ?? null,
                'used_pct'     => isset($data['total_mb'], $data['available_mb']) && $data['total_mb'] > 0
                                  ? round((1 - ($data['available_mb'] / $data['total_mb'])) * 100, 1) 
                                  : null,
            ],
            'cpu_pct'    => $data['cpu_pct'] ?? null,
            'uptime_s'   => $data['uptime_s'] ?? null,
            'cpu_temp_c' => $data['cpu_temp_c'] ?? null,
        ];
    }

    /**
     * LEE MÉTRICAS EN LINUX (Debian 12 en tu caso)
     * Lee directamente de los archivos virtuales del sistema (/proc y /sys).
     */
    private function getLinuxMetrics(): array
    {
        // 1. MEMORIA (/proc/meminfo)
        $meminfo = @file_get_contents('/proc/meminfo') ?: '';
        preg_match('/^MemTotal:\s+(\d+)\s+kB/m', $meminfo, $totalMatch);
        preg_match('/^MemAvailable:\s+(\d+)\s+kB/m', $meminfo, $availMatch);

        $totalKb = (int)($totalMatch[1] ?? 0);
        $availKb = (int)($availMatch[1] ?? 0);
        $totalMb = round($totalKb / 1024);
        $availMb = round($availKb / 1024);

        // 2. UPTIME (/proc/uptime)
        $uptimeData = @file_get_contents('/proc/uptime') ?: '0 0';
        $uptimeS = (int)explode(' ', $uptimeData)[0];

        // 3. CPU LOAD (Carga del sistema en base a los núcleos)
        $load = sys_getloadavg();
        $cpuCores = (int)shell_exec('nproc') ?: 1;
        $cpuPct = $load ? round(($load[0] / $cpuCores) * 100, 1) : null;

        // 4. TEMPERATURA (/sys/class/thermal/...)
        $tempFile = '/sys/class/thermal/thermal_zone0/temp';
        $cpuTemp = file_exists($tempFile) ? round((int)file_get_contents($tempFile) / 1000, 1) : null;

        return [
            'memory' => [
                'total_mb'     => $totalMb,
                'available_mb' => $availMb,
                'used_pct'     => $totalMb > 0 ? round((1 - ($availMb / $totalMb)) * 100, 1) : null,
            ],
            'cpu_pct'    => $cpuPct,
            'uptime_s'   => $uptimeS,
            'cpu_temp_c' => $cpuTemp,
        ];
    }   
}