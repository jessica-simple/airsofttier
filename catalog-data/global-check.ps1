foreach($sort in @(@('date','asc'),@('name','asc'),@('name','desc'))){
 $s=[Microsoft.PowerShell.Commands.WebRequestSession]::new()
 $s.Cookies.Add([System.Net.Cookie]::new('pagination[5][Category][page_size]','36','/','www.paradiseonlinestorehk.com'))
 $s.Cookies.Add([System.Net.Cookie]::new('gridsort[5][field]',$sort[0],'/','www.paradiseonlinestorehk.com'))
 $s.Cookies.Add([System.Net.Cookie]::new('gridsort[5][sort]',$sort[1],'/','www.paradiseonlinestorehk.com'))
 for($p=1;$p -le 5;$p++){$s.Cookies.Add([System.Net.Cookie]::new('pagination[5][Category][page_from]',[string]$p,'/','www.paradiseonlinestorehk.com'));$r=Invoke-WebRequest 'https://www.paradiseonlinestorehk.com/Category' -WebSession $s;$r.Content | Set-Content -Encoding utf8 ("catalog-data/global-"+($sort -join '-')+"-$p.html")}
}
