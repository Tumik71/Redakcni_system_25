param(
  [string]$RepoPath = (Get-Location).Path,
  [int]$DebounceMs = 5000
)

Write-Host "Auto-push běží v: $RepoPath"
Set-Location $RepoPath

function HasChanges {
  $s = git status --porcelain
  return -not [string]::IsNullOrWhiteSpace($s)
}

function DoPush {
  if (HasChanges) {
    git add -A
    $msg = "Auto push: " + (Get-Date -Format "yyyy-MM-dd HH:mm:ss")
    git commit -m $msg 2>$null
    git push 2>$null
    Write-Host "[$(Get-Date)] Pushed"
  } else {
    Write-Host "[$(Get-Date)] Bez změn"
  }
}

$pending = $false
function QueuePush { $script:pending = $true }

$watcher = New-Object System.IO.FileSystemWatcher $RepoPath, '*'
$watcher.IncludeSubdirectories = $true
$watcher.EnableRaisingEvents = $true

$ignore = @('.git','node_modules','vendor','.trae','.vscode','.idea')
function ShouldIgnore($path) {
  foreach ($i in $ignore) { if ($path -like "*\$i*") { return $true } }
  return $false
}

Register-ObjectEvent -InputObject $watcher -EventName Changed -Action { if (-not (ShouldIgnore($Event.SourceEventArgs.FullPath))) { QueuePush } } | Out-Null
Register-ObjectEvent -InputObject $watcher -EventName Created -Action { if (-not (ShouldIgnore($Event.SourceEventArgs.FullPath))) { QueuePush } } | Out-Null
Register-ObjectEvent -InputObject $watcher -EventName Deleted -Action { if (-not (ShouldIgnore($Event.SourceEventArgs.FullPath))) { QueuePush } } | Out-Null
Register-ObjectEvent -InputObject $watcher -EventName Renamed -Action { if (-not (ShouldIgnore($Event.SourceEventArgs.FullPath))) { QueuePush } } | Out-Null

$timer = New-Object System.Timers.Timer($DebounceMs)
$timer.AutoReset = $true
$timer.Enabled = $true
Register-ObjectEvent -InputObject $timer -EventName Elapsed -Action { if ($script:pending) { $script:pending = $false; DoPush } } | Out-Null

DoPush
Write-Host "Čekám na změny… stiskněte Ctrl+C pro ukončení"
while ($true) { Start-Sleep -Seconds 5 }
