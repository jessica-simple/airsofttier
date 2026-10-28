$missing=Get-Content catalog-data/missing-pages.txt
$missing | ForEach-Object -Parallel {
 $u=$_;$f='catalog-data/'+$u.Trim('/')+'.html'
 if(Test-Path catalog-data/pause.flag){return}
 if(!(Test-Path $f)){
 try{$r=Invoke-WebRequest ('https://www.paradiseonlinestorehk.com'+$u);$r.Content | Set-Content -Encoding utf8 $f}
 catch{Write-Output ('FAILED '+$u+' '+$_.Exception.Message);if($_.Exception.Message -match '500 '){'pause' | Set-Content catalog-data/pause.flag}}
 }
} -ThrottleLimit 4

