<?php
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

// Função para obter o src do iframe da URL fornecida
function getIframeSrc($url) {
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
    
    // Expressão regular para encontrar o src do iframe
    $pattern = '/<iframe[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/';
    
    // Faz a correspondência com o padrão
    if (preg_match($pattern, $html, $matches)) {
        return ['Player' => $matches[1]]; // Retorna o URL encontrado
    } else {
        return ['error' => 'Iframe não encontrado.'];
    }
}

// Chama a função e obtém o resultado
$result = getIframeSrc($url);

// Define o tipo de conteúdo como JSON
header('Content-Type: application/json');

// Exibe o resultado em formato JSON
echo json_encode($result);
?>