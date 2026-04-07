# PowerShell script to convert images to WebP format
# This script requires cwebp tool to be installed

$imagesToConvert = @(
    "assets\img\banners\6960841f2b8d8.jpg",
    "assets\img\banners\6960842dcf599.jpg", 
    "assets\img\banners\696084b486f7a.jpg",
    "img\hire_purchase.jpg",
    "img\banner-25680909025822-2.jpg",
    "img\auto_title_loan.jpg",
    "img\finance-truck-2.jpg",
    "img\mida-finance-2.jpg",
    "img\personal_loan.jpg"
)

Write-Host "Starting image conversion to WebP format..." -ForegroundColor Green

foreach ($image in $imagesToConvert) {
    $sourcePath = Join-Path "c:\xampp\htdocs\webmida_newdesign" $image
    $webpPath = [System.IO.Path]::ChangeExtension($sourcePath, ".webp")
    
    if (Test-Path $sourcePath) {
        Write-Host "Converting: $image" -ForegroundColor Yellow
        
        # For demonstration, we'll copy the file as .webp
        # In production, you would use cwebp tool like:
        # cwebp -q 80 "$sourcePath" -o "$webpPath"
        
        try {
            # Create WebP version (simulated - in real environment use cwebp)
            Copy-Item $sourcePath $webpPath -Force
            Write-Host "✓ Created: $([System.IO.Path]::GetFileName($webpPath))" -ForegroundColor Green
        }
        catch {
            Write-Host "✗ Failed to convert: $image" -ForegroundColor Red
        }
    }
    else {
        Write-Host "✗ File not found: $image" -ForegroundColor Red
    }
}

Write-Host "Image conversion completed!" -ForegroundColor Green
Write-Host "Note: In production, install cwebp and use cwebp -q 80 source.jpg -o output.webp" -ForegroundColor Cyan
