$h=Get-Content Category.html -Raw
$paths=[regex]::Matches($h,'href="(/Category/category-[^"]+|/category-\d+)"') | ForEach-Object {$_.Groups[1].Value} | Sort-Object -Unique
$paths | ForEach-Object -Parallel {
 $path=$_; $id=($path -split '/')[-1]; $s=[Microsoft.PowerShell.Commands.WebRequestSession]::new()
 $s.Cookies.Add([System.Net.Cookie]::new("pagination[5][$id][page_size]",'36','/','www.paradiseonlinestorehk.com'))
 for($p=1;$p -le 5;$p++) {
 $s.Cookies.Add([System.Net.Cookie]::new("pagination[5][$id][page_from]",[string]$p,'/','www.paradiseonlinestorehk.com'))
 $r=Invoke-WebRequest ('https://www.paradiseonlinestorehk.com'+$path) -WebSession $s
 $r.Content | Set-Content -Encoding utf8 ("catalog-data/c-$id-$p.html")
 $n=[int][regex]::Match($r.Content,'data-page-total="(\d+)"').Groups[1].Value
 if($p -ge $n){break}
 }
} -ThrottleLimit 6
$items=@{}; Get-ChildItem catalog-data/*.html | ForEach-Object {
 $h=Get-Content $_ -Raw
 foreach($m in [regex]::Matches($h,'class="card product-card[^>]+href="(/product-\d+)" alt="([^"]*)"')) { $items[$m.Groups[1].Value]=[System.Net.WebUtility]::HtmlDecode($m.Groups[2].Value).Trim() }
}
$items | ConvertTo-Json | Set-Content catalog-data/products-expanded.json
Write-Output ('Unique products: '+$items.Count)
$items.Keys | ForEach-Object -Parallel {
 $url=$_; $f='catalog-data/'+$url.Trim('/')+'.html'
 if(!(Test-Path $f)) { for($try=0;$try -lt 3;$try++) { try {$r=Invoke-WebRequest ('https://www.paradiseonlinestorehk.com'+$url); $r.Content | Set-Content -Encoding utf8 $f;break} catch {if($try -eq 2){Write-Output ('FAILED '+$url)}}} }
} -ThrottleLimit 8
