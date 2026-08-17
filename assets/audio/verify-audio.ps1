$base = "c:\laragon\www\vongquay\wp-content\plugins\spin-whell\assets\audio"

$jobs = @(
    @{ Link = 'audio-link-start.txt'; Dir = 'audio-start' },
    @{ Link = 'audio-link-end.txt';   Dir = 'audio-end' }
)

foreach ($job in $jobs) {
    $links = Get-Content (Join-Path $base $job.Link) | Where-Object { $_.Trim() -ne '' }
    $missing = @()
    foreach ($url in $links) {
        $f = [System.IO.Path]::GetFileName($url.Trim())
        if (-not (Test-Path (Join-Path $base (Join-Path $job.Dir $f)))) {
            $missing += $f
        }
    }
    Write-Host "$($job.Dir): $($links.Count) links, missing: $($missing.Count)"
    if ($missing.Count -gt 0) {
        $missing | ForEach-Object { Write-Host "  MISSING: $_" }
    }
}
