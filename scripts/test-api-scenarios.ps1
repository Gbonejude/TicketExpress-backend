# TicketExpress API Test Runner
# Utilise les scénarios JSON avec Invoke-RestMethod

param(
    [string]$ScenarioFile = "postman\test-scenarios-critical.json",
    [string]$BaseUrl = "http://localhost:8000",
    [switch]$Verbose
)

Write-Host ""
Write-Host "╔════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║                                                    ║" -ForegroundColor Cyan
Write-Host "║         TicketExpress API Test Runner             ║" -ForegroundColor Green
Write-Host "║                                                    ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# Charger le fichier de scénarios
if (-not (Test-Path $ScenarioFile)) {
    Write-Host "❌ Fichier non trouvé: $ScenarioFile" -ForegroundColor Red
    exit 1
}

$scenarios = Get-Content $ScenarioFile | ConvertFrom-Json
Write-Host "✅ Scénarios chargés: $($scenarios.info.name)" -ForegroundColor Green
Write-Host "   Total: $($scenarios.scenarios.Count) scénarios" -ForegroundColor White
Write-Host ""

# Variables pour stocker les tokens et IDs
$variables = @{
    baseUrl = $BaseUrl
}

# Fonction pour remplacer les variables dans les strings
function Replace-Variables {
    param([string]$text)
    
    foreach ($key in $variables.Keys) {
        $text = $text -replace "{{\s*$key\s*}}", $variables[$key]
    }
    return $text
}

# Compteurs
$passed = 0
$failed = 0
$testResults = @()

# Exécuter les scénarios
foreach ($scenario in $scenarios.scenarios) {
    Write-Host "🧪 Test: $($scenario.name)" -ForegroundColor Yellow
    Write-Host "   ID: $($scenario.id)" -ForegroundColor Gray
    
    try {
        # Construire l'URL
        $url = $BaseUrl + (Replace-Variables $scenario.endpoint)
        
        # Construire les headers
        $headers = @{
            "Accept" = "application/json"
        }
        
        if ($scenario.headers) {
            foreach ($header in $scenario.headers.PSObject.Properties) {
                $headers[$header.Name] = Replace-Variables $header.Value
            }
        }
        
        # Préparer le body si présent
        $bodyParam = @{}
        if ($scenario.body) {
            $bodyParam["Body"] = ($scenario.body | ConvertTo-Json -Depth 10)
            $headers["Content-Type"] = "application/json"
        }
        
        # Exécuter la requête
        $response = Invoke-RestMethod `
            -Uri $url `
            -Method $scenario.method `
            -Headers $headers `
            @bodyParam `
            -ErrorAction Stop
        
        # Sauvegarder les variables si spécifié
        if ($scenario.saveAs) {
            if ($response.data.token) {
                $variables[$scenario.saveAs] = $response.data.token
            } elseif ($response.data.id) {
                $variables[$scenario.saveAs] = $response.data.id
            }
        }
        
        Write-Host "   ✅ SUCCESS" -ForegroundColor Green
        $passed++
        
        if ($Verbose) {
            Write-Host "   Response: $($response | ConvertTo-Json -Depth 2 -Compress)" -ForegroundColor Gray
        }
        
    } catch {
        Write-Host "   ❌ FAILED: $($_.Exception.Message)" -ForegroundColor Red
        $failed++
        
        if ($Verbose) {
            Write-Host "   Error: $($_ | Out-String)" -ForegroundColor Gray
        }
    }
    
    Write-Host ""
}

# Résumé
Write-Host "════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "📊 RÉSULTATS:" -ForegroundColor Yellow
Write-Host "   ✅ Passed:  $passed" -ForegroundColor Green
Write-Host "   ❌ Failed:  $failed" -ForegroundColor Red
Write-Host "   📊 Total:   $($passed + $failed)" -ForegroundColor White
$percentage = if (($passed + $failed) -gt 0) { [math]::Round(($passed / ($passed + $failed)) * 100, 2) } else { 0 }
Write-Host "   🎯 Success: $percentage%" -ForegroundColor Cyan
Write-Host ""
Write-Host "════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

if ($failed -eq 0) {
    Write-Host "🎉 Tous les tests ont réussi!" -ForegroundColor Green
} else {
    Write-Host "⚠️  Certains tests ont échoué. Vérifiez les logs ci-dessus." -ForegroundColor Yellow
}
Write-Host ""
