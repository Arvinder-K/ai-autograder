$currentPath = [Environment]::GetEnvironmentVariable('PATH', 'Machine')
$newPath = $currentPath.Replace('C:\xampp\php;', 'C:\xampp\php84;')
if ($currentPath -ne $newPath) {
    [Environment]::SetEnvironmentVariable('PATH', $newPath, 'Machine')
    Write-Host "Successfully updated PATH to use PHP 8.4"
    Write-Host "Old: C:\xampp\php"
    Write-Host "New: C:\xampp\php84"
} else {
    Write-Host "PATH already points to PHP 8.4 or pattern not found"
}
