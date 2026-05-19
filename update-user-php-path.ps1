$currentPath = [Environment]::GetEnvironmentVariable('PATH', 'User')
if ($currentPath) {
    $newPath = $currentPath.Replace('C:\xampp\php;', 'C:\xampp\php84;')
    if ($currentPath -ne $newPath) {
        [Environment]::SetEnvironmentVariable('PATH', $newPath, 'User')
        Write-Host "Updated User PATH to PHP 8.4"
    } else {
        Write-Host "User PATH does not contain C:\xampp\php entry"
    }
} else {
    Write-Host "No User PATH set. Adding C:\xampp\php84"
    [Environment]::SetEnvironmentVariable('PATH', 'C:\xampp\php84', 'User')
}

# Verify
$verifyPath = [Environment]::GetEnvironmentVariable('PATH', 'User')
Write-Host "User PATH: $verifyPath"
