# Script tải audio từ các file link về thư mục tương ứng
# Chạy: powershell -ExecutionPolicy Bypass -File download-audio.ps1

$ErrorActionPreference = 'Continue'
$baseDir = $PSScriptRoot

$jobs = @(
    @{ LinkFile = 'audio-start\audio-link-start.txt'; DestDir = 'audio-start' },
    @{ LinkFile = 'audio-end\audio-link-end.txt';   DestDir = 'audio-end' }
)

foreach ($job in $jobs) {
    $linkPath = Join-Path $baseDir $job.LinkFile
    $destDir  = Join-Path $baseDir $job.DestDir

    if (-not (Test-Path $linkPath)) {
        Write-Host "Khong tim thay file: $linkPath" -ForegroundColor Yellow
        continue
    }
    if (-not (Test-Path $destDir)) {
        New-Item -ItemType Directory -Path $destDir -Force | Out-Null
    }

    $links = Get-Content $linkPath | Where-Object { $_.Trim() -ne '' }
    Write-Host "=== $($job.DestDir): $($links.Count) file ===" -ForegroundColor Cyan

    $ok = 0; $fail = 0
    foreach ($url in $links) {
        $url = $url.Trim()
        $fileName = [System.IO.Path]::GetFileName($url)
        $destFile = Join-Path $destDir $fileName

        if (Test-Path $destFile) {
            Write-Host "  [SKIP] $fileName (da ton tai)" -ForegroundColor DarkGray
            $ok++
            continue
        }

        try {
            Invoke-WebRequest -Uri $url -OutFile $destFile -UseBasicParsing
            Write-Host "  [OK]   $fileName" -ForegroundColor Green
            $ok++
        } catch {
            Write-Host "  [FAIL] $fileName -> $($_.Exception.Message)" -ForegroundColor Red
            $fail++
        }
    }
    Write-Host "--- $($job.DestDir): thanh cong $ok, that bai $fail ---" -ForegroundColor Cyan
}

Write-Host "Hoan tat!" -ForegroundColor Green
