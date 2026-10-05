$filePath = "i:\Work\SIMPLE-CREATIVE\Wordpress\Airsoft\wp-content\themes\betheme\functions\builder\class-mfn-builder-fields.php"
$lines = [System.IO.File]::ReadAllLines($filePath, [System.Text.Encoding]::UTF8)

for ($i = 0; $i -lt $lines.Length; $i++) {
    if ($lines[$i] -match "show_category") {
        Write-Host "Line $($i+1): $($lines[$i])"
    }
}
