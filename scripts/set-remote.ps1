param(
  [Parameter(Mandatory=$true)] [string]$RepoUrl
)

git remote remove origin 2>$null
git remote add origin $RepoUrl
git branch -M main
Write-Host "Remote nastaveno na $RepoUrl a větev přejmenována na 'main'"
