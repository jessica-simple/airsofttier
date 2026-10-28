$skip=Get-Content catalog-data/products-expanded.json -Raw | ConvertFrom-Json -AsHashtable
$extra=Get-Content catalog-data/extra.json -Raw | ConvertFrom-Json -AsHashtable
$all=@{}
Get-ChildItem catalog-data/s-*.html | ForEach-Object {$h=[string](Get-Content $_ -Raw);foreach($m in [regex]::Matches($h,'class="card product-card[^>]+href="(/product-\d+)" alt="([^"]*)"')){$u=$m.Groups[1].Value;if(!$skip.ContainsKey($u) -and !$extra.ContainsKey($u)){$all[$u]=$m.Groups[2].Value}}}
Write-Output ('Further products: '+$all.Count)
$all.Keys | ForEach-Object -Parallel {$u=$_;$f='catalog-data/'+$u.Trim('/')+'.html';if(!(Test-Path $f)){for($i=0;$i -lt 2;$i++){try{$r=Invoke-WebRequest ('https://www.paradiseonlinestorehk.com'+$u);$r.Content | Set-Content -Encoding utf8 $f;break}catch{if($i -eq 1){Write-Output ('FAILED '+$u)}}}}} -ThrottleLimit 12
