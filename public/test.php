<?php 

/**
 * Converting accession numbers to GI numbers
 *
 * @param string $acc_list a comma-separated list of accession numbers (<200)
 * @return array containing the GI numbers for each accession numbers (if valid).
 */
function accn_to_gin($acc_list){
    $acc_list = explode(',', $acc_list);
    for ($i=0; $i < count($acc_list); $i++) {
        $acc_list[$i] .= "[accn]";
    }
    $acc_list = implode('+OR+', $acc_list);
    
    $base = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/';
    $url = $base . "esearch.fcgi?db=nucleotide&term={$acc_list}&retmode=json";
    
    $json = json_decode(file_get_contents($url), true);
    
    return isset($json['esearchresult']['idlist']) ? $json['esearchresult']['idlist'] : [];
}

//print file_get_contents("https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=protein&id={$gi_nnumber}&rettype=fasta&retmode=text");

$gi_list = '24475906,224465210,50978625,9507198';
$acc_list = 'NM_009417,NM_000547,NM_001003009,NM_019353';
// 5835540,312233122,187250348,8572562,187250362,17737322

foreach(accn_to_gin($acc_list) as $gi_nnumber){
    copy("https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=protein&id={$gi_nnumber}&rettype=fasta&retmode=text", "/tmp/test.txt");
}

/*
var info;
$.ajax({
        method: 'get',
        url: 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi',
        data: 'db=nucleotide&term=NM_009417[accn]&retmode=json',
        cache: false,
        dataType: 'json',
        beforeSend: function(){
        },
        success: function(res){
            info = res;
        },
        error: function(xhr, status){
        }
});
info.esearchresult.idlist;

var info;
$.ajax({
        method: 'get',
        url: 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi',
        data: 'db=nucleotide&id=927442695&retmode=json',
        cache: false,
        dataType: 'json',
        beforeSend: function(){
        },
        success: function(res){
            info = res;
        },
        error: function(xhr, status){
        }
});

info.result[927442695]['title']
*/