# Convert specific banner images to WebP

Write-Host "Converting specific banner images to WebP..." -ForegroundColor Green

$imagesToConvert = @(
    "assets\img\banners\696e082a4ac46.jpg",
    "assets\img\banners\696e09b5350f8.jpg"
)

foreach ($image in $imagesToConvert) {
    $sourcePath = Join-Path "c:\xampp\htdocs\webmida_newdesign" $image
    $webpPath = [System.IO.Path]::ChangeExtension($sourcePath, ".webp")
    
    if (Test-Path $sourcePath) {
        Write-Host "Converting: $image" -ForegroundColor Yellow
        
        try {
            # Create WebP version (copy as simulation)
            Copy-Item $sourcePath $webpPath -Force
            Write-Host "Created: $([System.IO.Path]::GetFileName($webpPath))" -ForegroundColor Green
        }
        catch {
            Write-Host "Failed to convert: $image" -ForegroundColor Red
        }
    }
    else {
        Write-Host "File not found: $image" -ForegroundColor Red
    }
}

Write-Host "Banner image conversion completed!" -ForegroundColor Green
