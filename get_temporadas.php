<?php
// Função para obter e processar o HTML da URL fornecida
function fetchAndParseHtml($url) {
    // Inicia uma sessão cURL
    $ch = curl_init();
    
    // Define a URL e outras opções
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    // Executa a requisição e obtém o conteúdo
    $html = curl_exec($ch);
    
    // Verifica se houve erro na requisição
    if (curl_errno($ch)) {
        curl_close($ch);
        return ['error' => 'Erro ao acessar a URL: ' . curl_error($ch)];
    }
    
    // Fecha a sessão cURL
    curl_close($ch);
    
    // Cria um novo DOMDocument e carrega o HTML
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Para evitar warnings de HTML malformado
    $dom->loadHTML($html);
    libxml_clear_errors();
    
    // Cria um XPath para navegar no DOM
    $xpath = new DOMXPath($dom);
    
    // Consulta para obter os dados dos artigos
    $articles = $xpath->query("//article[contains(@class, 'item se seasons')]");
    
    $results = [];
    
    foreach ($articles as $article) {
        $imgNode = $xpath->query(".//div[@class='poster']/img", $article)->item(0);
        $titleNode = $xpath->query(".//div[@class='data']/h3/a", $article)->item(0);
        $dateNode = $xpath->query(".//div[@class='data']/span", $article)->item(0);
        $seasonNode = $xpath->query(".//div[@class='season_m']/a", $article)->item(0);
        
        $results[] = [
            'title' => $titleNode ? trim($titleNode->textContent) : 'N/A',
            'url' => $titleNode ? $titleNode->getAttribute('href') : 'N/A',
            'image' => $imgNode ? $imgNode->getAttribute('src') : 'N/A',
            'date' => $dateNode ? trim($dateNode->textContent) : 'N/A',
            'season_info' => $seasonNode ? trim($seasonNode->textContent) : 'N/A'
        ];
    }
    
    return $results;
}

// URL a ser requisitada
$url = 'https://novelasflixbr.net/temporadas/';

// Obtém e processa os dados
$data = fetchAndParseHtml($url);

// Define o tipo de conteúdo como JSON
header('Content-Type: application/json; charset=utf-8');

// Converte o array de resultados para uma string JSON
$jsonString = json_encode($data, JSON_PRETTY_PRINT);

// Exibe a string JSON
echo $jsonString;
?>
