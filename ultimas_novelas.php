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
    $articles = $xpath->query("//article[@class='item tvshows']");
    
    $results = [];
    
    foreach ($articles as $article) {
        // Extrair título, URL, data, descrição, gênero e imagem
        $titleNode = $xpath->query(".//div[@class='data']/h3/a", $article)->item(0);
        $dateNode = $xpath->query(".//div[@class='data']/span", $article)->item(0);
        $descNode = $xpath->query(".//div[@class='texto']", $article)->item(0);
        $genreNode = $xpath->query(".//div[@class='genres']/div[@class='mta']/a", $article)->item(0);
        $imgNode = $xpath->query(".//div[@class='poster']/img", $article)->item(0);
        
        // Obtém o texto e atributos, substitui por 'N/A' se não encontrado
        $title = $titleNode ? trim($titleNode->textContent) : '';
        $url = $titleNode ? $titleNode->getAttribute('href') : '';
        $date = $dateNode ? trim($dateNode->textContent) : '';
        $description = $descNode ? trim($descNode->textContent) : '';
        $genre = $genreNode ? trim($genreNode->textContent) : '';
        $image = $imgNode ? $imgNode->getAttribute('src') : '';
        
        // Adiciona ao resultado somente se o título não estiver vazio
        if (!empty($title)) {
            $result = ['title' => $title];
            
            if (!empty($url)) {
                $result['url'] = $url;
            }
            
            if (!empty($date)) {
                $result['date'] = $date;
            }
            
            if (!empty($description)) {
                $result['description'] = $description;
            }
            
            if (!empty($genre)) {
                $result['genre'] = $genre;
            }
            
            if (!empty($image)) {
                $result['image'] = $image;
            }
            
            $results[] = $result;
        }
    }
    
    return $results;
}

// URL a ser requisitada
$url = 'https://novelasflixbr.net/novelas/';

// Obtém e processa os dados
$data = fetchAndParseHtml($url);

// Define o tipo de conteúdo como JSON
header('Content-Type: application/json; charset=utf-8');

// Converte o array de resultados para uma string JSON
$json = json_encode($data, JSON_PRETTY_PRINT);

// Verifica se a conversão para JSON foi bem-sucedida
if (json_last_error() === JSON_ERROR_NONE) {
    // Exibe a string JSON
    echo $json;
} else {
    // Exibe um erro se a conversão para JSON falhar
    echo json_encode(['error' => 'Erro na codificação JSON: ' . json_last_error_msg()]);
}
?>
