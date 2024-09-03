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
    
    // Fecha a sessão cURL
    curl_close($ch);
    
    // Verifica se a requisição foi bem-sucedida
    if ($html === false) {
        return ['error' => 'Erro ao acessar a URL.'];
    }
    
    // Cria um novo DOMDocument e carrega o HTML
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Para evitar warnings de HTML malformado
    $dom->loadHTML($html);
    libxml_clear_errors();
    
    // Cria um XPath para navegar no DOM
    $xpath = new DOMXPath($dom);
    
    // Consulta para obter os dados dos episódios
    $episodes = $xpath->query("//li[contains(@class, 'mark')]");
    
    $results = [];
    
    foreach ($episodes as $episode) {
        $imgNode = $xpath->query(".//div[@class='imagen']/img", $episode)->item(0);
        $titleNode = $xpath->query(".//div[@class='episodiotitle']/a", $episode)->item(0);
        $dateNode = $xpath->query(".//div[@class='episodiotitle']/span[@class='date']", $episode)->item(0);
        
        $results[] = [
            'number' => trim($xpath->query(".//div[@class='numerando']", $episode)->item(0)->textContent),
            'title' => $titleNode ? trim($titleNode->textContent) : 'N/A',
            'url' => $titleNode ? $titleNode->getAttribute('href') : 'N/A',
            'image' => $imgNode ? $imgNode->getAttribute('src') : 'N/A',
            'date' => $dateNode ? trim($dateNode->textContent) : 'N/A'
        ];
    }
    
    return $results;
}

// Verifica se o parâmetro 'url' está presente na URL
if (!isset($_GET['url']) || empty($_GET['url'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Parâmetro URL não fornecido.']);
    exit;
}

// Obtém a URL a partir dos parâmetros GET e a valida
$url = filter_var($_GET['url'], FILTER_SANITIZE_URL);

if (!filter_var($url, FILTER_VALIDATE_URL)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'URL inválida.']);
    exit;
}

// Obtém e processa os dados da URL fornecida
$data = fetchAndParseHtml($url);

// Define o tipo de conteúdo como JSON
header('Content-Type: application/json');

// Exibe o resultado em formato JSON
echo json_encode($data);
?>